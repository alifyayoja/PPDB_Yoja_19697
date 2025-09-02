<?php require_once '../includes/header_admin.php'; ?>

<h3>Manajemen Data Pendaftar</h3>
<p>Di bawah ini adalah daftar semua calon siswa yang telah membuat akun atau mengisi formulir.</p>

<?php
// Logika Pencarian dan Filter
$search_keyword = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT u.id, u.nama_lengkap, u.nik, u.email, p.asal_sekolah, s.status 
        FROM users u 
        LEFT JOIN pendaftar p ON u.id = p.user_id 
        LEFT JOIN seleksi s ON u.id = s.user_id";

$where_clauses = [];
if (!empty($search_keyword)) {
    $where_clauses[] = "(u.nama_lengkap LIKE '%$search_keyword%' OR u.nik LIKE '%$search_keyword%' OR p.asal_sekolah LIKE '%$search_keyword%')";
}
if (!empty($filter_status)) {
    $where_clauses[] = "s.status = '$filter_status'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY u.created_at DESC";

$result = $conn->query($sql);
?>

<!-- Form Pencarian dan Filter -->
<div class="card mb-4">
    <div class="card-header">Cari & Filter Pendaftar</div>
    <div class="card-body">
        <form action="pendaftar.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama, NIK, Sekolah..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Belum diverifikasi" <?php echo ($filter_status == 'Belum diverifikasi') ? 'selected' : ''; ?>>Belum diverifikasi</option>
                        <option value="Lengkap dan Valid" <?php echo ($filter_status == 'Lengkap dan Valid') ? 'selected' : ''; ?>>Lengkap dan Valid</option>
                        <option value="Tidak Lengkap / Ditolak" <?php echo ($filter_status == 'Tidak Lengkap / Ditolak') ? 'selected' : ''; ?>>Tidak Lengkap / Ditolak</option>
                        <option value="Diterima" <?php echo ($filter_status == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                        <option value="Tidak diterima" <?php echo ($filter_status == 'Tidak diterima') ? 'selected' : ''; ?>>Tidak diterima</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Pendaftar -->
<div class="card">
    <div class="card-header">Daftar Pendaftar</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>Asal Sekolah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['nik']); ?></td>
                            <td><?php echo htmlspecialchars($row['asal_sekolah'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['status'] ?? 'Baru Registrasi'); ?></span>
                            </td>
                            <td>
                                <a href="detail_pendaftar.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data pendaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
