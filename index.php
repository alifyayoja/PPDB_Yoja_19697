<?php require_once 'config/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Selamat Datang di PPDB Online</h1>
        <p class="col-md-8 fs-4">Sistem Penerimaan Peserta Didik Baru secara online. Silakan pilih menu di bawah ini untuk melanjutkan.</p>
    </div>
</div>

<div class="row align-items-md-stretch">
    <div class="col-md-6">
        <div class="h-100 p-5 bg-warning rounded-3">
            <h2>Portal Siswa</h2>
            <p>Masuk atau daftar sebagai calon siswa baru untuk mengisi formulir dan melihat status pendaftaran Anda.</p>
            <a href="siswa/" class="btn btn-outline-dark" type="button">Masuk ke Portal Siswa</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="h-100 p-5 bg-light border rounded-3">
            <h2>Login Admin</h2>
            <p>Halaman khusus untuk administrator sistem untuk mengelola data pendaftar dan pengaturan PPDB.</p>
            <a href="admin/" class="btn btn-outline-secondary" type="button">Masuk sebagai Admin</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>