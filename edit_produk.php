<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode PUT"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id_produk = $data["id_produk"] ?? 0;
$id_kategori = $data["id_kategori"] ?? 0;
$id_penjual = $data["id_penjual"] ?? 0;
$nama_produk = trim($data["nama_produk"] ?? "");
$deskripsi = $data["deskripsi"] ?? "";
$harga = $data["harga"] ?? 0;
$stok = $data["stok"] ?? 0;
$gambar = $data["gambar"] ?? "";

if (
    !$id_produk ||
    !$id_kategori ||
    !$id_penjual ||
    $nama_produk === "" ||
    !is_numeric($harga) ||
    !is_numeric($stok) ||
    $harga <= 0 ||
    $stok < 0
) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Data produk tidak lengkap atau tidak valid"
    ]);
    exit;
}

// Periksa apakah produk tersedia
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_produk FROM produk WHERE id_produk = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);
$produk = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($produk) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Produk tidak ditemukan"
    ]);
    exit;
}

// Periksa kategori
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_kategori FROM kategori WHERE id_kategori = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);
$kategori = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($kategori) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Kategori tidak ditemukan"
    ]);
    exit;
}

// Periksa penjual
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user FROM users WHERE id_user = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_penjual);
mysqli_stmt_execute($stmt);
$penjual = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($penjual) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Penjual tidak ditemukan"
    ]);
    exit;
}

// Update produk
$query = "UPDATE produk SET
    id_kategori = ?,
    id_penjual = ?,
    nama_produk = ?,
    deskripsi = ?,
    harga = ?,
    stok = ?,
    gambar = ?
    WHERE id_produk = ?";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iissdisi",
    $id_kategori,
    $id_penjual,
    $nama_produk,
    $deskripsi,
    $harga,
    $stok,
    $gambar,
    $id_produk
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Produk berhasil diperbarui"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal memperbarui produk",
        "error" => mysqli_error($conn)
    ]);
}
?>