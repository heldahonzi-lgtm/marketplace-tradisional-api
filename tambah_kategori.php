<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode POST"
    ]);
    exit;
}

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$nama_kategori = trim($data["nama_kategori"] ?? "");
$deskripsi = trim($data["deskripsi"] ?? "");

// Validasi data
if ($nama_kategori === "") {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Nama kategori wajib diisi"
    ]);
    exit;
}

// Periksa kategori yang sama
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_kategori FROM kategori WHERE nama_kategori = ?"
);

mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Nama kategori sudah tersedia"
    ]);
    exit;
}

// Simpan kategori
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $nama_kategori,
    $deskripsi
);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(201);

    echo json_encode([
        "status" => true,
        "pesan" => "Kategori berhasil ditambahkan",
        "data" => [
            "id_kategori" => mysqli_insert_id($conn),
            "nama_kategori" => $nama_kategori,
            "deskripsi" => $deskripsi
        ]
    ]);
} else {
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menambahkan kategori"
    ]);
}
?>