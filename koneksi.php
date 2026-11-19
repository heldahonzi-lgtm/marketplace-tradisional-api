
<?php

// Konfigurasi database
$host = getenv("DB_HOST") ?: "localhost";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASS") ?: "";
$db   = getenv("DB_NAME") ?: "marketplace_tradisional";

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db);

// Alias koneksi untuk file lama
$koneksi = $conn;

// Periksa koneksi
if (!$conn) {
    http_response_code(500);

    die(json_encode([
        "status" => false,
        "pesan" => "Koneksi database gagal"
    ]));
}

// Atur karakter database
mysqli_set_charset($conn, "utf8mb4");

?>
