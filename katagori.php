
<?php
header("Content-Type: application/json; charset=UTF-8");

// Fungsi response JSON
function response($code, $status, $message, $data = null) {
    http_response_code($code);

    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ], JSON_PRETTY_PRINT);

    exit;
}

// API hanya menerima GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    response(405, "error", "Method tidak diizinkan");
}

// Koneksi database
require_once __DIR__ . "/../config/koneksi.php";

// Mengambil data kategori
$query = "SELECT id_kategori, nama_kategori, deskripsi
          FROM kategori
          ORDER BY id_kategori ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    response(500, "error", "Gagal mengambil data kategori");
}

// Menampung data kategori
$kategori = [];

while ($row = mysqli_fetch_assoc($result)) {
    $kategori[] = $row;
}

// Mengirim response
response(200, "success", "Data kategori berhasil diambil", $kategori);
?>
