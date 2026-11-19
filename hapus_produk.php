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

// Ambil ID produk dari URL
$id_produk = isset($_GET["id_produk"])
    ? (int) $_GET["id_produk"]
    : 0;

if ($id_produk <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID produk wajib diisi"
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
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Produk tidak ditemukan"
    ]);
    exit;
}

// Hapus produk
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM produk WHERE id_produk = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_produk);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Produk berhasil dihapus",
        "data" => [
            "id_produk" => $id_produk
        ]
    ]);
} else {
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menghapus produk",
        "error" => mysqli_error($conn)
    ]);
}
?>