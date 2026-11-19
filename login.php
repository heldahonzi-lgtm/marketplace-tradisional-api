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

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// Validasi input
if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Email dan password wajib diisi"
    ]);
    exit;
}

// Cari pengguna berdasarkan email
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user, nama, email, password, role, no_hp, alamat
     FROM users
     WHERE email = ?"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Periksa akun dan password
if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode([
        "status" => false,
        "pesan" => "Email atau password salah"
    ]);
    exit;
}

// Jangan tampilkan password
unset($user["password"]);

// Respons login berhasil
echo json_encode([
    "status" => true,
    "pesan" => "Login berhasil",
    "data" => $user
]);
?>