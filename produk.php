<?php
include '../config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }
if(!isset($conn)){ die("Koneksi gagal. Cek config/koneksi.php"); }

// ===== GAMBAR LOKAL - DARI FOLDER uploads/, BUKAN HTTP =====
function gambar_admin($raw){
  $raw = trim($raw ?? '');
  if($raw == "") return "../uploads/kopi.jpeg";
  if(strpos($raw, "http") === 0){ return $raw; }
  if(strpos($raw, "uploads/") === 0){ return "../".str_replace(" ", "%20", $raw); }
  return "../uploads/".str_replace(" ", "%20", basename($raw));
}

$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM produk"))['t'];
$total_pesanan = 0;
$total_omset = 0;
$cek_pesanan = mysqli_query($conn, "SHOW TABLES LIKE 'pesanan'");
if(mysqli_num_rows($cek_pesanan) > 0){
  $total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pesanan"))['t'];
  // OMSET - AMAN, CEK DULU NAMA KOLOM TOTALNYA APA
  $cols_p = [];
  $cq_p = mysqli_query($conn, "SHOW COLUMNS FROM pesanan");
  while($c_p = mysqli_fetch_assoc($cq_p)){ $cols_p[] = $c_p['Field']; }
  $kolom_total_p = "";
  if(in_array('total', $cols_p)) $kolom_total_p = 'total';
  elseif(in_array('total_harga', $cols_p)) $kolom_total_p = 'total_harga';
  elseif(in_array('total_bayar', $cols_p)) $kolom_total_p = 'total_bayar';
  elseif(in_array('grand_total', $cols_p)) $kolom_total_p = 'grand_total';
  elseif(in_array('harga_total', $cols_p)) $kolom_total_p = 'harga_total';
  if($kolom_total_p != ""){
    $om = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM($kolom_total_p) as t FROM pesanan"));
    $total_omset = $om['t'] ?? 0;
  }
}
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM produk WHERE stok < 5"))['t'];

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";
$where = $cari != "" ? "WHERE p.nama_produk LIKE '%$cari%'" : "";
$q = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori=k.id_kategori $where ORDER BY p.id_produk ASC");
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Produk - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.sidebar{background:#0B3D1E;min-height:100vh;color:white;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:#CBD5CE;text-decoration:none;display:block;padding:12px 14px;border-radius:12px;font-weight:600;margin-bottom:6px}
.sidebar a.active,.sidebar a:hover{background:#1A5C36;color:white}
.logo{font-weight:800;font-size:20px;color:white}
.logo span{color:#FF8A00}
.card-stat{border:none;border-radius:20px;padding:20px;color:white}
.card-stat h2{font-weight:800;margin:0}
.table-card{background:white;border-radius:20px;padding:22px;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.badge-kat{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px}
.btn-edit{background:#FFC107;color:#000;font-weight:700;border-radius:999px;padding:7px 16px;text-decoration:none;font-size:13px;border:none}
.btn-hapus{background:#DC3545;color:white;font-weight:700;border-radius:999px;padding:7px 16px;text-decoration:none;font-size:13px;border:none}
.btn-tambah{background:#FF8A00;color:white;font-weight:700;border-radius:12px;padding:12px 18px;text-decoration:none}
.img-produk{width:80px;height:60px;object-fit:cover;object-position:center;border-radius:10px;background:#eee;display:block;flex-shrink:0}
</style></head><body>

<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar">
  <div class="logo">Pasar<span>Tradisional</span>.id</div>
  <small>Admin Panel - Bengkulu</small>
  <hr style="border-color:#2A5C40">
  <a href="produk.php" class="active">Kelola Produk</a>
  <a href="pesanan.php">Pesanan Masuk <?php if($total_pesanan>0){ echo "<span class='badge bg-warning text-dark float-end'>".$total_pesanan."</span>"; } ?></a>
  <a href="../index.php">Lihat Toko</a>
  <a href="../keranjang.php">Keranjang</a>
  <a href="../riwayat.php">Riwayat Pesanan</a>
</div>

<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <h1 style="font-weight:800">Halo, Admin</h1>
      <p class="text-muted">Kelola toko pasar tradisionalmu di sini.</p>
    </div>
    <a href="tambah.php" class="btn-tambah">+ Tambah Produk Baru</a>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-md-3"><div class="card-stat" style="background:#0E7A3D"><small>Total Produk</small><h2><?php echo $total_produk; ?> Produk</h2></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#FF8A00"><small>Total Pesanan</small><h2><?php echo $total_pesanan; ?> Pesanan</h2><?php if($total_pesanan>0){ echo '<small><a href="pesanan.php" style="color:white;text-decoration:underline">Lihat Pesanan →</a></small>'; } ?></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#2563EB"><small>Total Omset</small><h2>Rp <?php echo number_format($total_omset,0,',','.'); ?></h2></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#7C3AED"><small>Stok Menipis</small><h2><?php echo $stok_menipis; ?> Produk</h2></div></div>
  </div>

  <div class="table-card mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h4 style="font-weight:800">Daftar Produk</h4>
      <form method="GET" class="d-flex gap-2"><input name="cari" value="<?php echo htmlspecialchars($cari); ?>" class="form-control" placeholder="Cari produk..." style="border-radius:12px"><button class="btn btn-dark" style="border-radius:12px">Cari</button></form>
    </div>
    <div class="table-responsive mt-3">
    <table class="table align-middle">
      <thead><tr><th>PRODUK</th><th>KATEGORI</th><th>HARGA</th><th>STOK</th><th class="text-end">AKSI</th></tr></thead>
      <tbody>
      <?php while($p = mysqli_fetch_assoc($q)){ $g = gambar_admin($p['gambar']); ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <img src="<?php echo $g; ?>" class="img-produk">
              <div><b><?php echo htmlspecialchars($p['nama_produk']); ?></b><br><small class="text-muted">ID #<?php echo $p['id_produk']; ?></small></div>
            </div>
          </td>
          <td><span class="badge-kat"><?php echo $p['nama_kategori'] ? $p['nama_kategori'] : '-'; ?></span></td>
          <td style="color:#0E7A3D;font-weight:800">Rp <?php echo number_format($p['harga'],0,',','.'); ?></td>
          <td><?php echo $p['stok']; ?></td>
          <td class="text-end">
            <a href="edit.php?id=<?php echo $p['id_produk']; ?>" class="btn-edit">Edit</a>
            <a href="hapus.php?id=<?php echo $p['id_produk']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus <?php echo htmlspecialchars($p['nama_produk']); ?>?')">Hapus</a>
          </td>
        </tr>
      <?php } ?>
      <?php if(mysqli_num_rows($q)==0){ echo "<tr><td colspan='5' class='text-center py-4'>Produk tidak ditemukan. <a href='produk.php'>Lihat semua</a></td></tr>"; } ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
</div></div></body></html>