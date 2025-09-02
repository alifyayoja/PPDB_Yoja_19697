<?php
require_once __DIR__ . '/../config/config.php';

// Cek jika user tidak login atau bukan admin, redirect ke halaman login
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/admin/");
    exit;
}

// Ambil data admin
$admin_id = $_SESSION['admin_id'];
$result = $conn->query("SELECT nama_lengkap FROM admin WHERE id = $admin_id");
$admin_data = $result->fetch_assoc();
$nama_admin = $admin_data['nama_lengkap'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PPDB Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin PPDB</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAdmin">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pendaftar.php">Manajemen Pendaftar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pengaturan.php">Pengaturan PPDB</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="manajemen_admin.php">Manajemen Admin</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($nama_admin); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
