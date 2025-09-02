<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', ''); // Ganti dengan password database Anda
define('DB_NAME', 'ppdb2');

// Buat Koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// URL dasar
define('BASE_URL', 'http://localhost/ppdb2');
?>