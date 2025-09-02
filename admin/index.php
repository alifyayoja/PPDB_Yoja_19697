<?php
require_once '../config/config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            // Note: In a real app, you'd use password_verify().
            // The sample hash is just a placeholder.
            // For this example, we'll just compare directly for simplicity since we don't have a registration page for admins.
            // A proper implementation should hash the password upon admin creation.
            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['role'] = 'admin';
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Username atau password salah.";
            }
        } else {
            $error = "Username atau password salah.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<?php require_once '../includes/header.php'; // Menggunakan header umum untuk halaman login ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Login Admin</div>
            <div class="card-body">
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
