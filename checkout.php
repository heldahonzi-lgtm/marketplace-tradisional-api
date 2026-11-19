<?php
session_start();
include 'config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }

// ===== GAMBAR LOKAL - SAMA KAYAK KERANJANG & ADMIN =====
function gambar_checkout($raw){
  $raw = trim($raw ?? '');
  if($raw == "") return "uploads/kopi.jpeg";
  if(strpos($raw, "http") === 0){ return $raw; }
  if(strpos($raw, "uploads/") === 0){ return str_replace(" ", "%20", $raw); }
  return "uploads/".str_replace(" ", "%20", basename($raw));
}

// Ambil keranjang
$keranjang = $_SESSION['keranjang'] ?? [];
$cart = [];
foreach($keranjang as $k => $v){
  if(is_array($v)){
    $id = $v['id_produk'] ?? $v['id'] ?? $k;
    $qty = $v['jumlah'] ?? $v['qty'] ?? 1;
    $cart[$id] = $qty;
  } else {
    $cart[$k] = $v;
  }
}

// Kalau keranjang kosong, balik ke keranjang
if(empty($cart) && !isset($_SESSION['pesanan_terakhir'])){
  header("Location: keranjang.php");
  exit;
}

// Ambil produk
$items = [];
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
if($total >= 200000) $ongkir = 0; // Gratis ongkir 200rb+
$grand_total = $total + $ongkir;

// Proses checkout
$sukses = false;
if(isset($_POST['checkout'])){
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $hp = mysqli_real_escape_string($conn, $_POST['hp']);
  $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
  $metode = mysqli_real_escape_string($conn, $_POST['metode']);
  $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

  // ===== PERBAIKAN: PASTIKAN TABEL & KOLOM PESANAN ADA =====
  mysqli_query($conn, "CREATE TABLE IF NOT EXISTS pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pembeli VARCHAR(100) NOT NULL,
    hp VARCHAR(30),
    alamat TEXT,
    metode VARCHAR(50),
    total INT DEFAULT 0,
    status VARCHAR(30) DEFAULT 'Baru',
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
  )");
  // Kalau tabel lama belum punya kolom ini, tambahin otomatis
  $cols = [];
  $cq = mysqli_query($conn, "SHOW COLUMNS FROM pesanan");
  while($c = mysqli_fetch_assoc($cq)){ $cols[] = $c['Field']; }
  if(!in_array('hp', $cols)) mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN hp VARCHAR(30)");
  if(!in_array('alamat', $cols)) mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN alamat TEXT");
  if(!in_array('metode', $cols)) mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN metode VARCHAR(50)");
  if(!in_array('status', $cols)) mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN status VARCHAR(30) DEFAULT 'Baru'");
  if(!in_array('tanggal', $cols) && !in_array('created_at', $cols)) mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN tanggal DATETIME DEFAULT CURRENT_TIMESTAMP");
  if(!in_array('nama_pembeli', $cols) && in_array('nama', $cols)){
    mysqli_query($conn, "INSERT INTO pesanan (nama, hp, alamat, metode, total, status, tanggal) VALUES ('$nama', '$hp', '$alamat', '$metode', '$grand_total', 'Baru', NOW())");
  } else {
    mysqli_query($conn, "INSERT INTO pesanan (nama_pembeli, hp, alamat, metode, total, status, tanggal) VALUES ('$nama', '$hp', '$alamat', '$metode', '$grand_total', 'Baru', NOW())");
  }
  $id_pesanan_baru = mysqli_insert_id($conn);

  // Kurangi stok
  foreach($items as $p){
    mysqli_query($conn, "UPDATE produk SET stok = stok - ".$p['qty']." WHERE id_produk = ".$p['id_produk']." AND stok >= ".$p['qty']);
  }

  $_SESSION['pesanan_terakhir'] = [
    'id_pesanan' => $id_pesanan_baru,
    'nama' => $_POST['nama'],
    'hp' => $_POST['hp'],
    'alamat' => $_POST['alamat'],
    'metode' => $_POST['metode'],
    'total' => $grand_total,
    'items' => $items
  ];
  $_SESSION['keranjang'] = [];
  $sukses = true;
}

// Kalau baru sukses, tampilkan struk
$pesanan = $_SESSION['pesanan_terakhir'] ?? null;
if($sukses || ($pesanan && empty($cart))){
  $tampil_sukses = true;
} else {
  $tampil_sukses = false;
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.header{background:#0B8A3D;padding:14px 0}
.header a{color:white;text-decoration:none;font-weight:600}
.logo{font-weight:800;font-size:22px;color:white}
.logo span{color:#FF8A00}
.card-box{background:white;border-radius:20px;padding:22px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none}
.img-checkout{width:60px;height:60px;object-fit:cover;border-radius:12px;background:#eee}
.form-control, .form-select{border-radius:12px;padding:12px 14px}
.form-control:focus{border-color:#0B8A3D;box-shadow:0 0 0 3px rgba(11,138,61,.15)}
.btn-bayar{background:#FF8A00;color:white;font-weight:800;border-radius:12px;padding:14px;border:none;width:100%;font-size:16px}
.btn-bayar:hover{background:#E67E00;color:white}
.metode{border:2px solid #eee;border-radius:14px;padding:12px;cursor:pointer}
.metode input{display:none}
.metode:has(input:checked){border-color:#0B8A3D;background:#EAF7EF}
.success-box{background:white;border-radius:24px;padding:40px 20px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.harga{color:#0E7A3D;font-weight:800}
</style></head><body>

<div class="header">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="logo">Pasar<span>Tradisional</span>.id</a>
    <div class="d-flex gap-3">
      <a href="keranjang.php">← Kembali ke Keranjang</a>
      <a href="index.php">Beranda</a>
      <a href="riwayat.php">Riwayat Pesanan</a>
    </div>
  </div>
</div>

<div class="container py-4">

<?php if($tampil_sukses && $pesanan): ?>
  <!-- ===== SUKSES ===== -->
  <div class="success-box">
    <div style="font-size:70px">✅</div>
    <h2 style="font-weight:800">Pesanan Berhasil!</h2>
    <p class="text-muted">Terima kasih <b><?php echo htmlspecialchars($pesanan['nama']); ?></b>, pesananmu sudah kami terima.<br>Admin akan menghubungi via WA ke <b><?php echo htmlspecialchars($pesanan['hp']); ?></b></p>
    <?php if(!empty($pesanan['id_pesanan'])): ?><div style="background:#EAF7EF;border-radius:12px;padding:10px 16px;display:inline-block;font-weight:700;color:#0E7A3D">Nomor Pesanan: #<?php echo $pesanan['id_pesanan']; ?> • Status: Baru - Menunggu Konfirmasi</div><?php endif; ?>
    <div class="card-box text-start mx-auto mt-3" style="max-width:500px">
      <div class="d-flex justify-content-between"><span>Nama</span><b><?php echo htmlspecialchars($pesanan['nama']); ?></b></div>
      <div class="d-flex justify-content-between mt-2"><span>Alamat</span><b style="text-align:right"><?php echo htmlspecialchars($pesanan['alamat']); ?></b></div>
      <div class="d-flex justify-content-between mt-2"><span>Pembayaran</span><b><?php echo htmlspecialchars($pesanan['metode']); ?></b></div>
      <hr>
      <?php foreach($pesanan['items'] as $p): ?>
      <div class="d-flex justify-content-between mt-1"><span><?php echo htmlspecialchars($p['nama_produk']); ?> x <?php echo $p['qty']; ?></span><b>Rp <?php echo number_format($p['subtotal'],0,',','.'); ?></b></div>
      <?php endforeach; ?>
      <hr>
      <div class="d-flex justify-content-between"><span style="font-weight:800">TOTAL BAYAR</span><b class="harga" style="font-size:20px">Rp <?php echo number_format($pesanan['total'],0,',','.'); ?></b></div>
    </div>
    <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
      <a href="riwayat.php?cari=<?php echo urlencode($pesanan['nama']); ?>" class="btn btn-success rounded-pill px-4 py-2" style="font-weight:700">📋 Lihat Riwayat Pesanan →</a>
      <a href="index.php" class="btn btn-outline-success rounded-pill px-4 py-2" style="font-weight:700">Belanja Lagi</a>
    </div>
  </div>
  <?php unset($_SESSION['pesanan_terakhir']); ?>

<?php else: ?>
  <!-- ===== FORM CHECKOUT ===== -->
  <h1 style="font-weight:800">💳 Checkout</h1>
  <p class="text-muted">Lengkapi data pengiriman, lalu klik Buat Pesanan.</p>

  <form method="POST">
  <div class="row">
    <div class="col-md-7">
      <div class="card-box">
        <h5 style="font-weight:800">📦 Data Pengiriman</h5>
        <div class="mt-3">
          <label class="fw-bold">Nama Lengkap *</label>
          <input name="nama" class="form-control" placeholder="Contoh: Reno Dwi Putra" required>
        </div>
        <div class="mt-3">
          <label class="fw-bold">No. HP / WhatsApp *</label>
          <input name="hp" class="form-control" placeholder="08xxxxxxxxxx" required>
        </div>
        <div class="mt-3">
          <label class="fw-bold">Alamat Lengkap *</label>
          <textarea name="alamat" class="form-control" rows="3" placeholder="Nama jalan, RT/RW, Kecamatan, Kota Bengkulu" required></textarea>
        </div>
        <div class="mt-3">
          <label class="fw-bold">Catatan (Opsional)</label>
          <input name="catatan" class="form-control" placeholder="Contoh: Tolong bungkus rapi, titip di teras">
        </div>
      </div>

      <div class="card-box mt-3">
        <h5 style="font-weight:800">💰 Metode Pembayaran</h5>
        <div class="row g-2 mt-2">
          <div class="col-6 col-md-4"><label class="metode d-block text-center"><input type="radio" name="metode" value="COD - Bayar di Tempat" checked><div style="font-size:28px">💵</div><b>COD</b><br><small class="text-muted">Bayar di Tempat</small></label></div>
          <div class="col-6 col-md-4"><label class="metode d-block text-center"><input type="radio" name="metode" value="Transfer Bank"><div style="font-size:28px">🏦</div><b>Transfer</b><br><small class="text-muted">BRI / BCA / Mandiri</small></label></div>
          <div class="col-6 col-md-4"><label class="metode d-block text-center"><input type="radio" name="metode" value="QRIS"><div style="font-size:28px">📱</div><b>QRIS</b><br><small class="text-muted">Scan QR</small></label></div>
        </div>
        <div class="alert alert-info mt-3" style="border-radius:12px;font-size:13px">
          <b>Transfer ke:</b> BRI 1234-5678-9012 a.n. PasarTradisional.id<br>
          Konfirmasi ke WA setelah transfer ya.
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card-box">
        <h5 style="font-weight:800">Ringkasan Pesanan (<?php echo $total_item; ?> produk)</h5>
        <?php foreach($items as $p): ?>
        <div class="d-flex gap-3 align-items-center mt-3">
          <img src="<?php echo gambar_checkout($p['gambar']); ?>" class="img-checkout">
          <div style="flex:1">
            <b><?php echo htmlspecialchars($p['nama_produk']); ?></b><br>
            <small class="text-muted"><?php echo $p['qty']; ?> x Rp <?php echo number_format($p['harga'],0,',','.'); ?></small>
          </div>
          <b>Rp <?php echo number_format($p['subtotal'],0,',','.'); ?></b>
        </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between"><span>Subtotal</span><b>Rp <?php echo number_format($total,0,',','.'); ?></b></div>
        <div class="d-flex justify-content-between mt-2"><span>Ongkir (Bengkulu)</span><b><?php echo $ongkir == 0 ? "GRATIS 🎉" : "Rp ".number_format($ongkir,0,',','.'); ?></b></div>
        <?php if($total >= 200000): ?><small class="text-success fw-bold">Kamu dapat gratis ongkir!</small><?php endif; ?>
        <hr>
        <div class="d-flex justify-content-between"><span style="font-weight:800;font-size:17px">TOTAL</span><b class="harga" style="font-size:22px">Rp <?php echo number_format($grand_total,0,',','.'); ?></b></div>
        <button type="submit" name="checkout" class="btn-bayar mt-3">Buat Pesanan Sekarang →</button>
        <p class="text-center text-muted mt-2" style="font-size:12px">🔒 Data kamu aman, tidak dibagikan ke pihak lain</p>
      </div>
    </div>
  </div>
  </form>
<?php endif; ?>
</div>
</body></html>