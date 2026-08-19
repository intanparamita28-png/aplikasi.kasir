<?php
header('Content-Type: application/json');

// 1. Koneksi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kasir';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi ke database gagal']);
    exit;
}

// 2. Tangkap Input JSON dari Front-end
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Data keranjang kosong']);
    exit;
}

$totalPrice    = $data['total_price'] ?? 0;
$payAmount     = $data['pay_amount'] ?? 0;
$changeAmount  = $data['change_amount'] ?? 0;
$paymentMethod = $data['payment_method'] ?? 'Tunai';
$items         = $data['items'];

// 3. Simpan ke Tabel Transactions
$stmt = $conn->prepare("INSERT INTO transactions (total_price, pay_amount, change_amount, payment_method, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ddds", $totalPrice, $payAmount, $changeAmount, $paymentMethod);

if ($stmt->execute()) {
    $transactionId = $stmt->insert_id;
    $stmt->close();

    // 4. Simpan Detail Items & Kurangi Stok
    foreach ($items as $item) {
        $productId = $item['id'];
        $qty       = $item['qty'];
        $price     = $item['price'];
        $subtotal  = $qty * $price;

        // Ambil cost_price (modal) dari tabel products
        $costPrice = 0;
        $qProd = $conn->query("SELECT cost_price FROM products WHERE id = '$productId'");
        if ($qProd && $rProd = $qProd->fetch_assoc()) {
            $costPrice = $rProd['cost_price'];
        }

        // Insert ke transaction_details
        $stmtDetail = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, cost_price, qty, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtDetail->bind_param("isdidd", $transactionId, $productId, $costPrice, $qty, $price, $subtotal);
        $stmtDetail->execute();
        $stmtDetail->close();

        // Update / Kurangi Stok Produk
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = '$productId'");
    }

    echo json_encode(['success' => true, 'transaction_id' => $transactionId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi']);
}
?>