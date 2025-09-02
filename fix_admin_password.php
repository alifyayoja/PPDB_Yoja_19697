<?php
require_once 'config/config.php';

echo "<p>Mencoba memperbaiki password admin...</p>";

// Password baru yang kita inginkan
$new_password = 'admin';

// Buat hash yang benar untuk password 'admin'
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Username admin yang ingin diperbaiki
$admin_username = 'admin';

// Update password di database
$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    echo "<h3 style='color:green;'>BERHASIL!</h3>";
    echo "<p>Password untuk username <strong>admin</strong> telah berhasil direset menjadi <strong>admin</strong>.</p>";
    echo "<p>Silakan coba login kembali di <a href='admin/'>halaman login admin</a>.</p>";
    echo "<p style='color:red; font-weight:bold;'>PENTING: Setelah Anda berhasil login, segera hapus file ini (fix_admin_password.php) dari server Anda demi keamanan.</p>";
} else {
    echo "<h3 style='color:red;'>GAGAL!</h3>";
    echo "<p>Gagal memperbarui password. Error: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>