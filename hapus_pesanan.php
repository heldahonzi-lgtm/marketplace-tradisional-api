
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

// Hanya menerima DELETE
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    header("Allow: DELETE");
    response(405, "error", "Gunakan method DELETE");
}

// Koneksi database
require_once __DIR__ . "/../config/koneksi.php";

// Membaca ID pesanan dari JSON
$input = json_decode(file_get_contents("php://input"), true);

$id_pesanan = (int) ($input["id_pesanan"] ?? 0);

if ($id_pesanan <= 0) {
    response(400, "error", "ID pesanan wajib diisi");
}

// Periksa pesanan
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

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Hapus detail pesanan terlebih dahulu
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM detail_pesanan WHERE id_pesanan = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus detail pesanan");
    }

    // Hapus pesanan utama
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM pesanan WHERE id_pesanan = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus pesanan");
    }

    mysqli_commit($conn);

    response(200, "success", "Pesanan berhasil dihapus", [
        "id_pesanan" => $id_pesanan
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    response(500, "error", $e->getMessage());
}
?>
