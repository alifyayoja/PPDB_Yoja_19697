<?php
require_once '../config/config.php';
require_once '../includes/header_admin.php'; // Header untuk session check dan navigasi

// Logika untuk memproses ekspor ketika form disubmit
if (isset($_POST['export_csv'])) {
    $status_filter = $_POST['status_filter'];

    // Query untuk mengambil data
    $sql = "SELECT u.nama_lengkap, u.nik, u.email, p.*, o.*, s.status, s.catatan 
            FROM users u 
            LEFT JOIN pendaftar p ON u.id = p.user_id 
            LEFT JOIN orang_tua o ON u.id = o.user_id
            LEFT JOIN seleksi s ON u.id = s.user_id";

    if (!empty($status_filter)) {
        $sql .= " WHERE s.status = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($status_filter)) {
        $stmt->bind_param("s", $status_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $filename = "data_pendaftar_" . strtolower(str_replace(' ', '_', $status_filter)) . "_" . date('Ymd') . ".csv";

        // Set header untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Tulis header kolom
        fputcsv($output, [
            'Nama Lengkap', 'NIK', 'Email', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Alamat', 
            'Asal Sekolah', 'NISN', 'Nilai Rata-rata', 'Nama Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 
            'Nama Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu', 'Status Seleksi', 'Catatan Admin'
        ]);

        // Tulis data baris
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['nama_lengkap'], $row['nik'], $row['email'], $row['tempat_lahir'], $row['tanggal_lahir'], $row['jenis_kelamin'], $row['alamat'],
                $row['asal_sekolah'], $row['nisn'], $row['nilai_rata_rata'], $row['nama_ayah'], $row['pekerjaan_ayah'], $row['penghasilan_ayah'],
                $row['nama_ibu'], $row['pekerjaan_ibu'], $row['penghasilan_ibu'], $row['status'], $row['catatan']
            ]);
        }

        fclose($output);
        exit();
    } else {
        $error = "Tidak ada data untuk diekspor dengan filter yang dipilih.";
    }
}
?>

<h3>Ekspor Data Pendaftar</h3>
<p>Pilih status pendaftar yang ingin Anda ekspor datanya ke dalam format CSV.</p>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Opsi Ekspor</div>
    <div class="card-body">
        <form action="export.php" method="POST">
            <div class="mb-3">
                <label for="status_filter" class="form-label">Filter Berdasarkan Status</label>
                <select name="status_filter" id="status_filter" class="form-select">
                    <option value="">-- Semua Pendaftar --</option>
                    <option value="Belum diverifikasi">Belum diverifikasi</option>
                    <option value="Lengkap dan Valid">Berkas Lengkap & Valid</option>
                    <option value="Tidak Lengkap / Ditolak">Berkas Tidak Lengkap / Ditolak</option>
                    <option value="Diterima">Diterima</option>
                    <option value="Tidak diterima">Tidak diterima</option>
                </select>
            </div>
            <button type="submit" name="export_csv" class="btn btn-primary">Ekspor ke CSV</button>
        </form>
    </div>
    <div class="card-footer">
        <small class="text-muted">Untuk ekspor ke format PDF atau Excel, diperlukan instalasi library tambahan pada sistem.</small>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
