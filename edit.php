<?php
include '../config/koneksi.php';
$id = intval($_GET['id']);
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='".$id."'"));
if(!$p){ echo "Produk tidak ditemukan"; exit; }
if(isset($_POST['update'])){
  $kat = intval($_POST['id_kategori']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
  $desk = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $harga = intval($_POST['harga']);
  $stok = intval($_POST['stok']);
  $gambar = $p['gambar'];
  if(isset($_FILES['gambar']) && $_FILES['gambar']['name']!=""){
    $gambar = time()."_".$_FILES['gambar']['name'];
    move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/img/".$gambar);
  }
  mysqli_query($conn, "UPDATE produk SET id_kategori='".$kat."', nama_produk='".$nama."', deskripsi='".$desk."', harga='".$harga."', stok='".$stok."', gambar='".$gambar."' WHERE id_produk='".$id."'");
  header("Location: produk.php"); exit;
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Edit Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>
<div class="container mt-4" style="max-width:600px"><h3>Edit Produk</h3>
<form method="POST" enctype="multipart/form-data">
<label>Kategori</label><select name="id_kategori" class="form-control mb-2">
<?php $k=mysqli_query($conn,"SELECT * FROM kategori"); while($r=mysqli_fetch_assoc($k)){ $sel=""; if($r['id_kategori']==$p['id_kategori']){ $sel="selected"; } echo "<option value='".$r['id_kategori']."' ".$sel.">".$r['nama_kategori']."</option>"; } ?>
</select>
<label>Nama Produk</label><input name="nama_produk" value="<?php echo htmlspecialchars($p['nama_produk']); ?>" class="form-control mb-2" required>
<label>Deskripsi</label><textarea name="deskripsi" class="form-control mb-2"><?php echo htmlspecialchars($p['deskripsi']); ?></textarea>
<label>Harga</label><input type="number" name="harga" value="<?php echo $p['harga']; ?>" class="form-control mb-2" required>
<label>Stok</label><input type="number" name="stok" value="<?php echo $p['stok']; ?>" class="form-control mb-2" required>
<label>Gambar Baru (kosongkan kalau tidak ganti)</label><input type="file" name="gambar" class="form-control mb-3">
<button name="update" class="btn btn-warning">Update</button>
<a href="produk.php" class="btn btn-secondary">Batal</a>
</form></div></body></html>