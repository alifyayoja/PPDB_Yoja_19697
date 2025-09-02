<?php 
require_once '../includes/header_admin.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: pendaftar.php");
    exit;
}

$user_id = $_GET['id'];

// Logika untuk update status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $catatan = $_POST['catatan'];

    $stmt = $conn->prepare("UPDATE seleksi SET status = ?, catatan = ?, admin_id = ? WHERE user_id = ?");
    $stmt->bind_param("ssii", $status, $catatan, $admin_id, $user_id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Status berhasil diperbarui.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui status.</div>";
    }
    $stmt->close();
}

// Ambil semua data pendaftar
$query = "SELECT u.*, p.*, o.*, s.status, s.catatan 
          FROM users u
          LEFT JOIN pendaftar p ON u.id = p.user_id
          LEFT JOIN orang_tua o ON u.id = o.user_id
          LEFT JOIN seleksi s ON u.id = s.user_id
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Pendaftar tidak ditemukan.";
    exit;
}
$data = $result->fetch_assoc();

// Ambil dokumen
$dokumen_result = $conn->query("SELECT tipe_dokumen, path FROM dokumen WHERE user_id = $user_id");
$dokumen = [];
while($row = $dokumen_result->fetch_assoc()){
    $dokumen[$row['tipe_dokumen']] = $row['path'];
}

?>

<a href="pendaftar.php" class="btn btn-secondary mb-3"><< Kembali ke Daftar Pendaftar</a>

<div class="row">
    <!-- Kolom Data -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h4>Data Pendaftar</h4></div>
            <div class="card-body">
                <h5>Data Akun</h5>
                <table class="table table-bordered">
                    <tr><th>Nama Lengkap</th><td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td></tr>
                    <tr><th>NIK</th><td><?php echo htmlspecialchars($data['nik']); ?></td></tr>
                    <tr><th>Email</th><td><?php echo htmlspecialchars($data['email']); ?></td></tr>
                </table>

                <h5 class="mt-4">Data Pribadi</h5>
                <table class="table table-bordered">
                    <tr><th>Tempat, Tanggal Lahir</th><td><?php echo htmlspecialchars($data['tempat_lahir'] . ', ' . $data['tanggal_lahir']); ?></td></tr>
                    <tr><th>Jenis Kelamin</th><td><?php echo htmlspecialchars($data['jenis_kelamin']); ?></td></tr>
                    <tr><th>Alamat</th><td><?php echo htmlspecialchars($data['alamat']); ?></td></tr>
                </table>

                <h5 class="mt-4">Data Orang Tua</h5>
                <table class="table table-bordered">
                    <tr><th>Nama Ayah</th><td><?php echo htmlspecialchars($data['nama_ayah']); ?></td></tr>
                    <tr><th>Pekerjaan Ayah</th><td><?php echo htmlspecialchars($data['pekerjaan_ayah']); ?></td></tr>
                    <tr><th>Penghasilan Ayah</th><td>Rp <?php echo number_format($data['penghasilan_ayah']); ?></td></tr>
                    <tr><th>Nama Ibu</th><td><?php echo htmlspecialchars($data['nama_ibu']); ?></td></tr>
                    <tr><th>Pekerjaan Ibu</th><td><?php echo htmlspecialchars($data['pekerjaan_ibu']); ?></td></tr>
                    <tr><th>Penghasilan Ibu</th><td>Rp <?php echo number_format($data['penghasilan_ibu']); ?></td></tr>
                </table>

                <h5 class="mt-4">Data Akademik</h5>
                <table class="table table-bordered">
                    <tr><th>Asal Sekolah</th><td><?php echo htmlspecialchars($data['asal_sekolah']); ?></td></tr>
                    <tr><th>NISN</th><td><?php echo htmlspecialchars($data['nisn']); ?></td></tr>
                    <tr><th>Nilai Rata-rata</th><td><?php echo htmlspecialchars($data['nilai_rata_rata']); ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Verifikasi -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h4>Verifikasi & Aksi</h4></div>
            <div class="card-body">
                <form action="detail_pendaftar.php?id=<?php echo $user_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="status" class="form-label">Ubah Status Seleksi</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Belum diverifikasi" <?php echo ($data['status'] == 'Belum diverifikasi') ? 'selected' : ''; ?>>Belum diverifikasi</option>
                            <option value="Lengkap dan Valid" <?php echo ($data['status'] == 'Lengkap dan Valid') ? 'selected' : ''; ?>>Berkas Lengkap & Valid</option>
                            <option value="Tidak Lengkap / Ditolak" <?php echo ($data['status'] == 'Tidak Lengkap / Ditolak') ? 'selected' : ''; ?>>Berkas Tidak Lengkap / Ditolak</option>
                            <option value="Diterima" <?php echo ($data['status'] == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Tidak diterima" <?php echo ($data['status'] == 'Tidak diterima') ? 'selected' : ''; ?>>Tidak diterima</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan (Opsional)</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="4"><?php echo htmlspecialchars($data['catatan']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4>Dokumen Terupload</h4></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach($dokumen as $tipe => $path): ?>
                        <li class="list-group-item">
                            <?php echo ucfirst(str_replace('_',' ',$tipe)); ?>
                            <a href="<?php echo BASE_URL . str_replace('../', '/', $path); ?>" target="_blank" class="btn btn-sm btn-info float-end">Lihat</a>
                        </li>
                    <?php endforeach; ?>
                    <?php if(empty($dokumen)): ?>
                        <li class="list-group-item">Belum ada dokumen yang diupload.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>