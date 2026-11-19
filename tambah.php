<?php
include '../config/koneksi.php';
if(!isset($conn) && isset($koneksi)){ $conn = $koneksi; }
if(!isset($conn)){ die("Koneksi gagal. Cek config/koneksi.php"); }

$pesan = "";
// Ambil kategori dari database
$kategoris = [];
$qkat = mysqli_query($conn, "SHOW TABLES LIKE 'kategori'");
if(mysqli_num_rows($qkat) > 0){
  $qk = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
  while($k = mysqli_fetch_assoc($qk)){ $kategoris[] = $k; }
}
// Kalau tabel kategori kosong, pakai default
if(empty($kategoris)){
  $kategoris = [
    ['id_kategori'=>1, 'nama_kategori'=>'Makanan'],
    ['id_kategori'=>2, 'nama_kategori'=>'Minuman'],
    ['id_kategori'=>3, 'nama_kategori'=>'Kerajinan'],
  ];
}

if(isset($_POST['simpan'])){
  $id_kategori = intval($_POST['id_kategori']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $harga = intval($_POST['harga']);
  $stok = intval($_POST['stok']);
  $gambar = "";

  // Upload gambar ke ../uploads/
  if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0){
    if(!file_exists("../uploads")){ mkdir("../uploads", 0777, true); }
    $nama_file = basename($_FILES['gambar']['name']);
    // Biar aman, ganti spasi tetap boleh, tapi simpan nama asli kayak "batik baserek.jpg"
    $target = "../uploads/" . $nama_file;
    // Kalau nama sama, tambahin angka biar nggak ketimpa
    $i = 1;
    $nama_asli = pathinfo($nama_file, PATHINFO_FILENAME);
    $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
    while(file_exists($target)){
      $nama_file = $nama_asli . " ($i)." . $ext;
      $target = "../uploads/" . $nama_file;
      $i++;
    }
    if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target)){
      $gambar = $nama_file; // Simpan cuma namanya aja, kayak "kopi.jpeg"
    }
  }

  // Cek kolom yang ada di tabel produk
  $cols = [];
  $cq = mysqli_query($conn, "SHOW COLUMNS FROM produk");
  while($c = mysqli_fetch_assoc($cq)){ $cols[] = $c['Field']; }

  $ok = false;
  if(in_array('id_kategori', $cols) && in_array('deskripsi', $cols)){
    $ok = mysqli_query($conn, "INSERT INTO produk (id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES ('$id_kategori', '$nama', '$deskripsi', '$harga', '$stok', '$gambar')");
  } elseif(in_array('id_kategori', $cols)){
    $ok = mysqli_query($conn, "INSERT INTO produk (id_kategori, nama_produk, harga, stok, gambar) VALUES ('$id_kategori', '$nama', '$harga', '$stok', '$gambar')");
  } else {
    $ok = mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, stok, gambar) VALUES ('$nama', '$harga', '$stok', '$gambar')");
  }

  if($ok){
    header("Location: produk.php?baru=1");
    exit;
  } else {
    $pesan = "Gagal simpan: " . mysqli_error($conn);
  }
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Produk - PasarTradisional.id</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F6F7F3}
.sidebar{background:#0B3D1E;min-height:100vh;color:white;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:#CBD5CE;text-decoration:none;display:block;padding:12px 14px;border-radius:12px;font-weight:600;margin-bottom:6px}
.sidebar a.active,.sidebar a:hover{background:#1A5C36;color:white}
.logo{font-weight:800;font-size:20px;color:white}
.logo span{color:#FF8A00}
.card-box{background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none}
.form-control,.form-select{border-radius:12px;padding:12px 14px}
.form-control:focus,.form-select:focus{border-color:#0B8A3D;box-shadow:0 0 0 3px rgba(11,138,61,.15)}
.btn-simpan{background:#FF8A00;color:white;font-weight:800;border:none;border-radius:12px;padding:14px 28px;width:100%}
.btn-simpan:hover{background:#E67E00;color:white}
.btn-batal{background:#E9ECEF;color:#333;font-weight:700;border-radius:12px;padding:14px 28px;text-decoration:none;display:block;text-align:center}
.preview-box{border:2px dashed #CBD5CE;border-radius:16px;padding:20px;text-align:center;background:#FAFAF7;cursor:pointer}
.preview-box:hover{border-color:#0B8A3D}
.preview-box img{width:100%;max-height:220px;object-fit:cover;border-radius:12px;display:none}
.badge-contoh{background:#E6F4EB;color:#0E7A3D;font-weight:700;border-radius:999px;padding:6px 12px;font-size:12px;cursor:pointer;border:none}
</style>
<script>
function previewGambar(input){
  const file = input.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = function(e){
    const img = document.getElementById('previewImg');
    img.src = e.target.result;
    img.style.display = 'block';
    document.getElementById('previewText').style.display = 'none';
    document.getElementById('namaFile').innerText = "📁 " + file.name;
  }
  reader.readAsDataURL(file);
}
function isiContoh(nama, harga, stok){
  document.querySelector('[name=nama_produk]').value = nama;
  document.querySelector('[name=harga]').value = harga;
  document.querySelector('[name=stok]').value = stok;
}
</script>
</head><body>

<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar">
  <div class="logo">Pasar<span>Tradisional</span>.id</div>
  <small>Admin Panel - Bengkulu</small>
  <hr style="border-color:#2A5C40">
  <a href="produk.php" class="active">Kelola Produk</a>
  <a href="#">Pesanan Masuk</a>
  <a href="../index.php">Lihat Toko</a>
  <a href="../keranjang.php">Keranjang</a>
</div>

<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 style="font-weight:800">➕ Tambah Produk Baru</h1>
      <p class="text-muted">Isi data produk, upload foto, langsung muncul di toko & admin.</p>
    </div>
    <a href="produk.php" style="font-weight:700;text-decoration:none;color:#0B8A3D">← Kembali ke Daftar Produk</a>
  </div>

  <?php if($pesan != ""): ?><div class="alert alert-danger mt-3" style="border-radius:12px"><?php echo $pesan; ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
  <div class="row g-3 mt-1">
    <!-- KIRI: FORM -->
    <div class="col-md-7">
      <div class="card-box">
        <h5 style="font-weight:800">📝 Detail Produk</h5>

        <div class="mt-3">
          <label class="fw-bold">Kategori *</label>
          <select name="id_kategori" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach($kategoris as $k): ?>
            <option value="<?php echo $k['id_kategori']; ?>"><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Contoh: Kopi = Minuman, Batik = Kerajinan, Keripik = Makanan</small>
        </div>

        <div class="mt-3">
          <label class="fw-bold">Nama Produk *</label>
          <input name="nama_produk" class="form-control" placeholder="Contoh: Kopi Bengkulu" required>
          <div class="d-flex gap-2 mt-2 flex-wrap">
            <button type="button" class="badge-contoh" onclick="isiContoh('Kopi Bengkulu',75000,20)">☕ Kopi Bengkulu</button>
            <button type="button" class="badge-contoh" onclick="isiContoh('Batik Besurek',150000,10)">👘 Batik Besurek</button>
            <button type="button" class="badge-contoh" onclick="isiContoh('Keripik Pisang',25000,50)">🍌 Keripik Pisang</button>
          </div>
        </div>

        <div class="mt-3">
          <label class="fw-bold">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3" placeholder="Ceritakan produknya... Contoh: Kopi Robusta asli Bengkulu, aroma kuat, dipetik petani lokal."></textarea>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-6">
            <label class="fw-bold">Harga (Rp) *</label>
            <div class="input-group">
              <span class="input-group-text" style="border-radius:12px 0 0 12px;background:#E6F4EB;font-weight:800;color:#0E7A3D">Rp</span>
              <input name="harga" type="number" class="form-control" placeholder="75000" required>
            </div>
          </div>
          <div class="col-6">
            <label class="fw-bold">Stok *</label>
            <input name="stok" type="number" class="form-control" placeholder="20" required>
          </div>
        </div>

      </div>
    </div>

    <!-- KANAN: GAMBAR + SIMPAN -->
    <div class="col-md-5">
      <div class="card-box">
        <h5 style="font-weight:800">🖼️ Foto Produk</h5>
        <label class="preview-box w-100 mt-2" for="inputGambar">
          <img id="previewImg">
          <div id="previewText">
            <div style="font-size:50px">📷</div>
            <b>Klik untuk Pilih Foto</b><br>
            <small class="text-muted">JPG / PNG, tersimpan di folder uploads/<br>Contoh: kopi.jpeg, batik baserek.jpg</small>
          </div>
          <div id="namaFile" class="mt-2 fw-bold" style="color:#0E7A3D"></div>
        </label>
        <input type="file" id="inputGambar" name="gambar" accept="image/*" style="display:none" onchange="previewGambar(this)">
        <small class="text-muted d-block mt-2">💡 Tips: Pakai foto terang, produknya di tengah, biar di toko keliatan menarik.</small>
      </div>

      <div class="card-box mt-3">
        <button type="submit" name="simpan" class="btn-simpan">💾 Simpan Produk</button>
        <a href="produk.php" class="btn-batal mt-2">Batal</a>
        <p class="text-center text-muted mt-2 mb-0" style="font-size:12px">Produk langsung muncul di halaman utama & admin.</p>
      </div>
    </div>
  </div>
  </form>
</div>
</div></div>
</body></html>