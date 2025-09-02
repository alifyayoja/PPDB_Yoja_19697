<?php 
require_once '../includes/header_siswa.php'; 

// Ambil status seleksi siswa dari database
$status = 'Belum Mendaftar';
$catatan = '';
$status_class = 'alert-secondary'; // Warna default untuk status

$stmt = $conn->prepare("SELECT status, catatan FROM seleksi WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($status, $catatan);
    $stmt->fetch();
}
$stmt->close();

// Tentukan warna alert berdasarkan status
switch ($status) {
    case 'Diterima':
        $status_class = 'alert-success';
        $pesan_status = 'Selamat, Anda dinyatakan DITERIMA!';
        break;
    case 'Tidak diterima':
        $status_class = 'alert-danger';
        $pesan_status = 'Mohon maaf, Anda dinyatakan TIDAK DITERIMA.';
        break;
    case 'Lengkap dan Valid':
        $status_class = 'alert-info';
        $pesan_status = 'Berkas Anda sudah diverifikasi dan valid. Harap tunggu pengumuman kelulusan.';
        break;
    case 'Tidak Lengkap / Ditolak':
        $status_class = 'alert-warning';
        $pesan_status = 'Berkas Anda tidak lengkap atau ditolak. Silakan periksa catatan dari admin.';
        break;
    case 'Belum diverifikasi':
        $status_class = 'alert-primary';
        $pesan_status = 'Data Anda telah kami terima dan sedang dalam proses verifikasi oleh admin.';
        break;
    default:
        $pesan_status = 'Anda belum menyelesaikan proses pendaftaran.';
        break;
}

?>

<h3>Status Seleksi Pendaftaran</h3>
<p>Di bawah ini adalah status akhir dari pendaftaran Anda.</p>

<div class="card">
    <div class="card-header">Pengumuman Kelulusan</div>
    <div class="card-body text-center">
        <div class="alert <?php echo $status_class; ?>" role="alert">
            <h4 class="alert-heading"><?php echo $pesan_status; ?></h4>
            <p>Status Anda saat ini adalah: <strong><?php echo htmlspecialchars($status); ?></strong></p>
            <?php if (!empty($catatan)): ?>
                <hr>
                <p class="mb-0"><strong>Catatan dari Admin:</strong> <?php echo htmlspecialchars($catatan); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($status == 'Diterima'): ?>
            <p>Silakan unduh kembali bukti pendaftaran Anda sebagai bukti kelulusan dan ikuti instruksi selanjutnya dari sekolah.</p>
            <a href="cetak.php" class="btn btn-success btn-lg">Cetak Bukti Kelulusan</a>
        <?php else: ?>
            <p>Terima kasih telah berpartisipasi dalam proses seleksi PPDB Online.</p>
            <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
