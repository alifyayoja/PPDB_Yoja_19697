<?php require_once '../includes/header_siswa.php'; ?>

<?php
// Ambil status seleksi siswa
$status_seleksi = 'Belum Mendaftar';
$catatan = '';
$stmt = $conn->prepare("SELECT status, catatan FROM seleksi WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($status_seleksi, $catatan);
    $stmt->fetch();
}
$stmt->close();
?>

<div class="card">
    <div class="card-header">
        <h3>Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</h3>
    </div>
    <div class="card-body">
        <h5 class="card-title">Status Pendaftaran Anda</h5>
        <p class="card-text">Berikut adalah ringkasan dari status pendaftaran Anda saat ini.</p>
        
        <div class="alert alert-info">
            <strong>Status:</strong> <?php echo htmlspecialchars($status_seleksi); ?><br>
            <?php if (!empty($catatan)): ?>
                <strong>Catatan dari Admin:</strong> <?php echo htmlspecialchars($catatan); ?>
            <?php endif; ?>
        </div>

        <?php if ($status_seleksi == 'Belum Mendaftar'): ?>
            <p>Anda belum mengisi formulir pendaftaran. Silakan lengkapi formulir untuk melanjutkan.</p>
            <a href="formulir.php" class="btn btn-primary">Isi Formulir Pendaftaran</a>
        <?php else: ?>
            <p>Gunakan menu di atas untuk melihat detail formulir, status seleksi, atau mencetak bukti pendaftaran.</p>
            <a href="cetak.php" class="btn btn-success">Cetak Bukti Pendaftaran</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
