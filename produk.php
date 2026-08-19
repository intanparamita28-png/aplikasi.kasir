<?php
require_once 'koneksi.php';

// --- LOGIKA FORM HANDLER ---
$message = '';

// A. TAMBAH PRODUK
if (isset($_POST['add_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];

    $query = "INSERT INTO products (id, name, category, price, stock) VALUES ('$id', '$name', '$category', $price, $stock)";
    if (mysqli_query($conn, $query)) {
        $message = "Produk berhasil ditambahkan!";
    } else {
        $message = "Gagal menambah produk: " . mysqli_error($conn);
    }
}

// B. EDIT PRODUK
if (isset($_POST['edit_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];

    $query = "UPDATE products SET name='$name', category='$category', price=$price, stock=$stock WHERE id='$id'";
    if (mysqli_query($conn, $query)) {
        $message = "Data produk berhasil diperbarui!";
    } else {
        $message = "Gagal memperbarui produk: " . mysqli_error($conn);
    }
}

// C. HAPUS PRODUK
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: /kasir/produk.php");
    exit;
}

// Ambil semua data produk
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Stok & Produk - POS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased overflow-x-hidden">

  <div class="flex h-screen">
    
    <!-- SIDEBAR NAVIGASI -->
    <aside class="w-20 bg-slate-900 flex flex-col items-center py-6 justify-between shadow-xl z-30 shrink-0 select-none">
      <div class="flex flex-col items-center gap-8 w-full">
        <!-- Logo Home -->
        <a href="/kasir/index.php" title="Halaman Kasir" class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-slate-900 font-black text-xl shadow-lg shadow-amber-500/30 hover:scale-105 transition cursor-pointer">
          ☕
        </a>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-4 w-full px-3">
          <!-- 1. Kasir -->
          <a href="/kasir/index.php" title="Halaman Kasir" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </a>

          <!-- 2. Laporan -->
          <a href="/kasir/laporan.php" title="Laporan Penjualan" class="p-3 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-xl transition flex justify-center items-center cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          </a>

          <!-- 3. Produk (Aktif) -->
          <a href="/kasir/produk.php" title="Manajemen Produk" class="p-3 bg-amber-500/20 text-amber-400 rounded-xl transition flex justify-center items-center cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          </a>
        </nav>
      </div>

      <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">KS</div>
    </aside>

    <!-- CONTENT AREA -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
      
      <!-- Header -->
      <header class="px-8 py-5 bg-white border-b border-slate-200/80 flex justify-between items-center shrink-0">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Produk & Stok</h1>
          <p class="text-xs text-slate-400 font-medium">Kelola daftar menu, harga, dan ketersediaan barang</p>
        </div>
        <button onclick="openModal('add')" class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition">
          <span>+</span> Tambah Menu Baru
        </button>
      </header>

      <!-- Alert Notification -->
      <?php if ($message): ?>
        <div class="mx-8 mt-4 p-3 bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl flex justify-between items-center">
          <span><?= $message; ?></span>
          <button onclick="this.parentElement.remove()" class="text-emerald-500 font-black">✕</button>
        </div>
      <?php endif; ?>

      <!-- Tabel Daftar Produk -->
      <div class="p-8 flex-1 overflow-y-auto">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase text-slate-400 tracking-wider">
                <th class="py-4 px-6">Kode Produk</th>
                <th class="py-4 px-6">Nama Menu</th>
                <th class="py-4 px-6">Kategori</th>
                <th class="py-4 px-6">Harga</th>
                <th class="py-4 px-6">Stok Sisa</th>
                <th class="py-4 px-6 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
              <?php if (mysqli_num_rows($products) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                  <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-4 px-6 font-mono font-bold text-slate-400"><?= $p['id']; ?></td>
                    <td class="py-4 px-6 font-bold text-slate-900"><?= htmlspecialchars($p['name']); ?></td>
                    <td class="py-4 px-6">
                      <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                        <?= htmlspecialchars($p['category']); ?>
                      </span>
                    </td>
                    <td class="py-4 px-6 font-black text-amber-600">Rp <?= number_format($p['price'], 0, ',', '.'); ?></td>
                    <td class="py-4 px-6">
                      <?php if ($p['stock'] > 10): ?>
                        <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg text-xs font-bold"><?= $p['stock']; ?> unit</span>
                      <?php elseif ($p['stock'] > 0): ?>
                        <span class="bg-amber-50 text-amber-600 px-2.5 py-1 rounded-lg text-xs font-bold">Sisa <?= $p['stock']; ?></span>
                      <?php else: ?>
                        <span class="bg-rose-50 text-rose-600 px-2.5 py-1 rounded-lg text-xs font-bold">Habis</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-4 px-6 text-center space-x-2">
                      <button onclick='openModal("edit", <?= json_encode($p); ?>)' class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition">Edit</button>
                      <a href="/kasir/produk.php?delete=<?= $p['id']; ?>" onclick="return confirm('Yakin ingin menghapus menu ini?')" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg font-bold transition">Hapus</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-8 text-slate-400">Belum ada produk tersimpan di database.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <!-- MODAL FORM (TAMBAH / EDIT) -->
  <div id="product-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative">
      <h3 id="modal-title" class="text-lg font-extrabold text-slate-900 mb-4">Tambah Menu Baru</h3>
      
      <form method="POST" action="/kasir/produk.php" class="space-y-4">
        <input type="hidden" name="add_product" id="form-action-add" value="1">
        <input type="hidden" name="edit_product" id="form-action-edit" value="1" disabled>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kode Produk (ID)</label>
          <input type="text" name="id" id="field-id" required placeholder="Contoh: 8991005" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl font-mono text-xs focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Menu</label>
          <input type="text" name="name" id="field-name" required placeholder="Nama makanan / minuman" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kategori</label>
            <select name="category" id="field-category" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none bg-white">
              <option value="Minuman">Minuman</option>
              <option value="Makanan">Makanan</option>
              <option value="Dessert">Dessert</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Harga (Rp)</label>
            <input type="number" name="price" id="field-price" required placeholder="15000" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Stok Awal / Sisa</label>
          <input type="number" name="stock" id="field-stock" required placeholder="20" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
        </div>

        <div class="flex gap-3 pt-2">
          <button type="button" onclick="closeModal()" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition">Batal</button>
          <button type="submit" class="w-1/2 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold rounded-xl text-xs shadow-lg shadow-amber-500/20 transition">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(mode, data = null) {
      const modal = document.getElementById("product-modal");
      const title = document.getElementById("modal-title");
      const addAction = document.getElementById("form-action-add");
      const editAction = document.getElementById("form-action-edit");
      const idField = document.getElementById("field-id");

      modal.classList.remove("hidden");

      if (mode === "edit" && data) {
        title.innerText = "Edit Menu Produk";
        addAction.disabled = true;
        editAction.disabled = false;

        idField.value = data.id;
        idField.readOnly = true;
        idField.classList.add("bg-slate-100");

        document.getElementById("field-name").value = data.name;
        document.getElementById("field-category").value = data.category;
        document.getElementById("field-price").value = data.price;
        document.getElementById("field-stock").value = data.stock;
      } else {
        title.innerText = "Tambah Menu Baru";
        addAction.disabled = false;
        editAction.disabled = true;

        idField.readOnly = false;
        idField.classList.remove("bg-slate-100");

        document.getElementById("field-id").value = "";
        document.getElementById("field-name").value = "";
        document.getElementById("field-category").value = "Minuman";
        document.getElementById("field-price").value = "";
        document.getElementById("field-stock").value = "";
      }
    }

    function closeModal() {
      document.getElementById("product-modal").classList.add("hidden");
    }
  </script>
</body>
</html>