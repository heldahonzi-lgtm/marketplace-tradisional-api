<?php
include '../config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }
if(!isset($conn)){ die("Koneksi gagal. Cek config/koneksi.php"); }

// ===== CEK TABEL PESANAN =====
$ada_tabel = false;
$cek = mysqli_query($conn, "SHOW TABLES LIKE 'pesanan'");
if(mysqli_num_rows($cek) > 0){ $ada_tabel = true; }

// ===== CEK KOLOM YANG ADA - BIAR NGGAK ERROR LAGI =====
$cols = [];
$kolom_tanggal = "id_pesanan";
$kolom_nama = "nama_pembeli";
$kolom_hp = "hp";
$kolom_total = "total";
$kolom_status = "status";
$kolom_alamat = "alamat";
$kolom_metode = "metode";

if($ada_tabel){
  $cq = mysqli_query($conn, "SHOW COLUMNS FROM pesanan");
  while($c = mysqli_fetch_assoc($cq)){ $cols[] = $c['Field']; }

  // Kalau kolom tanggal belum ada, tambahin otomatis - ini yang bikin error kemarin
  if(!in_array('tanggal', $cols) && !in_array('created_at', $cols) && !in_array('tanggal_pesanan', $cols)){
    mysqli_query($conn, "ALTER TABLE pesanan ADD COLUMN tanggal DATETIME DEFAULT CURRENT_TIMESTAMP");
    $cols[] = 'tanggal';
  }
  foreach(['tanggal','created_at','tanggal_pesanan','tgl','waktu'] as $k){
    if(in_array($k, $cols)){ $kolom_tanggal = $k; break; }
  }
  if(!in_array('nama_pembeli', $cols) && in_array('nama', $cols)) $kolom_nama = 'nama';
  if(!in_array('hp', $cols) && in_array('no_hp', $cols)) $kolom_hp = 'no_hp';
  if(!in_array('hp', $cols) && in_array('telp', $cols)) $kolom_hp = 'telp';
  if(!in_array('total', $cols) && in_array('total_harga', $cols)) $kolom_total = 'total_harga';
  if(!in_array('total', $cols) && in_array('total_bayar', $cols)) $kolom_total = 'total_bayar';
}

// Buat tabel kalau belum ada - klik tombol di halaman
if(isset($_GET['buat_tabel'])){
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
  // Data contoh biar nggak kosong - sesuai produk kamu
  mysqli_query($conn, "INSERT INTO pesanan (nama_pembeli, hp, alamat, metode, total, status, tanggal) VALUES
    ('Reno Dwi Putra', '081234567890', 'Jl. Merdeka No. 12, Bengkulu', 'COD - Bayar di Tempat', 100000, 'Baru', NOW()),
    ('Sinta', '082345678901', 'Jl. Pariwisata, Pantai Panjang, Bengkulu', 'Transfer Bank', 150000, 'Diproses', NOW()),
    ('Budi', '083456789012', 'Jl. S. Parman, Bengkulu', 'QRIS', 25000, 'Selesai', NOW())
  ");
  header("Location: pesanan.php");
  exit;
}

// Ubah status
if(isset($_GET['status']) && isset($_GET['id'])){
  $id = intval($_GET['id']);
  $st = mysqli_real_escape_string($conn, $_GET['status']);
  if(in_array('status', $cols)){
    mysqli_query($conn, "UPDATE pesanan SET status='$st' WHERE id_pesanan=$id");
  }
  header("Location: pesanan.php");
  exit;
}
// Hapus
if(isset($_GET['hapus'])){
  $id = intval($_GET['hapus']);
  mysqli_query($conn, "DELETE FROM pesanan WHERE id_pesanan=$id");
  header("Location: pesanan.php");
  exit;
}

$pesanans = [];
$total_pesanan = 0; $baru = 0; $diproses = 0; $selesai = 0; $omset = 0;
if($ada_tabel){
  $q = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY $kolom_tanggal DESC");
  while($p = mysqli_fetch_assoc($q)){ $pesanans[] = $p; }
  $total_pesanan = count($pesanans);
  foreach($pesanans as $p){
    $omset += ($p[$kolom_total] ?? 0);
    $st_tmp = $p[$kolom_status] ?? 'Baru';
    if($st_tmp=='Baru') $baru++;
    if($st_tmp=='Diproses') $diproses++;
    if($st_tmp=='Selesai') $selesai++;
  }
}

function badge_status($s){
  if($s=='Baru') return '<span style="background:#FFF3CD;color:#8A6D00;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">🆕 Baru</span>';
  if($s=='Diproses') return '<span style="background:#D1E7FF;color:#084298;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">📦 Diproses</span>';
  if($s=='Dikirim') return '<span style="background:#D1E7DD;color:#0F5132;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">🚚 Dikirim</span>';
  if($s=='Selesai') return '<span style="background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">✅ Selesai</span>';
  if($s=='Batal') return '<span style="background:#F8D7DA;color:#842029;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">❌ Batal</span>';
  return '<span style="background:#eee;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">'.htmlspecialchars($s).'</span>';
}
function tampil_tgl_admin($p, $kolom_tanggal){
  if(!isset($p[$kolom_tanggal]) || $p[$kolom_tanggal]=="") return "-";
  $t = strtotime($p[$kolom_tanggal]);
  if(!$t) return htmlspecialchars($p[$kolom_tanggal]);
  return date('d/m/Y H:i', $t);
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pesanan Masuk - PasarTradisional.id</title>
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
.btn-status{background:white;border:1px solid #ddd;border-radius:999px;padding:5px 10px;font-size:12px;font-weight:700;text-decoration:none;color:#333}
.btn-status:hover{background:#0B3D1E;color:white;border-color:#0B3D1E}
.btn-hapus{background:#FFE4E4;color:#DC3545;border:none;border-radius:999px;padding:6px 12px;font-weight:700;font-size:12px;text-decoration:none}
.btn-wa{background:#25D366;color:white;border-radius:999px;padding:6px 12px;font-weight:700;font-size:12px;text-decoration:none}
</style></head><body>

<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar">
  <div class="logo">Pasar<span>Tradisional</span>.id</div>
  <small>Admin Panel - Bengkulu</small>
  <hr style="border-color:#2A5C40">
  <a href="produk.php">Kelola Produk</a>
  <a href="pesanan.php" class="active">Pesanan Masuk</a>
  <a href="../index.php">Lihat Toko</a>
  <a href="../keranjang.php">Keranjang</a>
</div>

<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <h1 style="font-weight:800">📦 Pesanan Masuk</h1>
      <p class="text-muted">Semua pesanan dari checkout, langsung dari pelanggan Bengkulu.</p>
    </div>
    <a href="../index.php" class="btn btn-dark" style="border-radius:12px;font-weight:700">Lihat Toko →</a>
  </div>

  <?php if(!$ada_tabel): ?>
    <!-- KALAU TABEL BELUM ADA -->
    <div class="table-card text-center py-5">
      <div style="font-size:70px">📦</div>
      <h3 style="font-weight:800">Tabel Pesanan Belum Dibuat</h3>
      <p class="text-muted">Klik tombol di bawah, tabelnya langsung dibuat + diisi 3 contoh pesanan (Reno, Sinta, Budi) biar kamu bisa lihat tampilannya.</p>
      <a href="pesanan.php?buat_tabel=1" class="btn btn-success rounded-pill px-4 py-2" style="font-weight:700">Buat Tabel Pesanan Sekarang</a>
    </div>
  <?php else: ?>

  <div class="row g-3 mt-1">
    <div class="col-md-3"><div class="card-stat" style="background:#0E7A3D"><small>Total Pesanan</small><h2><?php echo $total_pesanan; ?> Pesanan</h2></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#FF8A00"><small>Pesanan Baru</small><h2><?php echo $baru; ?> Pesanan</h2><small>Butuh diproses!</small></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#2563EB"><small>Sedang Diproses</small><h2><?php echo $diproses; ?> Pesanan</h2></div></div>
    <div class="col-md-3"><div class="card-stat" style="background:#7C3AED"><small>Total Omset</small><h2>Rp <?php echo number_format($omset,0,',','.'); ?></h2></div></div>
  </div>

  <div class="table-card mt-4">
    <h4 style="font-weight:800">Daftar Pesanan</h4>
    <?php if(empty($pesanans)): ?>
      <div class="text-center py-5">
        <div style="font-size:60px">🛒</div>
        <h5 style="font-weight:800">Belum Ada Pesanan</h5>
        <p class="text-muted">Coba checkout 1 produk di toko, nanti muncul di sini.</p>
        <a href="../index.php" class="btn btn-success rounded-pill px-4">Ke Toko →</a>
      </div>
    <?php else: ?>
    <div class="table-responsive mt-3">
    <table class="table align-middle">
      <thead><tr><th>ID</th><th>PEMBELI</th><th>ALAMAT / HP</th><th>TOTAL</th><th>STATUS</th><th>AKSI</th></tr></thead>
      <tbody>
      <?php foreach($pesanans as $p): 
        $nama_t = $p[$kolom_nama] ?? '-';
        $hp_t = $p[$kolom_hp] ?? '-';
        $alamat_t = $p[$kolom_alamat] ?? '-';
        $metode_t = $p[$kolom_metode] ?? '-';
        $total_t = $p[$kolom_total] ?? 0;
        $status_t = $p[$kolom_status] ?? 'Baru';
      ?>
        <tr>
          <td><b>#<?php echo $p['id_pesanan']; ?></b><br><small class="text-muted"><?php echo tampil_tgl_admin($p, $kolom_tanggal); ?></small></td>
          <td><b><?php echo htmlspecialchars($nama_t); ?></b><br><small class="text-muted"><?php echo htmlspecialchars($metode_t); ?></small></td>
          <td style="max-width:220px"><small><?php echo htmlspecialchars($alamat_t); ?><br><b>📞 <?php echo htmlspecialchars($hp_t); ?></b></small></td>
          <td style="color:#0E7A3D;font-weight:800">Rp <?php echo number_format($total_t,0,',','.'); ?></td>
          <td><?php echo badge_status($status_t); ?></td>
          <td>
            <div class="d-flex gap-1 flex-wrap" style="min-width:260px">
              <a href="pesanan.php?id=<?php echo $p['id_pesanan']; ?>&status=Diproses" class="btn-status">📦 Diproses</a>
              <a href="pesanan.php?id=<?php echo $p['id_pesanan']; ?>&status=Dikirim" class="btn-status">🚚 Dikirim</a>
              <a href="pesanan.php?id=<?php echo $p['id_pesanan']; ?>&status=Selesai" class="btn-status">✅ Selesai</a>
              <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','',$hp_t); ?>?text=<?php echo urlencode('Halo '.$nama_t.', pesanan #'.$p['id_pesanan'].' di PasarTradisional.id sedang kami proses ya. Total Rp '.number_format($total_t,0,',','.')); ?>" target="_blank" class="btn-wa">WA</a>
              <a href="pesanan.php?hapus=<?php echo $p['id_pesanan']; ?>" class="btn-hapus" onclick="return confirm('Hapus pesanan #<?php echo $p['id_pesanan']; ?>?')">Hapus</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
</div></div>
</body></html>