
<?php
header("Content-Type: application/json; charset=UTF-8");

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
$id_user = isset($_GET["id_user"]) ? (int) $_GET["id_user"] : 0;

if ($id_user <= 0) {
    response(400, "error", "Masukkan ID pengguna");
}

// Mengambil data pesanan
$query = "SELECT
            p.id_pesanan,
            p.id_user,
            u.nama AS nama_pembeli,
            p.tanggal_pesanan,
            p.total_harga,
            p.status,
            p.alamat_kirim
          FROM pesanan p
          JOIN users u ON p.id_user = u.id_user
          WHERE p.id_user = ?
          ORDER BY p.id_pesanan DESC";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    response(500, "error", "Query gagal disiapkan");
}

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pesanan = [];

while ($row = mysqli_fetch_assoc($result)) {
    $pesanan[] = $row;
}

response(200, "success", "Riwayat pesanan berhasil diambil", $pesanan);
?>
