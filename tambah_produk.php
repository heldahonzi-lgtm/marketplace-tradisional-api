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

$data = json_decode(file_get_contents("php://input"), true);

$id_kategori = $data["id_kategori"] ?? 0;
$id_penjual = $data["id_penjual"] ?? 0;
$nama_produk = trim($data["nama_produk"] ?? "");
$deskripsi = $data["deskripsi"] ?? "";
$harga = $data["harga"] ?? 0;
$stok = $data["stok"] ?? 0;
$gambar = $data["gambar"] ?? "";

if (
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

// Simpan produk
$query = "INSERT INTO produk
    (id_kategori, id_penjual, nama_produk, deskripsi, harga, stok, gambar)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iissdis",
    $id_kategori,
    $id_penjual,
    $nama_produk,
    $deskripsi,
    $harga,
    $stok,
    $gambar
);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(201);

    echo json_encode([
        "status" => true,
        "pesan" => "Produk berhasil ditambahkan",
        "data" => [
            "id_produk" => mysqli_insert_id($conn),
            "nama_produk" => $nama_produk,
            "harga" => $harga,
            "stok" => $stok
        ]
    ]);
} else {
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "pesan" => "Gagal menambahkan produk",
        "error" => mysqli_error($conn)
    ]);
}
?>