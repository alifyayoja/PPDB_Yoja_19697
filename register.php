<?php
require_once '../config/config.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nik = trim($_POST['nik']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nama_lengkap) || empty($nik) || empty($email) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($nik) != 16) {
        $error = "NIK harus terdiri dari 16 digit.";
    } else {
        // Cek jika email atau NIK sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR nik = ?");
        $stmt->bind_param("ss", $email, $nik);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email atau NIK sudah terdaftar.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru
            $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, nik, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama_lengkap, $nik, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $error = "Registrasi gagal, silakan coba lagi.";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Registrasi Akun Siswa</div>
            <div class="card-body">
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control" id="nik" name="nik" required maxlength="16">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
