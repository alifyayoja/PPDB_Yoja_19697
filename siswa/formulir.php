<?php
require_once '../includes/header_siswa.php';

$error = '';
$success = '';

// Direktori untuk upload
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Cek jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Ambil dan sanitasi data --- //
    // Data Pribadi
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $alamat = trim($_POST['alamat']);

    // Data Orang Tua
    $nama_ayah = trim($_POST['nama_ayah']);
    $pekerjaan_ayah = trim($_POST['pekerjaan_ayah']);
    $penghasilan_ayah = filter_var($_POST['penghasilan_ayah'], FILTER_SANITIZE_NUMBER_INT);
    $nama_ibu = trim($_POST['nama_ibu']);
    $pekerjaan_ibu = trim($_POST['pekerjaan_ibu']);
    $penghasilan_ibu = filter_var($_POST['penghasilan_ibu'], FILTER_SANITIZE_NUMBER_INT);

    // Data Akademik
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $nisn = trim($_POST['nisn']);
    $nilai_rata_rata = filter_var($_POST['nilai_rata_rata'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // --- Validasi --- //
    if (empty($tempat_lahir) || empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($asal_sekolah) || empty($nisn)) {
        $error = "Semua field data pribadi dan akademik wajib diisi.";
    }

    // --- Proses Database --- //
    if (empty($error)) {
        $conn->begin_transaction();
        try {
            // Cek jika data pendaftar sudah ada -> UPDATE, jika tidak -> INSERT
            $stmt = $conn->prepare("SELECT id FROM pendaftar WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                // UPDATE data pendaftar
                $stmt_pendaftar = $conn->prepare("UPDATE pendaftar SET tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, asal_sekolah=?, nisn=?, nilai_rata_rata=? WHERE user_id=?");
                $stmt_pendaftar->bind_param("ssssssdi", $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $asal_sekolah, $nisn, $nilai_rata_rata, $user_id);
                $stmt_pendaftar->execute();
            } else {
                // INSERT data pendaftar
                $stmt_pendaftar = $conn->prepare("INSERT INTO pendaftar (user_id, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, asal_sekolah, nisn, nilai_rata_rata) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_pendaftar->bind_param("issssssd", $user_id, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $asal_sekolah, $nisn, $nilai_rata_rata);
                $stmt_pendaftar->execute();
            }
            $stmt->close();

            // Lakukan hal yang sama untuk data orang tua
            $stmt = $conn->prepare("SELECT id FROM orang_tua WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt_ortu = $conn->prepare("UPDATE orang_tua SET nama_ayah=?, pekerjaan_ayah=?, penghasilan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, penghasilan_ibu=? WHERE user_id=?");
                $stmt_ortu->bind_param("ssisssi", $nama_ayah, $pekerjaan_ayah, $penghasilan_ayah, $nama_ibu, $pekerjaan_ibu, $penghasilan_ibu, $user_id);
            } else {
                $stmt_ortu = $conn->prepare("INSERT INTO orang_tua (user_id, nama_ayah, pekerjaan_ayah, penghasilan_ayah, nama_ibu, pekerjaan_ibu, penghasilan_ibu) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_ortu->bind_param("isssissi", $user_id, $nama_ayah, $pekerjaan_ayah, $penghasilan_ayah, $nama_ibu, $pekerjaan_ibu, $penghasilan_ibu);
            }
            $stmt_ortu->execute();
            $stmt->close();

            // Proses Upload Dokumen
            foreach ($_FILES as $key => $file) {
                if ($file['error'] == UPLOAD_ERR_OK) {
                    $tipe_dokumen = $key;
                    $filename = uniqid() . '-' . basename($file["name"]);
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($file["tmp_name"], $target_file)) {
                        $stmt_doc = $conn->prepare("INSERT INTO dokumen (user_id, tipe_dokumen, path) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE path = VALUES(path)");
                        $stmt_doc->bind_param("iss", $user_id, $tipe_dokumen, $target_file);
                        $stmt_doc->execute();
                    }
                }
            }

            // Set status seleksi menjadi 'Belum diverifikasi' jika baru pertama kali submit
            $stmt = $conn->prepare("SELECT id FROM seleksi WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $stmt_seleksi = $conn->prepare("INSERT INTO seleksi (user_id, status) VALUES (?, 'Belum diverifikasi')");
                $stmt_seleksi->bind_param("i", $user_id);
                $stmt_seleksi->execute();
            }
            $stmt->close();

            $conn->commit();
            $success = "Data formulir berhasil disimpan!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Ambil data yang sudah ada untuk ditampilkan di form
$data_pendaftar = $conn->query("SELECT * FROM pendaftar WHERE user_id = $user_id")->fetch_assoc();
$data_ortu = $conn->query("SELECT * FROM orang_tua WHERE user_id = $user_id")->fetch_assoc();
$data_dokumen = $conn->query("SELECT * FROM dokumen WHERE user_id = $user_id");

$docs = [];
while($row = $data_dokumen->fetch_assoc()){ 
    $docs[$row['tipe_dokumen']] = $row['path'];
}

?>

<h3>Formulir Pendaftaran</h3>
<p>Silakan lengkapi semua data di bawah ini dengan benar. Anda dapat menyimpan dan mengubahnya kembali sebelum pendaftaran ditutup.</p>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form action="formulir.php" method="POST" enctype="multipart/form-data">
    <!-- Data Pribadi -->
    <div class="card mb-4">
        <div class="card-header">Data Pribadi</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_lengkap); ?>" disabled>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                    <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($data_pendaftar['tempat_lahir'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($data_pendaftar['tanggal_lahir'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="" disabled selected>Pilih...</option>
                    <option value="Laki-laki" <?php echo (($data_pendaftar['jenis_kelamin'] ?? '') == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo (($data_pendaftar['jenis_kelamin'] ?? '') == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat Lengkap</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($data_pendaftar['alamat'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Data Orang Tua -->
    <div class="card mb-4">
        <div class="card-header">Data Orang Tua</div>
        <div class="card-body">
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_ayah" class="form-label">Nama Ayah</label>
                    <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" value="<?php echo htmlspecialchars($data_ortu['nama_ayah'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
                    <input type="text" class="form-control" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($data_ortu['pekerjaan_ayah'] ?? ''); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="penghasilan_ayah" class="form-label">Penghasilan Ayah</label>
                    <input type="number" class="form-control" id="penghasilan_ayah" name="penghasilan_ayah" value="<?php echo htmlspecialchars($data_ortu['penghasilan_ayah'] ?? ''); ?>">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_ibu" class="form-label">Nama Ibu</label>
                    <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" value="<?php echo htmlspecialchars($data_ortu['nama_ibu'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
                    <input type="text" class="form-control" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($data_ortu['pekerjaan_ibu'] ?? ''); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="penghasilan_ibu" class="form-label">Penghasilan Ibu</label>
                    <input type="number" class="form-control" id="penghasilan_ibu" name="penghasilan_ibu" value="<?php echo htmlspecialchars($data_ortu['penghasilan_ibu'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Data Akademik dan Dokumen -->
    <div class="card mb-4">
        <div class="card-header">Data Akademik & Dokumen</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="asal_sekolah" class="form-label">Asal Sekolah</label>
                <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" value="<?php echo htmlspecialchars($data_pendaftar['asal_sekolah'] ?? ''); ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nisn" class="form-label">NISN</label>
                    <input type="text" class="form-control" id="nisn" name="nisn" value="<?php echo htmlspecialchars($data_pendaftar['nisn'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nilai_rata_rata" class="form-label">Nilai Rata-rata Raport</label>
                    <input type="number" step="0.01" class="form-control" id="nilai_rata_rata" name="nilai_rata_rata" value="<?php echo htmlspecialchars($data_pendaftar['nilai_rata_rata'] ?? ''); ?>" required>
                </div>
            </div>
            <hr>
            <h6>Upload Dokumen (PDF/JPG/PNG)</h6>
            <div class="mb-3">
                <label for="kk" class="form-label">Kartu Keluarga</label>
                <input class="form-control" type="file" id="kk" name="kk">
                <?php if(isset($docs['kk'])) echo "<small>File sudah diupload: <a href='".BASE_URL.str_replace('../', '/', $docs['kk'])."' target='_blank'>Lihat</a></small>"; ?>
            </div>
            <div class="mb-3">
                <label for="akta_lahir" class="form-label">Akta Lahir</label>
                <input class="form-control" type="file" id="akta_lahir" name="akta_lahir">
                 <?php if(isset($docs['akta_lahir'])) echo "<small>File sudah diupload: <a href='".BASE_URL.str_replace('../', '/', $docs['akta_lahir'])."' target='_blank'>Lihat</a></small>"; ?>
            </div>
            <div class="mb-3">
                <label for="raport" class="form-label">Raport Terakhir</label>
                <input class="form-control" type="file" id="raport" name="raport">
                 <?php if(isset($docs['raport'])) echo "<small>File sudah diupload: <a href='".BASE_URL.str_replace('../', '/', $docs['raport'])."' target='_blank'>Lihat</a></small>"; ?>
            </div>
             <div class="mb-3">
                <label for="ijazah" class="form-label">Ijazah</label>
                <input class="form-control" type="file" id="ijazah" name="ijazah">
                 <?php if(isset($docs['ijazah'])) echo "<small>File sudah diupload: <a href='".BASE_URL.str_replace('../', '/', $docs['ijazah'])."' target='_blank'>Lihat</a></small>"; ?>
            </div>
             <div class="mb-3">
                <label for="pas_foto" class="form-label">Pas Foto</label>
                <input class="form-control" type="file" id="pas_foto" name="pas_foto">
                 <?php if(isset($docs['pas_foto'])) echo "<small>File sudah diupload: <a href='".BASE_URL.str_replace('../', '/', $docs['pas_foto'])."' target='_blank'>Lihat</a></small>"; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Simpan Data Formulir</button>
</form>

<?php require_once '../includes/footer.php'; ?>
