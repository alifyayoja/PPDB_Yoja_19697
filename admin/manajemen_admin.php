<?php 
require_once '../includes/header_admin.php'; 

$error = '';
$success = '';

// Logika untuk memproses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aksi: Tambah Admin Baru
    if (isset($_POST['add_admin'])) {
        $username = trim($_POST['username']);
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $password = $_POST['password'];

        if (empty($username) || empty($nama_lengkap) || empty($password)) {
            $error = "Semua field untuk menambah admin wajib diisi.";
        } else {
            // Cek jika username sudah ada
            $stmt_check = $conn->prepare("SELECT id FROM admin WHERE username = ?");
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $error = "Username sudah digunakan. Silakan pilih username lain.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO admin (username, nama_lengkap, password) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $username, $nama_lengkap, $hashed_password);
                if ($stmt_insert->execute()) {
                    $success = "Admin baru berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan admin baru.";
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
        }
    }

    // Aksi: Ganti Password Admin yang Sedang Login
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Semua field untuk ganti password wajib diisi.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru dan konfirmasi password tidak cocok.";
        } else {
            // Ambil password saat ini dari DB
            $stmt_get = $conn->prepare("SELECT password FROM admin WHERE id = ?");
            $stmt_get->bind_param("i", $admin_id);
            $stmt_get->execute();
            $stmt_get->bind_result($db_password);
            $stmt_get->fetch();
            $stmt_get->close();

            if (password_verify($current_password, $db_password)) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $new_hashed_password, $admin_id);
                if ($stmt_update->execute()) {
                    $success = "Password Anda berhasil diubah.";
                } else {
                    $error = "Gagal mengubah password.";
                }
                $stmt_update->close();
            } else {
                $error = "Password saat ini yang Anda masukkan salah.";
            }
        }
    }
}

// Ambil daftar admin
$admins = $conn->query("SELECT id, username, nama_lengkap FROM admin");

?>

<h3>Manajemen Admin</h3>

<?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">Daftar Admin</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>Username</th><th>Nama Lengkap</th></tr></thead>
                        <tbody>
                            <?php while($admin = $admins->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['nama_lengkap']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Ganti Password Saya (<?php echo htmlspecialchars($nama_admin); ?>)</div>
            <div class="card-body">
                <form action="manajemen_admin.php" method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Ganti Password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">Tambah Admin Baru</div>
            <div class="card-body">
                <form action="manajemen_admin.php" method="POST">
                    <input type="hidden" name="add_admin" value="1">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
