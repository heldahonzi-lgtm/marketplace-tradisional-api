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

$nama = trim($data["nama"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";
$role = $data["role"] ?? "pembeli";
$no_hp = trim($data["no_hp"] ?? "");
$alamat = trim($data["alamat"] ?? "");

// Validasi data
if ($nama === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Nama, email, dan password wajib diisi"
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

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Password minimal 6 karakter"
    ]);
    exit;
}

// Batasi role yang dapat didaftarkan
if (!in_array($role, ["pembeli", "penjual"], true)) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Role hanya boleh pembeli atau penjual"
    ]);
    exit;
}

// Periksa email
$stmt = mysqli_prepare(
    $conn,
    "SELECT id_user FROM users WHERE email = ?"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => false,
        "pesan" => "Email sudah terdaftar"
    ]);
    exit;
}

// Enkripsi password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Simpan pengguna
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (nama, email, password, role, no_hp, alamat)
     VALUES (?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $nama,
    $email,
    $password_hash,
    $role,
    $no_hp,
    $alamat
);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(201);

    echo json_encode([
        "status" => true,
        "pesan" => "Registrasi berhasil",
        "data" => [
            "id_user" => mysqli_insert_id($conn),
            "nama" => $nama,
            "email" => $email,
            "role" => $role,
            "no_hp" => $no_hp,
            "alamat" => $alamat
        ]
    ]);
} else {
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "pesan" => "Registrasi gagal"
    ]);
}
?>