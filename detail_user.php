
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

// Hanya menerima GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    response(405, "error", "Gunakan method GET");
}

// Koneksi database
require_once __DIR__ . "/../config/koneksi.php";

// Mengambil ID pengguna dari URL
$id_user = isset($_GET["id_user"])
    ? (int) $_GET["id_user"]
    : 0;

// Validasi ID
if ($id_user <= 0) {
    response(400, "error", "ID pengguna tidak valid");
}

// Query detail pengguna
$query = "SELECT
            id_user,
            nama,
            email,
            role,
            no_hp,
            alamat
          FROM users
          WHERE id_user = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    response(500, "error", "Gagal menyiapkan query");
}

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Periksa pengguna
if (!$user) {
    response(404, "error", "Pengguna tidak ditemukan");
}

// Mengirim response
response(200, "success", "Detail pengguna berhasil diambil", $user);
?>
