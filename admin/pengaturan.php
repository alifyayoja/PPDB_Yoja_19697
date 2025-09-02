<?php 
require_once '../includes/header_admin.php'; 

$error = '';
$success = '';

// Cek jika form disubmit untuk update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $tgl_buka = trim($_POST['tgl_buka']);
    $tgl_tutup = trim($_POST['tgl_tutup']);
    $kuota = filter_var($_POST['kuota'], FILTER_SANITIZE_NUMBER_INT);
    $pengumuman = trim($_POST['pengumuman']);

    if (empty($tahun_ajaran) || empty($tgl_buka) || empty($tgl_tutup) || empty($kuota)) {
        $error = "Tahun Ajaran, Tanggal, dan Kuota wajib diisi.";
    } else {
        // Cek jika sudah ada pengaturan (id=1), jika tidak, INSERT. Jika ada, UPDATE.
        $stmt_check = $conn->prepare("SELECT id FROM pengaturan WHERE id = 1");
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE pengaturan SET tahun_ajaran = ?, tgl_buka = ?, tgl_tutup = ?, kuota = ?, pengumuman = ? WHERE id = 1");
            $stmt->bind_param("sssis", $tahun_ajaran, $tgl_buka, $tgl_tutup, $kuota, $pengumuman);
        } else {
            $stmt = $conn->prepare("INSERT INTO pengaturan (id, tahun_ajaran, tgl_buka, tgl_tutup, kuota, pengumuman) VALUES (1, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $tahun_ajaran, $tgl_buka, $tgl_tutup, $kuota, $pengumuman);
        }

        if ($stmt->execute()) {
            $success = "Pengaturan berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan pengaturan.";
        }
        $stmt->close();
        $stmt_check->close();
    }
}

// Ambil data pengaturan saat ini
$pengaturan = $conn->query("SELECT * FROM pengaturan WHERE id = 1")->fetch_assoc();
if (!$pengaturan) {
    $pengaturan = ['tahun_ajaran' => '', 'tgl_buka' => '', 'tgl_tutup' => '', 'kuota' => '', 'pengumuman' => ''];
}

?>

<h3>Pengaturan PPDB</h3>
<p>Gunakan halaman ini untuk mengatur periode pendaftaran dan informasi penting lainnya.</p>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Form Pengaturan</div>
    <div class="card-body">
        <form action="pengaturan.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran Aktif</label>
                    <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" placeholder="Contoh: 2024/2025" value="<?php echo htmlspecialchars($pengaturan['tahun_ajaran']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kuota" class="form-label">Kuota Siswa</label>
                    <input type="number" class="form-control" id="kuota" name="kuota" value="<?php echo htmlspecialchars($pengaturan['kuota']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tgl_buka" class="form-label">Tanggal Buka Pendaftaran</label>
                    <input type="date" class="form-control" id="tgl_buka" name="tgl_buka" value="<?php echo htmlspecialchars($pengaturan['tgl_buka']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tgl_tutup" class="form-label">Tanggal Tutup Pendaftaran</label>
                    <input type="date" class="form-control" id="tgl_tutup" name="tgl_tutup" value="<?php echo htmlspecialchars($pengaturan['tgl_tutup']); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="pengumuman" class="form-label">Informasi/Pengumuman (Opsional)</label>
                <textarea class="form-control" id="pengumuman" name="pengumuman" rows="5" placeholder="Tuliskan pengumuman yang akan tampil di halaman depan pendaftar..."><?php echo htmlspecialchars($pengaturan['pengumuman']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
