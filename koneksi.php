<?php
$conn = mysqli_connect("localhost", "root", "", "marketplace_tradisional");
$koneksi = $conn; // biar yang pakai $koneksi juga jalan

if(!$conn){
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>