
<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "inventaris_prodi";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi database gagal. Periksa pengaturan database.");
}

$conn->set_charset("utf8mb4");
?>
