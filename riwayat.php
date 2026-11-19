<?php
session_start();
include 'config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : "";
$pesanans = [];
$ada_tabel = false;
$cek = mysqli_query($conn, "SHOW TABLES LIKE 'pesanan'");
if(mysqli_num_rows($cek) > 0){ $ada_tabel = true; }

// ===== CEK NAMA KOLOM YANG BENER-BENER ADA DI TABEL KAMU =====
$cols = [];
$kolom_tanggal = "id_pesanan"; // default aman, kalau nggak ada kolom tanggal, urutkan pakai ID
$kolom_nama = "nama_pembeli";
$kolom_hp = "hp";
$kolom_total = "total";
$kolom_status = "status";
$kolom_alamat = "alamat";
$kolom_metode = "metode";

if($ada_tabel){
  $cq = mysqli_query($conn, "SHOW COLUMNS FROM pesanan");
  while($c = mysqli_fetch_assoc($cq)){ $cols[] = $c['Field']; }
  // Cari kolom tanggal yang ada
  foreach(['tanggal','created_at','tanggal_pesanan','tgl','waktu','date'] as $k){
    if(in_array($k, $cols)){ $kolom_tanggal = $k; break; }
  }
  // Cari kolom nama yang ada
  if(!in_array('nama_pembeli', $cols) && in_array('nama', $cols)) $kolom_nama = 'nama';
  if(!in_array('hp', $cols) && in_array('no_hp', $cols)) $kolom_hp = 'no_hp';
  if(!in_array('hp', $cols) && in_array('telp', $cols)) $kolom_hp = 'telp';
  if(!in_array('total', $cols) && in_array('total_harga', $cols)) $kolom_total = 'total_harga';
  if(!in_array('total', $cols) && in_array('total_bayar', $cols)) $kolom_total = 'total_bayar';
}

if($ada_tabel){
  if($cari != ""){
    $c = mysqli_real_escape_string($conn, $cari);
    // Cari pakai kolom yang ada aja, biar nggak error lagi
    $where_cari = "id_pesanan = '$c'";
    if(in_array($kolom_nama, $cols)) $where_cari .= " OR $kolom_nama LIKE '%$c%'";
    if(in_array($kolom_hp, $cols)) $where_cari .= " OR $kolom_hp LIKE '%$c%'";
    $q = mysqli_query($conn, "SELECT * FROM pesanan WHERE $where_cari ORDER BY $kolom_tanggal DESC");
  } else {
    $q = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY $kolom_tanggal DESC LIMIT 20");
  }
  while($p = mysqli_fetch_assoc($q)){ $pesanans[] = $p; }
}

function badge_riwayat($s){
  if($s=='Baru') return '<span style="background:#FFF3CD;color:#8A6D00;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">🆕 Baru - Menunggu Konfirmasi</span>';
  if($s=='Diproses') return '<span style="background:#D1E7FF;color:#084298;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">📦 Diproses - Sedang Dikemas</span>';
  if($s=='Dikirim') return '<span style="background:#D1E7DD;color:#0F5132;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">🚚 Dikirim - Dalam Perjalanan</span>';
  if($s=='Selesai') return '<span style="background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">✅ Selesai - Sudah Diterima</span>';
  if($s=='Batal') return '<span style="background:#F8D7DA;color:#842029;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">❌ Batal</span>';
  return '<span style="background:#eee;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px">'.htmlspecialchars($s).'</span>';
}
function langkah($status){
  $tahap = ['Baru'=>1, 'Diproses'=>2, 'Dikirim'=>3, 'Selesai'=>4, 'Batal'=>0];
  return $tahap[$status] ?? 1;
}
function tampil_tanggal($p, $kolom_tanggal){
  if(!isset($p[$kolom_tanggal]) || $p[$kolom_tanggal]=="") return "-";
  $t = strtotime($p[$kolom_tanggal]);
  if(!$t) return htmlspecialchars($p[$kolom_tanggal]);
  return date('d M Y, H:i', $t);
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Pesanan - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.header{background:#0B8A3D;padding:14px 0}
.header a{color:white;text-decoration:none;font-weight:600}
.logo{font-weight:800;font-size:22px;color:white}
.logo span{color:#FF8A00}
.card-pesanan{background:white;border-radius:20px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;margin-bottom:16px}
.btn-belanja{background:#FF8A00;color:white;font-weight:700;border-radius:12px;padding:12px 24px;text-decoration:none;display:inline-block}
.btn-belanja:hover{background:#E67E00;color:white}
.btn-wa{background:#25D366;color:white;font-weight:700;border-radius:999px;padding:8px 18px;text-decoration:none;font-size:13px}
.track{display:flex;align-items:center;gap:0;margin-top:14px}
.dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.dot.on{background:#0B8A3D;color:white}
.dot.off{background:#E9ECEF;color:#999}
.line{height:4px;flex:1;border-radius:99px}
.line.on{background:#0B8A3D}
.line.off{background:#E9ECEF}
.harga{color:#0E7A3D;font-weight:800}
</style></head><body>

<div class="header">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="logo">Pasar<span>Tradisional</span>.id</a>
    <div class="d-flex gap-3">
      <a href="index.php">Beranda</a>
      <a href="keranjang.php">Keranjang</a>
      <a href="riwayat.php" style="color:#FFC107">Riwayat Pesanan</a>
    </div>
  </div>
</div>

<div class="container py-4" style="max-width:800px">
  <h1 style="font-weight:800">📋 Riwayat Pesanan</h1>
  <p class="text-muted">Lacak pesanan Kopi, Batik Besurek, & Keripik Pisang kamu di sini.</p>

  <form method="GET" class="d-flex gap-2 mt-3">
    <input name="cari" value="<?php echo htmlspecialchars($cari); ?>" class="form-control" placeholder="Cari pakai Nama / No. HP / ID Pesanan... Contoh: Reno / 0812 / 1" style="border-radius:12px;padding:12px">
    <button class="btn btn-dark" style="border-radius:12px;font-weight:700;padding:0 22px">Cari</button>
  </form>
  <?php if($cari != ""): ?><a href="riwayat.php" style="font-size:13px">✕ Hapus pencarian, lihat semua</a><?php endif; ?>

  <?php if(!$ada_tabel): ?>
    <div class="card-pesanan text-center py-5 mt-4">
      <div style="font-size:60px">📦</div>
      <h4 style="font-weight:800">Belum Ada Riwayat</h4>
      <p class="text-muted">Tabel pesanan belum dibuat. Buka dulu admin pesanan & klik Buat Tabel.</p>
      <a href="admin/pesanan.php" class="btn-belanja">Ke Admin Pesanan →</a>
    </div>
  <?php elseif(empty($pesanans)): ?>
    <div class="card-pesanan text-center py-5 mt-4">
      <div style="font-size:70px">🛒</div>
      <h4 style="font-weight:800">Belum Ada Pesanan<?php echo $cari != "" ? " untuk \"".htmlspecialchars($cari)."\"" : ""; ?></h4>
      <p class="text-muted">Yuk belanja dulu, nanti riwayatnya muncul di sini otomatis.</p>
      <a href="index.php" class="btn-belanja mt-1">Belanja Sekarang →</a>
      <div class="row g-3 mt-4 text-start">
        <div class="col-4"><div style="background:#F6F7F3;border-radius:14px;padding:12px;text-align:center"><img src="uploads/kopi.jpeg" style="width:100%;height:80px;object-fit:cover;border-radius:10px"><br><b>Kopi Bengkulu</b><br><span class="harga">Rp 75.000</span></div></div>
        <div class="col-4"><div style="background:#F6F7F3;border-radius:14px;padding:12px;text-align:center"><img src="uploads/batik basurek.jpg" style="width:100%;height:80px;object-fit:cover;border-radius:10px"><br><b>Batik Besurek</b><br><span class="harga">Rp 150.000</span></div></div>
        <div class="col-4"><div style="background:#F6F7F3;border-radius:14px;padding:12px;text-align:center"><img src="uploads/keripik p.jpg" style="width:100%;height:80px;object-fit:cover;border-radius:10px"><br><b>Keripik Pisang</b><br><span class="harga">Rp 25.000</span></div></div>
      </div>
    </div>
  <?php else: ?>
    <?php foreach($pesanans as $p): 
      $nama_tampil = $p[$kolom_nama] ?? '-';
      $hp_tampil = $p[$kolom_hp] ?? '-';
      $alamat_tampil = $p[$kolom_alamat] ?? '-';
      $metode_tampil = $p[$kolom_metode] ?? '-';
      $total_tampil = $p[$kolom_total] ?? 0;
      $status_tampil = $p[$kolom_status] ?? 'Baru';
      $step = langkah($status_tampil);
    ?>
    <div class="card-pesanan mt-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <b style="font-size:17px">Pesanan #<?php echo $p['id_pesanan']; ?></b>
          <span class="text-muted"> • <?php echo tampil_tanggal($p, $kolom_tanggal); ?></span><br>
          <span>Atas nama <b><?php echo htmlspecialchars($nama_tampil); ?></b> • <?php echo htmlspecialchars($metode_tampil); ?></span><br>
          <small class="text-muted">📍 <?php echo htmlspecialchars($alamat_tampil); ?> • 📞 <?php echo htmlspecialchars($hp_tampil); ?></small>
        </div>
        <div class="text-end">
          <?php echo badge_riwayat($status_tampil); ?><br>
          <b class="harga" style="font-size:19px">Rp <?php echo number_format($total_tampil,0,',','.'); ?></b>
        </div>
      </div>

      <?php if($status_tampil != 'Batal'): ?>
      <div class="track">
        <div class="dot <?php echo $step>=1?'on':'off'; ?>">✓</div><div class="line <?php echo $step>=2?'on':'off'; ?>"></div>
        <div class="dot <?php echo $step>=2?'on':'off'; ?>">📦</div><div class="line <?php echo $step>=3?'on':'off'; ?>"></div>
        <div class="dot <?php echo $step>=3?'on':'off'; ?>">🚚</div><div class="line <?php echo $step>=4?'on':'off'; ?>"></div>
        <div class="dot <?php echo $step>=4?'on':'off'; ?>">✅</div>
      </div>
      <div class="d-flex justify-content-between" style="font-size:11px;color:#666"><span>Baru</span><span>Diproses</span><span>Dikirim</span><span>Selesai</span></div>
      <?php else: ?>
      <div class="alert alert-danger mt-3 mb-0" style="border-radius:12px;font-size:13px">Pesanan ini dibatalkan. Hubungi admin untuk info lebih lanjut.</div>
      <?php endif; ?>

      <div class="d-flex gap-2 mt-3 flex-wrap">
        <a href="https://wa.me/6281234567890?text=<?php echo urlencode('Halo Admin, saya mau tanya pesanan #'.$p['id_pesanan'].' atas nama '.$nama_tampil.', statusnya '.$status_tampil); ?>" target="_blank" class="btn-wa">💬 Tanya Admin via WA</a>
        <?php if($status_tampil=='Selesai'): ?><a href="index.php" class="btn-belanja" style="padding:8px 18px;font-size:13px">Beli Lagi →</a><?php endif; ?>
        <?php if($status_tampil=='Baru'): ?><span style="font-size:13px;color:#8A6D00;background:#FFF3CD;border-radius:999px;padding:8px 14px;font-weight:700">⏳ Menunggu konfirmasi admin, ditunggu ya!</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body></html>