
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

// Mengambil ID pesanan dari URL
$id_pesanan = isset($_GET["id_pesanan"])
    ? (int) $_GET["id_pesanan"]
    : 0;

if ($id_pesanan <= 0) {
    response(400, "error", "Masukkan ID pesanan yang valid");
}

// Mengambil informasi pesanan
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
          WHERE p.id_pesanan = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    response(500, "error", "Gagal menyiapkan query pesanan");
}

mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result);

if (!$pesanan) {
    response(404, "error", "Pesanan tidak ditemukan");
}

// Mengambil detail produk pesanan
$query_detail = "SELECT
                    d.id_detail,
                    d.id_produk,
                    pr.nama_produk,
                    d.jumlah,
                    d.harga_saat_beli,
                    d.subtotal
                 FROM detail_pesanan d
                 JOIN produk pr ON d.id_produk = pr.id_produk
                 WHERE d.id_pesanan = ?";

$stmt_detail = mysqli_prepare($conn, $query_detail);

if (!$stmt_detail) {
    response(500, "error", "Gagal menyiapkan query detail");
}

mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
mysqli_stmt_execute($stmt_detail);

$result_detail = mysqli_stmt_get_result($stmt_detail);

$detail = [];

while ($row = mysqli_fetch_assoc($result_detail)) {
    $detail[] = $row;
}

// Menggabungkan data pesanan dan detail
$pesanan["detail_produk"] = $detail;

response(200, "success", "Detail pesanan berhasil diambil", $pesanan);
?>
