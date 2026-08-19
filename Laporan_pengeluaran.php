<?php
// 1. KONEKSI DATABASE
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kasir';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. QUERY MENGELOMPOKKAN PENGELUARAN PER TANGGAL
$queryGroup = "SELECT 
                    DATE(created_at) AS tanggal, 
                    SUM(amount) AS total_harian,
                    COUNT(id) AS total_transaksi
               FROM expenses 
               GROUP BY DATE(created_at) 
               ORDER BY tanggal DESC";

$resultGroup = mysqli_query($conn, $queryGroup);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Pengeluaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-100 p-8 text-slate-800">

  <div class="max-w-4xl mx-auto space-y-6">
    
    <!-- HEADER -->
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
      <div>
        <h1 class="text-2xl font-black text-slate-900">💸 Laporan Pengeluaran</h1>
        <p class="text-xs text-slate-500 font-medium">Pengelompokan pengeluaran berdasarkan tanggal</p>
      </div>
      <a href="index.php" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
        ← Kembali ke Kasir
      </a>
    </div>

    <!-- DAFTAR PENGELUARAN PER TANGGAL -->
    <div class="space-y-4">
      <?php if ($resultGroup && mysqli_num_rows($resultGroup) > 0): ?>
        <?php while ($rowGroup = mysqli_fetch_assoc($resultGroup)): ?>
          <?php 
            $tgl = $rowGroup['tanggal'];
            $tglFormat = date("d F Y", strtotime($tgl));
          ?>

          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            
            <!-- Tanggal & Total Harian -->
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
              <div>
                <h3 class="font-black text-slate-800 text-sm">📅 <?= $tglFormat; ?></h3>
                <p class="text-xs text-slate-500 font-medium"><?= $rowGroup['total_transaksi']; ?> Catatan Pengeluaran</p>
              </div>
              <div class="text-right">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 block">Total Hari Ini</span>
                <span class="text-base font-black text-rose-600">
                  Rp <?= number_format($rowGroup['total_harian'], 0, ',', '.'); ?>
                </span>
              </div>
            </div>

            <!-- Detail Pengeluaran -->
            <div class="p-4">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="text-slate-400 font-bold border-b border-slate-100">
                    <th class="pb-2">Waktu</th>
                    <th class="pb-2">Keterangan / Judul</th>
                    <th class="pb-2 text-right">Nominal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php
                    $queryDetail = "SELECT * FROM expenses WHERE DATE(created_at) = '$tgl' ORDER BY id DESC";
                    $resultDetail = mysqli_query($conn, $queryDetail);

                    while ($detail = mysqli_fetch_assoc($resultDetail)):
                      $waktu = date("H:i", strtotime($detail['created_at']));
                  ?>
                    <tr class="hover:bg-slate-50 transition">
                      <td class="py-2.5 font-mono text-slate-500"><?= $waktu; ?> WIB</td>
                      <td class="py-2.5 font-bold text-slate-800"><?= htmlspecialchars($detail['title']); ?></td>
                      <td class="py-2.5 font-black text-rose-500 text-right">
                        Rp <?= number_format($detail['amount'], 0, ',', '.'); ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>

          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 text-slate-400 font-bold text-xs">
          Belum ada catatan pengeluaran.
        </div>
      <?php endif; ?>
    </div>

  </div>

</body>
</html>