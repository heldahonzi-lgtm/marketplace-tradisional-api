<?php
include '../config/koneksi.php';
$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM produk WHERE id_produk='".$id."'");
header("Location: produk.php"); exit;
?>