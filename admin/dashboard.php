<?php require_once '../includes/header_admin.php'; ?>

<?php
// Ambil data statistik
$total_pendaftar = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];

$status_counts = [];
$result_status = $conn->query("SELECT status, COUNT(id) as count FROM seleksi GROUP BY status");
while($row = $result_status->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

$diterima = $status_counts['Diterima'] ?? 0;
$tidak_diterima = $status_counts['Tidak diterima'] ?? 0;
$belum_diverifikasi = $status_counts['Belum diverifikasi'] ?? 0;

?>

<h3>Dashboard Admin</h3>
<p>Selamat datang di halaman administrasi PPDB Online.</p>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Total Pendaftar</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $total_pendaftar; ?></h5>
                <p class="card-text">Siswa</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Diterima</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $diterima; ?></h5>
                <p class="card-text">Siswa</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Tidak Diterima</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $tidak_diterima; ?></h5>
                <p class="card-text">Siswa</p>
            </div>
        </div>
    </div>
     <div class="col-md-4">
        <div class="card text-dark bg-warning mb-3">
            <div class="card-header">Belum Diverifikasi</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $belum_diverifikasi; ?></h5>
                <p class="card-text">Siswa</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        Grafik Pendaftar (Placeholder)
    </div>
    <div class="card-body">
        <p class="text-center">Fitur grafik akan diimplementasikan di sini.</p>
        <!-- Di sini bisa ditambahkan library seperti Chart.js -->
    </div>
</div>


<?php require_once '../includes/footer.php'; ?>
