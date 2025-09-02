<?php
require_once '../config/config.php';

// Hancurkan semua session
session_destroy();

// Redirect ke halaman login admin
header("Location: " . BASE_URL . "/admin/");
exit;
?>