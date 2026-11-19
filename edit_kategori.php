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

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$id_kategori = $data["id_kategori"] ?? 0;
$nama_kategori = trim($data["nama_kategori"] ?? "");
$deskripsi = trim($data["deskripsi"] ?? "");

// Validasi
if (!$id_kategori || $nama_kategori === "") {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID dan nama kategori wajib diisi"
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

// Periksa nama kategori duplikat
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_kategori FROM kategori
     WHERE nama_kategori = ? AND id_kategori != ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $nama_kategori,
    $id_kategori
);

mysqli_stmt_execute($stmt);
$duplikat = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($duplikat) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Nama kategori sudah digunakan"
    ]);
    exit;
}

// Update kategori
$stmt = mysqli_prepare(
    $conn,
    "UPDATE kategori
     SET nama_kategori = ?, deskripsi = ?
     WHERE id_kategori = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $nama_kategori,
    $deskripsi,
    $id_kategori
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Kategori berhasil diperbarui",
        "data" => [
            "id_kategori" => $id_kategori,
            "nama_kategori" => $nama_kategori,
            "deskripsi" => $deskripsi
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal memperbarui kategori"
    ]);
}
?>