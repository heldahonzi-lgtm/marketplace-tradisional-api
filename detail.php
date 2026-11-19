<?php
include 'config/koneksi.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori=k.id_kategori WHERE p.id_produk='".$id."'"));
if(!$p){ echo "Produk tidak ditemukan. <a href='index.php'>Kembali</a>"; exit; }

// ===== FUNGSI GAMBAR - HARUS SAMA PERSIS DENGAN INDEX.PHP =====
function gambar_produk($raw, $id_produk){
  $raw = trim($raw);
  if(strpos($raw, "http") === 0){ return $raw; }
  if(strpos($raw, "uploads/") !== false && file_exists($raw)){ return $raw; }
  if($raw != "" && file_exists("uploads/".basename($raw))){ return "uploads/".basename($raw); }
  // FOTO CADANGAN - SUDAH DISAMAKAN DENGAN INDEX
  if($id_produk == 2) return "https://images.unsplash.com/photo-1590736969955-71cc94901144?w=800&q=80"; // Batik
  if($id_produk == 3) return "https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=800&q=80"; // Keripik
  return "https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80"; // Kopi - biji kopi, SAMA dengan index
}
$gambar = gambar_produk($p['gambar'], $p['id_produk']);

$lain = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk != '".$id."' ORDER BY RAND() LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($p['nama_produk']); ?> - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.navbar{background:white;box-shadow:0 2px 20px rgba(0,0,0,.06);position:sticky;top:0;z-index:99}
.logo{font-weight:800;font-size:22px;color:#0E7A3D;text-decoration:none}
.logo span{color:#FF8A00}
.breadcrumb a{color:#0E7A3D;text-decoration:none;font-weight:600}
.img-utama{width:100%;height:480px;object-fit:cover;border-radius:24px;box-shadow:0 10px 30px rgba(0,0,0,.12);background:#E5E7EB;display:block}
.badge-kat{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:7px 14px;font-size:13px}
.badge-stok{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:7px 14px;font-size:13px}
.badge-best{background:#FF8A00;color:white;font-weight:800;border-radius:999px;padding:7px 14px;font-size:13px}
.harga{color:#0E7A3D;font-weight:800;font-size:36px;line-height:1}
.harga-coret{color:#9CA3AF;text-decoration:line-through;font-size:18px}
.card-info{background:white;border-radius:20px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none}
.btn-keranjang{display:block;text-align:center;background:#FF8A00;color:white;font-weight:800;border-radius:14px;padding:14px;text-decoration:none;font-size:16px}
.btn-keranjang:hover{background:#E67E00;color:white}
.btn-beli{display:block;text-align:center;background:#0E7A3D;color:white;font-weight:800;border-radius:14px;padding:14px;text-decoration:none;font-size:16px}
.btn-beli:hover{background:#0A5E2F;color:white}
.qty-box{display:flex;align-items:center;gap:0;background:white;border:1px solid #E5E7EB;border-radius:12px;overflow:hidden;width:140px}
.qty-box button{border:none;background:#F3F4F6;width:40px;height:44px;font-weight:800;font-size:18px}
.qty-box input{border:none;text-align:center;width:60px;font-weight:700;outline:none}
.fitur{display:flex;gap:10px;align-items:center;background:#F6F7F3;border-radius:14px;padding:12px}
.card-produk{border:none;border-radius:20px;overflow:hidden;background:white;box-shadow:0 4px 20px rgba(0,0,0,.06);height:100%}
.img-box{height:180px;overflow:hidden;background:#E5E7EB}
.img-box img{width:100%;height:100%;object-fit:cover;display:block}
.badge-stok-kecil{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:5px 10px;font-size:12px}
.harga-kecil{color:#0E7A3D;font-weight:800;font-size:18px}
.btn-lihat{display:block;text-align:center;background:#0E7A3D;color:white;font-weight:700;border-radius:12px;padding:10px;text-decoration:none}
</style></head><body>

<nav class="navbar py-3"><div class="container">
<a class="logo" href="index.php">Pasar<span>Tradisional</span>.id</a>
<div class="d-flex gap-2">
<a href="keranjang.php" class="btn btn-light rounded-pill fw-bold">Keranjang</a>
<a href="admin/produk.php" class="btn btn-dark rounded-pill">Kelola Produk</a>
</div></div></nav>

<div class="container mt-3">
<div class="breadcrumb"><a href="index.php">← Beranda</a> <span class="text-muted">/ <?php echo $p['nama_kategori']; ?> /</span> <b><?php echo htmlspecialchars($p['nama_produk']); ?></b></div>

<div class="row mt-3 g-4">
  <div class="col-md-6">
    <div style="position:relative">
      <!-- ✅ SUDAH DIPERBAIKI: onerror-nya lengkap, pakai $gambar yang sama dengan index -->
      <img src="<?php echo $gambar; ?>" class="img-utama" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80';">
      <span class="badge-best" style="position:absolute;top:16px;left:16px">🔥 TERLARIS</span>
      <span class="badge bg-white text-dark" style="position:absolute;bottom:16px;right:16px;border-radius:999px;padding:8px 14px;box-shadow:0 4px 15px rgba(0,0,0,.15)">📍 Manna, Bengkulu Selatan</span>
    </div>
    <div class="d-flex gap-2 mt-3">
      <img src="<?php echo $gambar; ?>" style="width:110px;height:80px;object-fit:cover;border-radius:12px;border:2px solid #0E7A3D" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80';">
      <div style="width:110px;height:80px;border-radius:12px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;text-align:center;font-size:12px;font-weight:700;color:#9A3412">☕<br>100% Asli<br>Bengkulu</div>
      <div style="width:110px;height:80px;border-radius:12px;background:#ECFDF5;display:flex;align-items:center;justify-content:center;text-align:center;font-size:12px;font-weight:700;color:#065F46">🌿<br>Petani<br>Lokal</div>
      <div style="width:110px;height:80px;border-radius:12px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;text-align:center;font-size:12px;font-weight:700;color:#1E40AF">🚚<br>Pengiriman<br>Cepat</div>
    </div>
  </div>

  <div class="col-md-6">
    <div>
      <span class="badge-kat"><?php echo $p['nama_kategori']; ?></span>
      <span class="badge-stok">✅ Stok Tersedia: <?php echo $p['stok']; ?></span>
    </div>
    <h1 class="mt-3" style="font-weight:800;font-size:34px;line-height:1.1"><?php echo htmlspecialchars($p['nama_produk']); ?></h1>
    <div class="d-flex align-items-center gap-2 mt-2">
      <span style="background:#FEF3C7;padding:5px 10px;border-radius:999px;font-weight:800">⭐ 4.9</span>
      <span class="text-muted">| 127 Terjual | 98 Ulasan</span>
      <span class="text-success fw-bold">| Laris!</span>
    </div>

    <div class="card-info mt-3">
      <div class="d-flex align-items-end gap-2">
        <span class="harga">Rp <?php echo number_format($p['harga'],0,',','.'); ?></span>
        <span class="harga-coret">Rp <?php echo number_format($p['harga']*1.2,0,',','.'); ?></span>
        <span class="badge bg-danger rounded-pill">Hemat 20%</span>
      </div>
      <p class="text-muted small mt-1 mb-0">Harga sudah termasuk packing kayu + bubble wrap. Gratis ongkir area Manna!</p>
      <hr>
      <p style="font-size:15px;line-height:1.6"><?php echo nl2br(htmlspecialchars($p['deskripsi'])); ?></p>

      <div class="row g-2 mt-2">
        <div class="col-6"><div class="fitur"><span style="font-size:22px">🚚</span><div><b style="font-size:13px">Pengiriman Cepat</b><br><small class="text-muted">Sampai 1-2 hari</small></div></div></div>
        <div class="col-6"><div class="fitur"><span style="font-size:22px">🛡️</span><div><b style="font-size:13px">Garansi 100%</b><br><small class="text-muted">Uang kembali</small></div></div></div>
        <div class="col-6"><div class="fitur"><span style="font-size:22px">🌿</span><div><b style="font-size:13px">Asli Bengkulu</b><br><small class="text-muted">Langsung petani</small></div></div></div>
        <div class="col-6"><div class="fitur"><span style="font-size:22px">💬</span><div><b style="font-size:13px">Chat Penjual</b><br><small class="text-muted">Respon &lt; 5 menit</small></div></div></div>
      </div>

      <div class="d-flex align-items-center gap-3 mt-4">
        <span class="fw-bold">Jumlah:</span>
        <div class="qty-box">
          <button type="button" onclick="let i=document.getElementById('qty'); if(i.value>1) i.value--;">-</button>
          <input id="qty" value="1" readonly>
          <button type="button" onclick="let i=document.getElementById('qty'); if(i.value<<?php echo $p['stok']; ?>) i.value++;">+</button>
        </div>
        <span class="text-muted small">Stok: <?php echo $p['stok']; ?></span>
      </div>

      <div class="row g-2 mt-3">
        <div class="col-6"><a href="keranjang.php?aksi=tambah&id=<?php echo $p['id_produk']; ?>" class="btn-keranjang">+ Keranjang</a></div>
        <div class="col-6"><a href="keranjang.php?aksi=tambah&id=<?php echo $p['id_produk']; ?>" class="btn-beli">Beli Sekarang →</a></div>
      </div>
      <a href="index.php" class="btn btn-light w-100 mt-2" style="border-radius:14px;padding:12px;font-weight:700">← Lanjut Belanja</a>
    </div>
  </div>
</div>

<div class="card-info mt-4">
  <h4 class="fw-bold">📝 Deskripsi Lengkap</h4>
  <p><?php echo nl2br(htmlspecialchars($p['deskripsi'])); ?></p>
  <ul>
    <li><b>Berat:</b> 500 gram</li>
    <li><b>Asal:</b> Pegunungan Manna, Bengkulu Selatan</li>
    <li><b>Kemasan:</b> Aluminium foil + ziplock, kedap udara</li>
    <li><b>Cara Seduh:</b> 1 sendok kopi + 150ml air panas 90°C, diamkan 3 menit</li>
  </ul>
  <div class="alert alert-success" style="border-radius:14px"><b>Kenapa harus beli di sini?</b><br>Kopi kami dipetik langsung dari petani di Manna, disangrai setiap hari Jumat, jadi wanginya masih fresh saat sampai di rumah kamu.</div>
</div>

<h4 class="fw-bold mt-5">Kamu Mungkin Juga Suka ❤️</h4>
<div class="row mt-3 g-4">
<?php while($l = mysqli_fetch_assoc($lain)){ $g2 = gambar_produk($l['gambar'], $l['id_produk']); ?>
  <div class="col-md-4">
    <div class="card-produk">
      <!-- ✅ SUDAH DIPERBAIKI JUGA DI SINI -->
      <div class="img-box"><img src="<?php echo $g2; ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80';"></div>
      <div class="p-3">
        <span class="badge-stok-kecil">Stok Tersedia: <?php echo $l['stok']; ?></span>
        <h5 class="mt-2 mb-1" style="font-weight:700"><?php echo htmlspecialchars($l['nama_produk']); ?></h5>
        <p class="text-muted" style="font-size:13px"><?php echo htmlspecialchars($l['deskripsi']); ?></p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga-kecil">Rp <?php echo number_format($l['harga'],0,',','.'); ?></span>
          <span>⭐ 4.9</span>
        </div>
        <a href="detail.php?id=<?php echo $l['id_produk']; ?>" class="btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>
<?php } ?>
</div>

<div class="text-center mt-5 p-4" style="background:#111C12;color:#CBD5CE;border-radius:24px"><h5 class="fw-bold text-white">PasarTradisional.id</h5><p class="small mb-0">Jl. Merdeka No.10, Manna, Bengkulu Selatan - 2026</p></div>
</div></body></html>