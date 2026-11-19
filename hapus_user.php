<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode DELETE"
    ]);
    exit;
}

// Ambil ID pengguna dari URL
$id_user = isset($_GET["id_user"])
    ? (int) $_GET["id_user"]
    : 0;

// Validasi ID
if ($id_user <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID pengguna wajib diisi"
    ]);
    exit;
}

// Periksa pengguna
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user, role FROM users WHERE id_user = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Pengguna tidak ditemukan"
    ]);
    exit;
}

// Lindungi akun admin
if ($user["role"] === "admin") {
    http_response_code(403);
    echo json_encode([
        "status" => false,
        "pesan" => "Akun admin tidak boleh dihapus melalui API ini"
    ]);
    exit;
}

// Hapus pengguna
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM users WHERE id_user = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Pengguna berhasil dihapus",
        "data" => [
            "id_user" => $id_user
        ]
    ]);
} else {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Pengguna tidak dapat dihapus karena masih memiliki data terkait, seperti produk atau pesanan"
    ]);
}
?>