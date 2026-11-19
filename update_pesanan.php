
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

// Hanya menerima PUT
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    header("Allow: PUT");
    response(405, "error", "Gunakan method PUT");
}

// Koneksi database
require_once __DIR__ . "/../config/koneksi.php";

// Membaca data JSON
$input = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (
    !is_array($input) ||
    !isset($input["id_pesanan"]) ||
    !isset($input["status"])
) {
    response(400, "error", "ID pesanan dan status wajib diisi");
}

$id_pesanan = (int) $input["id_pesanan"];
$status = trim($input["status"]);

// Status yang diperbolehkan
$status_valid = [
    "menunggu",
    "diproses",
    "dikirim",
    "selesai",
    "batal"
];

if ($id_pesanan <= 0) {
    response(400, "error", "ID pesanan tidak valid");
}

if (!in_array($status, $status_valid, true)) {
    response(400, "error", "Status pesanan tidak valid");
}

// Periksa apakah pesanan tersedia
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_pesanan FROM pesanan WHERE id_pesanan = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    response(404, "error", "Pesanan tidak ditemukan");
}

// Update status pesanan
$stmt = mysqli_prepare(
    $conn,
    "UPDATE pesanan SET status = ? WHERE id_pesanan = ?"
);

mysqli_stmt_bind_param($stmt, "si", $status, $id_pesanan);

if (!mysqli_stmt_execute($stmt)) {
    response(500, "error", "Gagal memperbarui status pesanan");
}

// Response berhasil
response(200, "success", "Status pesanan berhasil diperbarui", [
    "id_pesanan" => $id_pesanan,
    "status" => $status
]);
?>
