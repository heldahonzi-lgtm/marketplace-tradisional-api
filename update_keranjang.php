<?php
header("Content-Type: application/json");

require_once "../config/koneksi.php";

// Pastikan metode PUT
if ($_SERVER["REQUEST_METHOD"] != "PUT") {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "pesan" => "Metode harus PUT"
    ]);
    exit;
}

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);

$id_keranjang = $data["id_keranjang"] ?? 0;
$jumlah = $data["jumlah"] ?? 0;

// Validasi
if ($id_keranjang <= 0 || $jumlah <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "ID keranjang dan jumlah wajib diisi"
    ]);
    exit;
}

// Cek data keranjang dan stok produk
$stmt = mysqli_prepare($conn, "
    SELECT p.stok
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id_produk
    WHERE k.id_keranjang = ?
");

mysqli_stmt_bind_param($stmt, "i", $id_keranjang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    http_response_code(404);
    echo json_encode([
        "status" => false,
        "pesan" => "Data keranjang tidak ditemukan"
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);

if ($jumlah > $row["stok"]) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "pesan" => "Jumlah melebihi stok produk"
    ]);
    exit;
}

// Update jumlah keranjang
$stmt = mysqli_prepare($conn, "
    UPDATE keranjang
    SET jumlah = ?
    WHERE id_keranjang = ?
");

mysqli_stmt_bind_param($stmt, "ii", $jumlah, $id_keranjang);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => true,
        "pesan" => "Keranjang berhasil diperbarui"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "pesan" => mysqli_error($conn)
    ]);
}
?>