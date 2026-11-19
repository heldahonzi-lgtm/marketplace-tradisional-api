<?php
session_start();
include 'config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }

// ===== GAMBAR LOKAL - SAMA KAYAK ADMIN & INDEX =====
function gambar_keranjang($raw){
  $raw = trim($raw ?? '');
  if($raw == "") return "uploads/kopi.jpeg";
  if(strpos($raw, "http") === 0){ return $raw; }
  if(strpos($raw, "uploads/") === 0){ return str_replace(" ", "%20", $raw); }
  return "uploads/".str_replace(" ", "%20", basename($raw));
}

// Ambil keranjang dari session
// Format yang didukung: $_SESSION['keranjang'][id_produk] = jumlah
// atau $_SESSION['keranjang'] = [ ['id'=>1,'qty'=>2], ... ]
$keranjang = $_SESSION['keranjang'] ?? [];
$items = [];

// Normalisasi jadi [id => qty]
$cart = [];
foreach($keranjang as $k => $v){
  if(is_array($v)){
    $id = $v['id_produk'] ?? $v['id'] ?? $k;
    $qty = $v['jumlah'] ?? $v['qty'] ?? $v['jumlah_produk'] ?? 1;
    $cart[$id] = $qty;
  } else {
    $cart[$k] = $v;
  }
}

// Hapus item
if(isset($_GET['hapus'])){
  $id = intval($_GET['hapus']);
  unset($cart[$id]);
  $_SESSION['keranjang'] = $cart;
  header("Location: keranjang.php");
  exit;
}
// Update jumlah
if(isset($_GET['aksi']) && isset($_GET['id'])){
  $id = intval($_GET['id']);
  if($_GET['aksi'] == 'tambah'){ $cart[$id] = ($cart[$id] ?? 0) + 1; }
  if($_GET['aksi'] == 'kurang'){ $cart[$id] = ($cart[$id] ?? 1) - 1; if($cart[$id] <= 0) unset($cart[$id]); }
  $_SESSION['keranjang'] = $cart;
  header("Location: keranjang.php");
  exit;
}

// Ambil data produk dari database
$total = 0;
$total_item = 0;
if(!empty($cart)){
  $ids = implode(",", array_map('intval', array_keys($cart)));
  $q = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk IN ($ids)");
  while($p = mysqli_fetch_assoc($q)){
    $qty = $cart[$p['id_produk']];
    $subtotal = $p['harga'] * $qty;
    $total += $subtotal;
    $total_item += $qty;
    $p['qty'] = $qty;
    $p['subtotal'] = $subtotal;
    $items[] = $p;
  }
}
$ongkir = $total > 0 ? 10000 : 0;
$grand_total = $total + $ongkir;
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keranjang Belanja - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.header{background:#0B8A3D;padding:14px 0}
.header a{color:white;text-decoration:none;font-weight:600}
.logo{font-weight:800;font-size:22px;color:white}
.logo span{color:#FF8A00}
.card-item{background:white;border-radius:20px;padding:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;margin-bottom:14px}
.img-keranjang{width:90px;height:90px;object-fit:cover;border-radius:14px;background:#eee}
.qty-btn{width:32px;height:32px;border-radius:50%;border:1px solid #ddd;background:white;font-weight:800;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;color:#333}
.qty-btn:hover{background:#0B8A3D;color:white;border-color:#0B8A3D}
.btn-hapus{background:#FFE4E4;color:#DC3545;border:none;border-radius:999px;padding:6px 14px;font-weight:700;font-size:13px;text-decoration:none}
.btn-lanjut{background:#0B8A3D;color:white;font-weight:700;border-radius:12px;padding:14px;text-decoration:none;display:block;text-align:center}
.btn-lanjut:hover{background:#096E31;color:white}
.btn-belanja{background:#FF8A00;color:white;font-weight:700;border-radius:12px;padding:14px 28px;text-decoration:none;display:inline-block}
.btn-belanja:hover{background:#E67E00;color:white}
.summary{background:white;border-radius:20px;padding:22px;box-shadow:0 4px 20px rgba(0,0,0,.06);position:sticky;top:20px}
.empty-box{background:white;border-radius:24px;padding:50px 20px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.promo{background:#FFF3E0;border:1px dashed #FF8A00;border-radius:12px;padding:12px;font-size:13px;font-weight:600;color:#8A4A00}
.harga{color:#0E7A3D;font-weight:800;font-size:17px}
</style></head><body>

<div class="header">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="logo">Pasar<span>Tradisional</span>.id</a>
    <div class="d-flex gap-3">
      <a href="index.php">← Lanjut Belanja</a>
      <a href="admin/produk.php">Admin</a>
    </div>
  </div>
</div>

<div class="container py-4">
  <h1 style="font-weight:800">🛒 Keranjang Belanja</h1>
  <p class="text-muted"><?php echo $total_item; ?> produk dari Pasar Bengkulu</p>

<?php if(empty($items)): ?>
  <!-- ===== KALAU KOSONG - VERSI MENARIK ===== -->
  <div class="empty-box">
    <div style="font-size:80px">🛒</div>
    <h3 style="font-weight:800" class="mt-2">Keranjangmu Masih Kosong</h3>
    <p class="text-muted">Yuk isi dengan kopi Bengkulu, batik Besurek, dan keripik pisang yang enak-enak.</p>
    <a href="index.php" class="btn-belanja mt-2">Belanja Sekarang →</a>
    <div class="row g-3 mt-4 text-start">
      <div class="col-md-4"><div class="card-item text-center"><img src="uploads/kopi.jpeg" class="img-keranjang mb-2"><br><b>Kopi Bengkulu</b><br><span class="harga">Rp 75.000</span><br><a href="index.php" class="btn btn-sm btn-success rounded-pill mt-2 px-3">+ Tambah</a></div></div>
      <div class="col-md-4"><div class="card-item text-center"><img src="uploads/batik basurek.jpg" class="img-keranjang mb-2"><br><b>Batik Besurek</b><br><span class="harga">Rp 150.000</span><br><a href="index.php" class="btn btn-sm btn-success rounded-pill mt-2 px-3">+ Tambah</a></div></div>
      <div class="col-md-4"><div class="card-item text-center"><img src="uploads/keripik p.jpg" class="img-keranjang mb-2"><br><b>Keripik Pisang</b><br><span class="harga">Rp 25.000</span><br><a href="index.php" class="btn btn-sm btn-success rounded-pill mt-2 px-3">+ Tambah</a></div></div>
    </div>
  </div>

<?php else: ?>
  <!-- ===== KALAU ADA ISI ===== -->
  <div class="row">
    <div class="col-md-8">
      <?php foreach($items as $p): ?>
      <div class="card-item d-flex gap-3 align-items-center flex-wrap">
        <img src="<?php echo gambar_keranjang($p['gambar']); ?>" class="img-keranjang">
        <div style="flex:1;min-width:180px">
          <b style="font-size:16px"><?php echo htmlspecialchars($p['nama_produk']); ?></b><br>
          <span class="text-muted" style="font-size:13px">Stok: <?php echo $p['stok']; ?> • Bengkulu</span><br>
          <span class="harga">Rp <?php echo number_format($p['harga'],0,',','.'); ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="keranjang.php?aksi=kurang&id=<?php echo $p['id_produk']; ?>" class="qty-btn">−</a>
          <b style="min-width:24px;text-align:center"><?php echo $p['qty']; ?></b>
          <a href="keranjang.php?aksi=tambah&id=<?php echo $p['id_produk']; ?>" class="qty-btn">+</a>
        </div>
        <div style="min-width:120px;text-align:right">
          <div style="font-size:12px" class="text-muted">Subtotal</div>
          <b>Rp <?php echo number_format($p['subtotal'],0,',','.'); ?></b><br>
          <a href="keranjang.php?hapus=<?php echo $p['id_produk']; ?>" class="btn-hapus mt-1 d-inline-block" onclick="return confirm('Hapus <?php echo htmlspecialchars($p['nama_produk']); ?>?')">🗑 Hapus</a>
        </div>
      </div>
      <?php endforeach; ?>
      <a href="index.php" style="font-weight:700;text-decoration:none;color:#0B8A3D">← Lanjut Belanja</a>
    </div>

    <div class="col-md-4">
      <div class="summary">
        <h5 style="font-weight:800">Ringkasan Belanja</h5>
        <div class="promo mt-2">🎉 Gratis ongkir kalau belanja Rp 200.000+ (Bengkulu kota)</div>
        <div class="d-flex justify-content-between mt-3"><span>Total Barang (<?php echo $total_item; ?>)</span><b>Rp <?php echo number_format($total,0,',','.'); ?></b></div>
        <div class="d-flex justify-content-between mt-2"><span>Ongkir</span><b>Rp <?php echo number_format($ongkir,0,',','.'); ?></b></div>
        <hr>
        <div class="d-flex justify-content-between"><span style="font-weight:800;font-size:17px">TOTAL</span><b style="color:#0E7A3D;font-size:20px">Rp <?php echo number_format($grand_total,0,',','.'); ?></b></div>
        <a href="checkout.php" class="btn-lanjut mt-3">Checkout Sekarang →</a>
        <p class="text-center text-muted mt-2" style="font-size:12px">🔒 Pembayaran aman • COD / Transfer</p>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>
</body></html>