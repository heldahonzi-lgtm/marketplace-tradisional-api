<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Gunakan metode PUT"
    ]);
    exit;
}

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$id_user = (int) ($data["id_user"] ?? 0);
$nama = trim($data["nama"] ?? "");
$email = trim($data["email"] ?? "");
$no_hp = trim($data["no_hp"] ?? "");
$alamat = trim($data["alamat"] ?? "");

// Validasi
if (
    $id_user <= 0 ||
    $nama === "" ||
    $email === ""
) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID pengguna, nama, dan email wajib diisi"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Format email tidak valid"
    ]);
    exit;
}

// Periksa pengguna
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user FROM users WHERE id_user = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Pengguna tidak ditemukan"
    ]);
    exit;
}

// Periksa email duplikat
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user FROM users
     WHERE email = ? AND id_user != ?"
);

mysqli_stmt_bind_param($stmt, "si", $email, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Email sudah digunakan pengguna lain"
    ]);
    exit;
}

// Update profil
$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET nama = ?, email = ?, no_hp = ?, alamat = ?
     WHERE id_user = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssi",
    $nama,
    $email,
    $no_hp,
    $alamat,
    $id_user
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Profil berhasil diperbarui",
        "data" => [
            "id_user" => $id_user,
            "nama" => $nama,
            "email" => $email,
            "no_hp" => $no_hp,
            "alamat" => $alamat
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => "Gagal memperbarui profil"
    ]);
}
?>