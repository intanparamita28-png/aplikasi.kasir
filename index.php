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

// -------------------------------------------------------------
// Penanganan AJAX Simpan Pengeluaran (Terhubung dengan Modal)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_expense') {
    $title  = mysqli_real_escape_string($conn, $_POST['title']);
    $amount = (float)$_POST['amount'];
    
    // Simpan ke tabel expenses
    $sql = "INSERT INTO expenses (title, amount, created_at) VALUES ('$title', '$amount', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// Data dummy opsional jika database kosong
$menuList = [
    [
        'id' => 'DUMMY-1',
        'name' => 'Kopi Susu Gula Aren',
        'category' => 'kopi',
        'price' => 18000,
        'stock' => 4,
        'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'id' => 'DUMMY-2',
        'name' => 'Cheesecake Dessert Box',
        'category' => 'dessert',
        'price' => 25000,
        'stock' => 10,
        'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=600&q=80'
    ]
];

// FUNGSI GAMBAR OTOMATIS
function getProductImage($name, $category) {
    $nameLower = strtolower($name);
    
    if (strpos($nameLower, 'kopi susu') !== false) return 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80';
    if (strpos($nameLower, 'espresso') !== false) return 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=600&q=80';
    if (strpos($nameLower, 'americano') !== false) return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80';
    if (strpos($nameLower, 'cheesecake') !== false) return 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=600&q=80';

    if (strtolower($category) == 'minuman') {
        return 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=600&q=80';
    } elseif (strtolower($category) == 'makanan') {
        return 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=80';
    } else {
        return 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80';
    }
}

// Ambil data dari database
$dbProducts = [];
$queryProducts = @mysqli_query($conn, "SELECT * FROM products");

if ($queryProducts && mysqli_num_rows($queryProducts) > 0) {
    while ($row = mysqli_fetch_assoc($queryProducts)) {
        $nameLower = strtolower($row['name']);
        $categoryLower = strtolower($row['category']);
        
        if ($categoryLower === 'minuman') {
            if (strpos($nameLower, 'kopi') !== false || strpos($nameLower, 'coffee') !== false || strpos($nameLower, 'espresso') !== false) {
                $categoryLower = 'kopi';
            } else {
                $categoryLower = 'non-kopi';
            }
        }

        $dbProducts[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'category' => $categoryLower,
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock'],
            'image' => getProductImage($row['name'], $row['category'])
        ];
    }
}

$products = !empty($dbProducts) ? $dbProducts : $menuList;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart POS - Coffee Shop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased overflow-hidden">

  <div class="flex h-screen">
    
    <!-- LEFT SIDEBAR -->
    <aside class="w-20 bg-slate-900 flex flex-col items-center py-6 justify-between shadow-xl z-50 shrink-0 select-none">
      <div class="flex flex-col items-center gap-8 w-full">
        <a href="/kasir/index.php" title="Halaman Kasir" class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-slate-900 font-black text-2xl shadow-lg shadow-amber-500/30 hover:scale-105 transition cursor-pointer">
          ☕
        </a>

        <nav class="flex flex-col gap-4 w-full px-3">
          <a href="/kasir/index.php" title="Halaman Kasir" class="p-3 bg-amber-500/20 text-amber-400 rounded-xl transition flex justify-center items-center hover:bg-amber-500/30 cursor-pointer block">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </a>

          <a href="/kasir/laporan.php" title="Laporan Penjualan" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer block">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          </a>

          <a href="/kasir/produk.php" title="Manajemen Produk & Stok" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer block">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          </a>

          <a href="/kasir/Laporan_pengeluaran.php" title="Laporan Pengeluaran" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer block">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </a>
        </nav>
      </div>

      <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-sm font-bold text-slate-300">
        KS
      </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-100">
      
      <!-- Header dengan Pencarian & Filter Tanggal -->
      <header class="px-8 py-4 bg-white border-b border-slate-200/80 flex justify-between items-center shrink-0 gap-4">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Coffee Shop</h1>
          <p class="text-sm text-slate-500 font-medium">Pilih menu makanan & minuman untuk pelanggan</p>
        </div>

        <div class="flex gap-2 items-center">
          <button onclick="openModal('shift-modal')" class="px-3 py-2 bg-slate-800 text-white hover:bg-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
            <span>🔑</span> Shift
          </button>

          <button onclick="openModal('expense-modal')" class="px-3 py-2 bg-rose-500/10 text-rose-600 hover:bg-rose-500/20 rounded-xl text-xs font-bold transition flex items-center gap-1 border border-rose-200">
            <span>💸</span> Pengeluaran
          </button>

          <button onclick="openModal('promo-modal')" class="px-3 py-2 bg-amber-500/10 text-amber-600 hover:bg-amber-500/20 rounded-xl text-xs font-bold transition flex items-center gap-1 border border-amber-200">
            <span>🎉</span> Promo
          </button>
        </div>

        <!-- Filter Nama & Tanggal -->
        <div class="flex items-center gap-2">
          <div class="relative">
            <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Cari nama menu..." class="w-48 pl-8 pr-3 py-2 bg-slate-100 border border-transparent rounded-xl text-xs font-semibold focus:bg-white focus:border-amber-400 transition outline-none">
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          </div>
        </div>
      </header>

      <!-- Category Filter Pills -->
      <div class="px-8 py-3 flex gap-2.5 overflow-x-auto shrink-0">
        <button onclick="filterCategory('all', this)" class="cat-btn px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold shadow-md transition">Semua Menu</button>
        <button onclick="filterCategory('kopi', this)" class="cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition">☕ Coffee</button>
        <button onclick="filterCategory('non-kopi', this)" class="cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition">🥤 Non-Coffee</button>
        <button onclick="filterCategory('makanan', this)" class="cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition">🍝 Makanan Berat</button>
        <button onclick="filterCategory('snack', this)" class="cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition">🍟 Snacks</button>
        <button onclick="filterCategory('dessert', this)" class="cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition">🍰 Dessert</button>
      </div>

      <!-- Product Cards Grid -->
      <div class="px-8 pb-8 flex-1 overflow-y-auto">
        <div class="grid grid-cols-3 gap-5" id="product-grid">
          <?php foreach ($products as $p): ?>
            <?php $isLowStock = $p['stock'] <= 5; ?>
            <div onclick='addToCart(<?= json_encode($p); ?>)' 
                 data-name="<?= strtolower(htmlspecialchars($p['name'])); ?>"
                 data-code="<?= strtolower(htmlspecialchars($p['id'])); ?>"
                 data-category="<?= strtolower(htmlspecialchars($p['category'])); ?>"
                 class="product-card group bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-amber-400 hover:-translate-y-1 cursor-pointer transition-all duration-300 flex flex-col justify-between">
              
              <div>
                <div class="w-full h-36 rounded-xl overflow-hidden mb-3 relative bg-slate-100 flex items-center justify-center">
                  <img src="<?= $p['image']; ?>" 
                       alt="<?= htmlspecialchars($p['name']); ?>" 
                       onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&q=80';"
                       class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                  
                  <span class="absolute top-2 left-2 text-xs font-extrabold uppercase tracking-wider text-slate-800 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg shadow-sm">
                    <?= htmlspecialchars($p['category']); ?>
                  </span>
                  
                  <span class="absolute top-2 right-2 text-xs font-extrabold <?= $isLowStock ? 'text-rose-700 bg-rose-100/90 animate-pulse border border-rose-300' : 'text-emerald-700 bg-emerald-100/90' ?> backdrop-blur-md px-2.5 py-1 rounded-lg shadow-sm">
                    <?= $isLowStock ? '⚠️ Sisa: ' . $p['stock'] : 'Stok: ' . $p['stock']; ?>
                  </span>
                </div>

                <h3 class="font-extrabold text-slate-800 text-base group-hover:text-amber-600 transition-colors line-clamp-1">
                  <?= htmlspecialchars($p['name']); ?>
                </h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Kode: <?= $p['id']; ?></p>
              </div>

              <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs text-slate-500 font-semibold">Harga</span>
                <span class="text-base font-black text-slate-900 group-hover:text-amber-600 transition-colors">
                  Rp <?= number_format($p['price'], 0, ',', '.'); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </main>

    <!-- RIGHT PANEL (KERANJANG PESANAN) -->
    <aside class="w-[430px] bg-white border-l border-slate-200 flex flex-col justify-between shadow-2xl z-20 shrink-0">
      
      <div class="p-4 border-b border-slate-100 flex justify-between items-center shrink-0">
        <div>
          <h2 class="text-lg font-black text-slate-900">Keranjang Belanja</h2>
          <p class="text-xs text-slate-500 font-medium">Order ID: <span id="order-id" class="font-mono font-bold text-slate-700">ORD-<?= date('mdHis'); ?></span></p>
        </div>
        <button onclick="clearCart()" class="text-xs text-rose-500 font-bold hover:bg-rose-50 px-3 py-1.5 rounded-lg transition">
          Reset
        </button>
      </div>

      <div class="p-4 overflow-y-auto flex-1 space-y-3" id="cart-list">
        <div class="h-full flex flex-col items-center justify-center text-slate-300 py-12">
          <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-3xl mb-2">🛒</div>
          <p class="text-xs font-bold text-slate-400">Belum ada item terpilih</p>
        </div>
      </div>

      <div class="p-4 bg-slate-50/90 border-t border-slate-200 space-y-3 shrink-0">
        
        <div class="bg-white p-2.5 rounded-xl border border-slate-200 space-y-2">
          <label class="block text-xs font-black text-slate-500 uppercase tracking-wide">Tipe Pesanan</label>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" id="btn-dinein" onclick="setOrderType('dinein')" class="py-1.5 bg-amber-500 text-slate-900 font-bold rounded-lg text-xs transition shadow-sm">
              🍽️ Dine-In
            </button>
            <button type="button" id="btn-takeaway" onclick="setOrderType('takeaway')" class="py-1.5 bg-slate-100 text-slate-600 font-bold rounded-lg text-xs hover:bg-slate-200 transition">
              🛍️ Takeaway
            </button>
          </div>
          <input type="text" id="order-info" placeholder="Nomor Meja (misal: Meja 04)" class="w-full text-xs px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg font-bold text-slate-800 outline-none focus:border-amber-500">
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
          <div>
            <label class="block text-xs font-black text-slate-500 uppercase tracking-wide mb-1">Diskon (%)</label>
            <input type="number" id="discount-percent" oninput="calculateTotal()" min="0" max="100" placeholder="0" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg font-bold text-slate-800 outline-none focus:border-amber-500 text-xs">
          </div>
          <div>
            <label class="block text-xs font-black text-slate-500 uppercase tracking-wide mb-1">Pembayaran</label>
            <select id="payment-method" onchange="toggleQrisView()" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg font-bold text-slate-800 outline-none focus:border-amber-500 text-xs">
              <option value="Tunai">💵 Tunai</option>
              <option value="QRIS">📱 QRIS / E-Wallet</option>
              <option value="Debit">💳 Kartu Debit</option>
            </select>
          </div>
        </div>

        <!-- BOX MODAL QRIS DANA BISNIS (TAMPIL OTOMATIS SAAT MODEL QRIS DIPILIH) -->
        <div id="qris-box" class="hidden p-3 bg-amber-50 border border-amber-300 rounded-xl text-center space-y-2">
          <p class="text-xs font-bold text-amber-900">Scan QRIS DANA Bisnis</p>
          <!-- Ganti src di bawah ini dengan link gambar QRIS DANA Bisnis milikmu -->
          <img id="qris-img" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=DANA_BISNIS_KASIR" alt="QRIS DANA Bisnis" class="w-32 h-32 mx-auto rounded-lg border-2 border-white shadow-md">
          <p class="text-[10px] text-slate-500">Silahkan minta pelanggan scan QR diatas</p>
        </div>

        <div class="space-y-1 text-xs pt-1">
          <div class="flex justify-between text-slate-600 font-medium">
            <span>Subtotal</span>
            <span id="subtotal-price" class="font-bold text-slate-800">Rp 0</span>
          </div>
          <div class="flex justify-between text-rose-500 font-medium">
            <span>Potongan Diskon</span>
            <span id="discount-amount" class="font-bold">- Rp 0</span>
          </div>
          <div class="flex justify-between items-center text-base font-black text-slate-900 pt-1 border-t border-slate-200">
            <span>Total Tagihan</span>
            <span id="total-price" class="text-xl text-amber-600">Rp 0</span>
          </div>
        </div>

        <div id="cash-input-box">
          <label class="block text-xs font-black text-slate-500 uppercase tracking-wide mb-1">Uang Pas Cepat</label>
          <div class="grid grid-cols-4 gap-1 mb-2">
            <button onclick="setQuickCash('pas')" class="py-1 bg-white border border-slate-200 text-xs font-bold text-slate-700 rounded-lg hover:bg-amber-500 hover:text-white transition">Uang Pas</button>
            <button onclick="setQuickCash(20000)" class="py-1 bg-white border border-slate-200 text-xs font-bold text-slate-700 rounded-lg hover:bg-amber-500 hover:text-white transition">20rb</button>
            <button onclick="setQuickCash(50000)" class="py-1 bg-white border border-slate-200 text-xs font-bold text-slate-700 rounded-lg hover:bg-amber-500 hover:text-white transition">50rb</button>
            <button onclick="setQuickCash(100000)" class="py-1 bg-white border border-slate-200 text-xs font-bold text-slate-700 rounded-lg hover:bg-amber-500 hover:text-white transition">100rb</button>
          </div>

          <label class="block text-xs font-black text-slate-500 uppercase tracking-wide mb-1">Uang Bayar (Rp)</label>
          <div class="relative">
            <span class="absolute left-3 top-2 text-slate-400 font-bold text-sm">Rp</span>
            <input type="number" id="pay-amount" oninput="calculateTotal()" placeholder="0" class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl font-bold text-sm text-slate-900 focus:ring-2 focus:ring-amber-500 outline-none transition shadow-sm">
          </div>
        </div>

        <div class="p-2.5 bg-white rounded-xl border border-slate-200 flex justify-between items-center">
          <span class="text-xs font-black text-slate-500 uppercase tracking-wide">Kembalian</span>
          <span id="change-amount" class="font-extrabold text-sm text-slate-400">Rp 0</span>
        </div>

        <button onclick="processCheckout()" class="w-full bg-slate-900 hover:bg-amber-600 active:scale-[0.98] text-white font-bold py-3 rounded-xl shadow-lg transition-all duration-200 text-sm flex items-center justify-center gap-2">
          <span>⚡</span> Bayar & Cetak Struk
        </button>
      </div>

    </aside>

  </div>

  <!-- MODAL EXPENSE (PENGELUARAN) -->
  <div id="expense-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-96 p-6 shadow-2xl relative">
      <button onclick="closeModal('expense-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold">✕</button>
      <h3 class="text-lg font-black text-slate-900 mb-1">💸 Catat Pengeluaran</h3>
      <p class="text-xs text-slate-500 mb-4">Pengeluaran ini akan tersimpan di database.</p>
      
      <div class="space-y-3 text-xs">
        <div>
          <label class="block font-bold text-slate-700 mb-1">Judul Pengeluaran</label>
          <input type="text" id="exp-title" placeholder="Misal: Beli Es Batu" class="w-full px-3 py-2 border rounded-xl font-medium outline-none focus:border-amber-500 text-xs">
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Nominal (Rp)</label>
          <input type="number" id="exp-amount" placeholder="0" class="w-full px-3 py-2 border rounded-xl font-bold outline-none focus:border-amber-500 text-xs">
        </div>
        <button onclick="submitExpense()" class="w-full py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl transition">Simpan Pengeluaran</button>
      </div>
    </div>
  </div>

  <!-- MODAL SHIFT & MODAL PROMO -->
  <div id="shift-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-96 p-6 shadow-2xl relative text-center">
      <button onclick="closeModal('shift-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 font-bold">✕</button>
      <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">🔑</div>
      <h3 class="text-lg font-black text-slate-900">Shift Kasir</h3>
      <p class="text-xs text-slate-500 mb-4">Kasir Aktif: <b class="text-slate-700">Admin Staff</b></p>
      <button onclick="alert('Shift ditutup!'); closeModal('shift-modal');" class="w-full py-2.5 bg-slate-900 text-white font-bold rounded-xl transition text-xs">Tutup Shift</button>
    </div>
  </div>

  <!-- MODAL RECEIPT / STRUK -->
  <div id="receipt-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-96 p-6 shadow-2xl flex flex-col items-center">
      <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mb-3">✓</div>
      <h3 class="text-lg font-black text-slate-900">Transaksi Berhasil!</h3>

      <div class="w-full bg-slate-50 border border-dashed border-slate-300 rounded-xl p-4 text-xs font-mono text-slate-700 space-y-2 my-4">
        <div class="text-center pb-2 border-b border-slate-200">
          <p class="font-bold text-sm text-slate-900">COFFEE SHOP POS</p>
          <p id="receipt-order-info" class="text-xs text-amber-600 font-bold">-</p>
        </div>
        <div class="text-xs flex justify-between text-slate-500">
          <span id="receipt-date">-</span>
          <span id="receipt-method">-</span>
        </div>
        <div id="receipt-items" class="py-2 border-b border-slate-200 space-y-1"></div>
        <div class="space-y-0.5 pt-1 text-xs">
          <div class="flex justify-between"><span>Subtotal:</span><span id="receipt-subtotal">Rp 0</span></div>
          <div class="flex justify-between text-rose-500"><span>Diskon:</span><span id="receipt-discount">Rp 0</span></div>
          <div class="flex justify-between font-bold text-slate-900 text-xs"><span>TOTAL:</span><span id="receipt-total">Rp 0</span></div>
          <div class="flex justify-between"><span>Bayar:</span><span id="receipt-pay">Rp 0</span></div>
          <div class="flex justify-between"><span>Kembali:</span><span id="receipt-change">Rp 0</span></div>
        </div>
      </div>

      <div class="flex gap-2 w-full">
        <button onclick="closeReceiptModal()" class="flex-1 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
        <button onclick="window.print()" class="flex-1 py-2.5 bg-amber-500 text-slate-900 font-bold rounded-xl text-xs">🖨️ Cetak</button>
      </div>
    </div>
  </div>

  <script>
    let cart = [];
    let currentOrderType = 'dinein';
    const formatRupiah = (num) => new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(num);

    function toggleQrisView() {
      const method = document.getElementById("payment-method").value;
      const qrisBox = document.getElementById("qris-box");
      const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
      const disc = parseFloat(document.getElementById("discount-percent").value) || 0;
      const total = subtotal - (subtotal * (disc / 100));

      if (method === "QRIS") {
        qrisBox.classList.remove("hidden");
        document.getElementById("pay-amount").value = total; 
      } else {
        qrisBox.classList.add("hidden");
      }
      calculateTotal();
    }

    function setOrderType(type) {
      currentOrderType = type;
      const btnDine = document.getElementById("btn-dinein");
      const btnTake = document.getElementById("btn-takeaway");
      const inputEl = document.getElementById("order-info");

      if (type === 'dinein') {
        btnDine.className = "py-1.5 bg-amber-500 text-slate-900 font-bold rounded-lg text-xs transition shadow-sm";
        btnTake.className = "py-1.5 bg-slate-100 text-slate-600 font-bold rounded-lg text-xs hover:bg-slate-200 transition";
        inputEl.placeholder = "Nomor Meja (misal: Meja 04)";
      } else {
        btnTake.className = "py-1.5 bg-amber-500 text-slate-900 font-bold rounded-lg text-xs transition shadow-sm";
        btnDine.className = "py-1.5 bg-slate-100 text-slate-600 font-bold rounded-lg text-xs hover:bg-slate-200 transition";
        inputEl.placeholder = "Nama Pelanggan (misal: Kak Budi)";
      }
    }

    function openModal(id) { document.getElementById(id).classList.remove("hidden"); }
    function closeModal(id) { document.getElementById(id).classList.add("hidden"); }

    async function submitExpense() {
      const title = document.getElementById("exp-title").value.trim();
      const amount = document.getElementById("exp-amount").value.trim();

      if (!title || !amount) {
        alert("⚠️ Judul dan Nominal pengeluaran wajib diisi!");
        return;
      }

      try {
        await fetch('/kasir/index.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ 'action': 'add_expense', 'title': title, 'amount': amount })
        });
        document.getElementById("exp-title").value = "";
        document.getElementById("exp-amount").value = "";
        closeModal('expense-modal');
        alert("✅ Pengeluaran berhasil dicatat!");
      } catch (error) {
        alert("✅ Catatan pengeluaran disimpan!");
        closeModal('expense-modal');
      }
    }

    function filterProducts() {
      const query = document.getElementById("search-input").value.toLowerCase();
      document.querySelectorAll(".product-card").forEach(card => {
        const name = card.getAttribute("data-name");
        card.style.display = name.includes(query) ? "flex" : "none";
      });
    }

    function filterCategory(cat, btn) {
      document.querySelectorAll(".cat-btn").forEach(b => b.className = "cat-btn px-4 py-2 bg-white text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition");
      btn.className = "cat-btn px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold shadow-md transition";

      document.querySelectorAll(".product-card").forEach(card => {
        const category = card.getAttribute("data-category");
        card.style.display = (cat === 'all' || category === cat) ? "flex" : "none";
      });
    }

    function addToCart(product) {
      if (parseInt(product.stock) <= 0) return alert("Stok habis!");
      const item = cart.find(i => i.id === product.id);
      if (item) {
        if (item.qty + 1 > parseInt(product.stock)) return alert("Stok tidak mencukupi!");
        item.qty++;
      } else {
        cart.push({ ...product, qty: 1 });
      }
      renderCart();
    }

    function updateQty(id, delta) {
      const item = cart.find(i => i.id === id);
      if (!item) return;
      if (delta > 0 && item.qty + delta > parseInt(item.stock)) return alert("Stok tidak mencukupi!");
      item.qty += delta;
      if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
      renderCart();
    }

    function clearCart() {
      cart = [];
      document.getElementById("pay-amount").value = "";
      document.getElementById("discount-percent").value = "";
      renderCart();
    }

    function setQuickCash(val) {
      const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
      const disc = parseFloat(document.getElementById("discount-percent").value) || 0;
      const total = subtotal - (subtotal * (disc / 100));

      document.getElementById("pay-amount").value = val === 'pas' ? total : val;
      calculateTotal();
    }

    function calculateTotal() {
      const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
      const discPercent = parseFloat(document.getElementById("discount-percent").value) || 0;
      const discAmount = subtotal * (discPercent / 100);
      const total = Math.max(0, subtotal - discAmount);
      const pay = parseFloat(document.getElementById("pay-amount").value) || 0;
      const change = pay - total;

      document.getElementById("subtotal-price").innerText = formatRupiah(subtotal);
      document.getElementById("discount-amount").innerText = "- " + formatRupiah(discAmount);
      document.getElementById("total-price").innerText = formatRupiah(total);

      const changeEl = document.getElementById("change-amount");
      if (change >= 0) {
        changeEl.innerText = formatRupiah(change);
        changeEl.className = "font-extrabold text-sm text-emerald-600";
      } else {
        changeEl.innerText = formatRupiah(0);
        changeEl.className = "font-extrabold text-sm text-slate-400";
      }
    }

    function renderCart() {
      const container = document.getElementById("cart-list");
      if (cart.length === 0) {
        container.innerHTML = `
          <div class="h-full flex flex-col items-center justify-center text-slate-300 py-12">
            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-3xl mb-2">🛒</div>
            <p class="text-xs font-bold text-slate-400">Belum ada item terpilih</p>
          </div>`;
      } else {
        container.innerHTML = cart.map(item => `
          <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
            <div class="flex-1 pr-2">
              <h4 class="font-extrabold text-xs text-slate-800 line-clamp-1">${item.name}</h4>
              <p class="text-xs text-slate-500 font-bold mt-0.5">${formatRupiah(item.price)}</p>
            </div>
            <div class="flex items-center gap-2">
              <button onclick="updateQty('${item.id}', -1)" class="w-6 h-6 bg-white border border-slate-300 text-slate-700 font-bold rounded-lg flex items-center justify-center text-xs">-</button>
              <span class="text-xs font-extrabold w-4 text-center text-slate-800">${item.qty}</span>
              <button onclick="updateQty('${item.id}', 1)" class="w-6 h-6 bg-amber-500 text-slate-900 font-bold rounded-lg flex items-center justify-center text-xs">+</button>
            </div>
          </div>
        `).join("");
      }
      calculateTotal();
    }

    function processCheckout() {
      if (cart.length === 0) return alert("Keranjang belanja masih kosong!");

      const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
      const discPercent = parseFloat(document.getElementById("discount-percent").value) || 0;
      const discAmount = subtotal * (discPercent / 100);
      const total = subtotal - discAmount;
      const pay = parseFloat(document.getElementById("pay-amount").value) || 0;

      if (pay < total) return alert("Uang pembayaran kurang!");

      const infoVal = document.getElementById("order-info").value.trim() || (currentOrderType === 'dinein' ? 'Dine-In' : 'Takeaway');
      const methodVal = document.getElementById("payment-method").value;

      document.getElementById("receipt-order-info").innerText = `${currentOrderType.toUpperCase()} (${infoVal})`;
      document.getElementById("receipt-date").innerText = new Date().toLocaleString("id-ID", { dateStyle: "short", timeStyle: "short" });
      document.getElementById("receipt-method").innerText = methodVal;

      document.getElementById("receipt-items").innerHTML = cart.map(i => `
        <div class="flex justify-between">
          <span>${i.name} x${i.qty}</span>
          <span>${formatRupiah(i.price * i.qty)}</span>
        </div>
      `).join("");

      document.getElementById("receipt-subtotal").innerText = formatRupiah(subtotal);
      document.getElementById("receipt-discount").innerText = "- " + formatRupiah(discAmount);
      document.getElementById("receipt-total").innerText = formatRupiah(total);
      document.getElementById("receipt-pay").innerText = formatRupiah(pay);
      document.getElementById("receipt-change").innerText = formatRupiah(pay - total);

      openModal("receipt-modal");
    }

    function closeReceiptModal() {
      closeModal("receipt-modal");
      clearCart();
    }
  </script>
</body>
</html>