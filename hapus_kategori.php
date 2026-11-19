<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode DELETE"
    ]);
    exit;
}

// Ambil ID kategori dari URL
$id_kategori = isset($_GET["id_kategori"])
    ? (int) $_GET["id_kategori"]
    : 0;

// Validasi ID
if ($id_kategori <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID kategori wajib diisi"
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
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Kategori tidak ditemukan"
    ]);
    exit;
}

// Periksa apakah kategori masih digunakan produk
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_produk FROM produk WHERE id_kategori = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);
$produk = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($produk) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Kategori tidak dapat dihapus karena masih digunakan oleh produk"
    ]);
    exit;
}

// Hapus kategori
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM kategori WHERE id_kategori = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_kategori);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Kategori berhasil dihapus",
        "data" => [
            "id_kategori" => $id_kategori
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menghapus kategori",
        "error" => mysqli_error($conn)
    ]);
}
?>