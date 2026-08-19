<?php
header('Content-Type: application/json');

// 1. Koneksi Database
require_once 'koneksi.php';

// Semua galat MySQL dilempar sebagai exception supaya bisa ditangkap & di-rollback.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 2. Tangkap Input JSON dari Front-end
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true);

if (!$data || empty($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Data keranjang kosong']);
    exit;
}

$totalPrice    = (float)($data['total_price'] ?? 0);
$payAmount     = (float)($data['pay_amount'] ?? 0);
$changeAmount  = (float)($data['change_amount'] ?? 0);
$paymentMethod = $data['payment_method'] ?? 'Tunai';
$items         = $data['items'];

// Hanya metode yang dikenal yang boleh masuk database.
if (!in_array($paymentMethod, ['Tunai', 'QRIS', 'Debit'], true)) {
    echo json_encode(['success' => false, 'message' => 'Metode pembayaran tidak dikenal']);
    exit;
}

// 3. Semua penulisan dibungkus satu transaksi database.
//    Kalau ada satu item gagal, seluruhnya dibatalkan supaya tidak ada
//    header transaksi tanpa rincian atau stok yang terlanjur terpotong.
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO transactions (total_price, pay_amount, change_amount, payment_method, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ddds", $totalPrice, $payAmount, $changeAmount, $paymentMethod);
    $stmt->execute();
    $transactionId = $stmt->insert_id;
    $stmt->close();

    // Statement disiapkan sekali, dipakai ulang untuk setiap item.
    $stmtCost   = $conn->prepare("SELECT name, cost_price FROM products WHERE id = ?");
    $stmtDetail = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, cost_price, qty, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    // Syarat `stock >= ?` mencegah stok menjadi minus saat dua kasir menjual bersamaan.
    $stmtStock  = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($items as $item) {
        $productId = (string)($item['id'] ?? '');
        $qty       = (int)($item['qty'] ?? 0);
        $price     = (float)($item['price'] ?? 0);

        if ($productId === '' || $qty <= 0) {
            throw new Exception('Item tidak valid di keranjang.');
        }

        // Harga modal diambil dari database, bukan dari browser.
        $stmtCost->bind_param("s", $productId);
        $stmtCost->execute();
        $prod = $stmtCost->get_result()->fetch_assoc();
        if (!$prod) {
            throw new Exception("Produk dengan kode '$productId' tidak ada di database.");
        }
        $costPrice = (float)$prod['cost_price'];
        $subtotal  = $qty * $price;

        $stmtDetail->bind_param("isdidd", $transactionId, $productId, $costPrice, $qty, $price, $subtotal);
        $stmtDetail->execute();

        // Stok dipotong dengan syarat; kalau tidak ada baris berubah berarti stok kurang.
        $stmtStock->bind_param("isi", $qty, $productId, $qty);
        $stmtStock->execute();
        if ($stmtStock->affected_rows < 1) {
            throw new Exception("Stok '{$prod['name']}' tidak mencukupi.");
        }
    }

    $stmtCost->close();
    $stmtDetail->close();
    $stmtStock->close();

    $conn->commit();
    echo json_encode(['success' => true, 'transaction_id' => $transactionId]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
