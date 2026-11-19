
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

// Mengambil data pengguna
$query = "SELECT
            id_user,
            nama,
            email,
            role,
            no_hp,
            alamat
          FROM users
          ORDER BY id_user ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    response(500, "error", "Gagal mengambil data pengguna");
}

// Menampung data pengguna
$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Mengirim response JSON
response(200, "success", "Data pengguna berhasil diambil", $users);
?>
