<?php
// 1. Koneksi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kasir';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. Query Ringkasan Statistik & Laba
$totalPendapatan = 0;
$totalTransaksi  = 0;
$totalItemTerjual = 0;
$totalLaba       = 0;

$queryStat = "SELECT 
                COUNT(id) as total_trx, 
                SUM(total_price) as total_income 
              FROM transactions";
$resStat = mysqli_query($conn, $queryStat);
if ($resStat && $rowStat = mysqli_fetch_assoc($resStat)) {
    $totalTransaksi  = $rowStat['total_trx'] ?? 0;
    $totalPendapatan = $rowStat['total_income'] ?? 0;
}

// Menghitung Laba Kotor (Harga Jual - Harga Modal) x Qty
$queryItem = "SELECT SUM(qty) as total_qty, 
                     SUM((price - cost_price) * qty) as total_profit 
              FROM transaction_details";
$resItem = mysqli_query($conn, $queryItem);
if ($resItem && $rowItem = mysqli_fetch_assoc($resItem)) {
    $totalItemTerjual = $rowItem['total_qty'] ?? 0;
    $totalLaba        = $rowItem['total_profit'] ?? 0;
}

// 3. Query Ambil Semua Transaksi
$transactions = [];
$queryTrx = "SELECT * FROM transactions ORDER BY created_at DESC";
$resTrx = mysqli_query($conn, $queryTrx);

if ($resTrx && mysqli_num_rows($resTrx) > 0) {
    while ($row = mysqli_fetch_assoc($resTrx)) {
        $trxId = $row['id'];
        $items = [];
        $stmtDetail = $conn->prepare("SELECT td.*, p.name as product_name
                                      FROM transaction_details td
                                      LEFT JOIN products p ON td.product_id = p.id
                                      WHERE td.transaction_id = ?");
        $stmtDetail->bind_param("i", $trxId);
        $stmtDetail->execute();
        $resDetail = $stmtDetail->get_result();
        while ($d = $resDetail->fetch_assoc()) {
            $items[] = $d;
        }
        $stmtDetail->close();
        $row['items'] = $items;
        $transactions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan & Laba - Smart POS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased overflow-hidden">

  <div class="flex h-screen">
    
    <!-- LEFT SIDEBAR -->
    <aside class="w-20 bg-slate-900 flex flex-col items-center py-6 justify-between shadow-xl z-50 shrink-0 select-none">
      <div class="flex flex-col items-center gap-8 w-full">
        <a href="index.php" title="Halaman Kasir" class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-slate-900 font-black text-xl shadow-lg shadow-amber-500/30 hover:scale-105 transition cursor-pointer">
          ☕
        </a>

        <nav class="flex flex-col gap-4 w-full px-3">
          <a href="index.php" title="Halaman Kasir" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer block">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </a>
          <a href="laporan.php" title="Laporan Penjualan" class="p-3 bg-amber-500/20 text-amber-400 rounded-xl transition flex justify-center items-center hover:bg-amber-500/30 cursor-pointer block">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          </a>
          <a href="produk.php" title="Manajemen Produk" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer block">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          </a>
        </nav>
      </div>
      <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">KS</div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-100">
      
      <!-- Top Header -->
      <header class="px-8 py-5 bg-white border-b border-slate-200/80 flex justify-between items-center shrink-0">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan Penjualan & Laba</h1>
          <p class="text-xs text-slate-400 font-medium">Ringkasan riwayat transaksi harian, pendapatan, dan laba kotor.</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition">
          ← Kembali ke Kasir
        </a>
      </header>

      <div class="p-8 flex-1 overflow-y-auto space-y-6">
        
        <!-- Cards Stats -->
        <div class="grid grid-cols-4 gap-5">
          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold">💰</div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Pendapatan</p>
              <h3 class="text-xl font-extrabold text-slate-900">Rp <?= number_format($totalPendapatan, 0, ',', '.'); ?></h3>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">📈</div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Laba Kotor</p>
              <h3 class="text-xl font-extrabold text-indigo-700">Rp <?= number_format($totalLaba, 0, ',', '.'); ?></h3>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl font-bold">🧾</div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Transaksi</p>
              <h3 class="text-xl font-extrabold text-slate-900"><?= number_format($totalTransaksi, 0, ',', '.'); ?></h3>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold">☕</div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Item Terjual</p>
              <h3 class="text-xl font-extrabold text-slate-900"><?= number_format($totalItemTerjual, 0, ',', '.'); ?></h3>
            </div>
          </div>
        </div>

        <!-- Table Riwayat -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-extrabold text-slate-900 text-base">Riwayat Transaksi</h2>
            <span class="text-xs text-slate-400 font-semibold">Total: <?= count($transactions); ?> Data</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                <tr>
                  <th class="py-3.5 px-6">ID Trx</th>
                  <th class="py-3.5 px-6">Waktu</th>
                  <th class="py-3.5 px-6">Total Tagihan</th>
                  <th class="py-3.5 px-6">Laba Kotor</th>
                  <th class="py-3.5 px-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php if (empty($transactions)): ?>
                  <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400 font-semibold">
                      Belum ada data transaksi yang tersimpan.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($transactions as $trx): 
                    // Hitung laba per transaksi
                    $trxLaba = 0;
                    if(isset($trx['items']) && is_array($trx['items'])) {
                       foreach($trx['items'] as $item) {
                           $trxLaba += ($item['price'] - $item['cost_price']) * $item['qty'];
                       }
                    }
                  ?>
                    <tr class="hover:bg-slate-50/80 transition">
                      <td class="py-4 px-6 font-mono font-bold text-slate-800">#<?= htmlspecialchars($trx['id']); ?></td>
                      <td class="py-4 px-6 text-slate-500 font-medium"><?= date('d M Y, H:i', strtotime($trx['created_at'])); ?></td>
                      <td class="py-4 px-6 font-extrabold text-slate-900">Rp <?= number_format($trx['total_price'], 0, ',', '.'); ?></td>
                      <td class="py-4 px-6 font-bold text-indigo-600">Rp <?= number_format($trxLaba, 0, ',', '.'); ?></td>
                      <td class="py-4 px-6 text-center">
                        <button onclick='openDetailModal(<?= htmlspecialchars(json_encode($trx), ENT_QUOTES, "UTF-8"); ?>)' class="px-3 py-1.5 bg-slate-100 hover:bg-amber-500 hover:text-white text-slate-700 font-bold rounded-lg transition text-[11px]">
                          Detail Laba
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

  </div>

  <!-- MODAL DETAIL TRANSAKSI & LABA -->
  <div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-[450px] p-6 shadow-2xl border border-slate-100 flex flex-col">
      <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
        <div>
          <h3 class="font-black text-slate-900 text-base" id="modal-trx-id">Detail Transaksi</h3>
          <p class="text-[10px] text-slate-400 font-medium" id="modal-trx-date">-</p>
        </div>
        <button onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-700 font-bold text-sm">✕</button>
      </div>

      <div class="space-y-2 mb-4 max-h-60 overflow-y-auto pr-1" id="modal-items-list">
        <!-- Item detail di-render via JS -->
      </div>

      <div class="pt-3 border-t border-slate-100 space-y-1 text-xs font-semibold text-slate-600">
        <div class="flex justify-between"><span>Metode:</span><span id="modal-method" class="font-bold text-slate-800">-</span></div>
        <div class="flex justify-between mt-2"><span>Total Tagihan:</span><span id="modal-total" class="font-black text-slate-900">-</span></div>
        <div class="flex justify-between bg-indigo-50 p-2 rounded mt-2"><span>Total Laba:</span><span id="modal-profit" class="font-black text-indigo-700">-</span></div>
      </div>

      <button onclick="closeDetailModal()" class="mt-5 w-full py-2.5 bg-slate-900 text-white font-bold rounded-xl text-xs hover:bg-slate-800 transition">
        Tutup
      </button>
    </div>
  </div>

  <script>
    const formatRupiah = (num) => new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(num);

    function openDetailModal(trx) {
      document.getElementById('modal-trx-id').innerText = '#' + trx.id;
      document.getElementById('modal-trx-date').innerText = new Date(trx.created_at).toLocaleString("id-ID");
      document.getElementById('modal-method').innerText = trx.payment_method;
      document.getElementById('modal-total').innerText = formatRupiah(trx.total_price);

      const itemsList = document.getElementById('modal-items-list');
      itemsList.innerHTML = '';
      
      let totalLaba = 0;

      if (trx.items && trx.items.length > 0) {
        trx.items.forEach(item => {
          const labaItem = (item.price - item.cost_price) * item.qty;
          totalLaba += labaItem;

          itemsList.innerHTML += `
            <div class="text-xs bg-slate-50 p-3 rounded-lg border border-slate-100">
              <div class="flex justify-between mb-1">
                <p class="font-bold text-slate-800">${item.product_name || item.product_id} (x${item.qty})</p>
                <span class="font-bold text-slate-900">${formatRupiah(item.subtotal)}</span>
              </div>
              <div class="flex justify-between text-[10px] text-slate-500">
                <p>Modal: ${formatRupiah(item.cost_price)} | Jual: ${formatRupiah(item.price)}</p>
                <p class="font-semibold text-indigo-600">Laba: ${formatRupiah(labaItem)}</p>
              </div>
            </div>
          `;
        });
      } else {
        itemsList.innerHTML = '<p class="text-xs text-slate-400 text-center py-2">Tidak ada rincian item.</p>';
      }

      document.getElementById('modal-profit').innerText = formatRupiah(totalLaba);
      document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
      document.getElementById('detail-modal').classList.add('hidden');
    }
  </script>
</body>
</html>