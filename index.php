<?php
include 'config/koneksi.php';

$cari = "";
if(isset($_GET['cari'])){ $cari = mysqli_real_escape_string($conn, $_GET['cari']); }
$kat_filter = 0;
if(isset($_GET['kat'])){ $kat_filter = intval($_GET['kat']); }

$where = "WHERE 1=1";
if($cari != ""){ $where .= " AND p.nama_produk LIKE '%".$cari."%'"; }
if($kat_filter != 0){ $where .= " AND p.id_kategori='".$kat_filter."'"; }

$q = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori=k.id_kategori ".$where." ORDER BY p.id_produk DESC");
$rj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM keranjang WHERE id_user=3"));
$jml = $rj['total'];
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.navbar{background:white;box-shadow:0 2px 20px rgba(0,0,0,.06);position:sticky;top:0;z-index:99}
.logo{font-weight:800;font-size:22px;color:#0E7A3D;text-decoration:none}
.logo span{color:#FF8A00}
.hero{background:linear-gradient(135deg,#0E7A3D,#14A94E);border-radius:24px;color:white;padding:36px}
.hero h1{font-weight:800;font-size:36px;line-height:1.1}
.btn-orange{background:#FF8A00;color:white;font-weight:700;border-radius:12px;padding:10px 18px;border:none}
.search-box{background:white;border-radius:16px;padding:6px 6px 6px 16px;display:flex;box-shadow:0 10px 30px rgba(0,0,0,.15)}
.search-box input{border:none;outline:none;flex:1}
.cat-pill{background:white;border:1px solid #E5E7EB;border-radius:999px;padding:8px 16px;font-weight:600;color:#333;text-decoration:none}
.cat-pill.active,.cat-pill:hover{background:#0E7A3D;color:white}
.card-produk{border:none;border-radius:20px;overflow:hidden;background:white;box-shadow:0 4px 20px rgba(0,0,0,.06);height:100%}
.img-box{height:210px;overflow:hidden;background:#E5E7EB}
.img-box img{width:100%;height:100%;object-fit:cover;display:block}
.badge-stok{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:5px 10px;font-size:12px}
.harga{color:#0E7A3D;font-weight:800;font-size:20px}
.btn-lihat{display:block;text-align:center;background:#0E7A3D;color:white;font-weight:700;border-radius:12px;padding:10px;text-decoration:none}
.btn-lihat:hover{background:#0A5E2F;color:white}
.btn-keranjang{display:block;text-align:center;background:#FF8A00;color:white;font-weight:700;border-radius:12px;padding:10px;text-decoration:none;margin-top:8px}
.btn-keranjang:hover{background:#E67E00;color:white}
.footer{background:#111C12;color:#CBD5CE;border-radius:24px 24px 0 0}
</style></head><body>
<nav class="navbar py-3"><div class="container">
<a class="logo" href="index.php">Pasar<span>Tradisional</span>.id</a>
<div class="d-flex gap-2">
<a href="keranjang.php" class="btn btn-light rounded-pill fw-bold">Keranjang <?php if($jml>0){ echo "<span class='badge bg-danger'>".$jml."</span>"; } ?></a>
<a href="riwayat.php" class="btn btn-light rounded-pill fw-bold">📋 Riwayat Pesanan</a>
<a href="admin/produk.php" class="btn btn-dark rounded-pill">Kelola Produk</a>
</div></div></nav>

<div class="container mt-3">
<div class="hero">
<span class="badge bg-white text-success rounded-pill px-3 py-2">Pasar Online No.1 di Bengkulu</span>
<h1 class="mt-3">Belanja Hasil Pasar<br>Lokal, Fresh dan Asli Bengkulu.</h1>
<p>Kopi Robusta, Batik Besurek, dan Keripik Pisang.</p>
<form method="GET" class="search-box mt-3"><input name="cari" value="<?php echo htmlspecialchars($cari); ?>" placeholder="Cari kopi, batik, keripik..."><button class="btn-orange">Cari</button></form>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap align-items-center">
<b>Kategori:</b>
<a href="index.php" class="cat-pill <?php if($kat_filter==0){ echo "active"; } ?>">Semua</a>
<?php
$kats = mysqli_query($conn, "SELECT * FROM kategori");
while($k = mysqli_fetch_assoc($kats)){
  $act = ($kat_filter == $k['id_kategori']) ? "active" : "";
  echo "<a href='?kat=".$k['id_kategori']."' class='cat-pill ".$act."'>".$k['nama_kategori']."</a>";
}
?>
<span class="ms-auto text-muted small"><?php echo mysqli_num_rows($q); ?> produk ditemukan</span>
</div>

<!-- ===== DI SINI TEMPATNYA - DI DALAM <div class="row"> ===== -->
<div class="row mt-3 g-4">

<!-- 1. KOPI -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/kopi.jpeg" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 20</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Kopi Robusta Bengkulu</h5>
        <p class="text-muted" style="font-size:13px">Kopi robusta asli dari pegunungan Bengkulu</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 75.000</span>
          <span>⭐ 4.9</span>
        </div>
        <a href="detail.php?id=1" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

<!-- 2. BATIK -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/batik basurek.jpg" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 10</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Batik Besurek</h5>
        <p class="text-muted" style="font-size:13px">Batik khas Bengkulu motif Besurek</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 250.000</span>
          <span>⭐ 4.8</span>
        </div>
        <a href="detail.php?id=2" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

<!-- 3. KERIPIK -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/keripik p.jpg" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 50</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Keripik Pisang</h5>
        <p class="text-muted" style="font-size:13px">Keripik pisang manis gurih, renyah khas Bengkulu</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 25.000</span>
          <span>⭐ 4.7</span>
        </div>
        <a href="detail.php?id=3" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

</div>
<!-- ===== SAMPAI DI SINI, JANGAN DI LUAR row ===== -->

<div class="footer mt-5 p-4 text-center"><h5 class="fw-bold text-white">PasarTradisional.id</h5><p class="small mb-0">Jl. Merdeka No.10, Manna, Bengkulu Selatan - 2026</p></div>
</div></body></html>