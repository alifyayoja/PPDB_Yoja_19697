<?php
require_once '../config/config.php';

// Hancurkan semua session
session_destroy();

// Redirect ke halaman login
header("Location: " . BASE_URL . "/siswa/login.php");
exit;
?>