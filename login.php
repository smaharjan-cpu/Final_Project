<?php
// login.php - User login page
include 'db/db_connect.php';
$page_title = "Login | Online Computer Store";

$email = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        try {
            // Check user credentials
            $stmt = $pdo->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password'])) {
                    // Start session
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    // Redirect to admin or home
                    $redirect = ($user['is_admin'] == 1) ? 'admin/dashboard.php' : 'index.php?login=1';
                    header("Location: $redirect");
                    exit();
                } else {
                    $errors[] = "Invalid email or password";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login error: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container">
    <div style="max-width: 500px; margin: 3rem auto;">
        <h2 style="text-align: center; margin-bottom: 2rem;">Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Login Error:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form name="login" method="POST" onsubmit="return validateLoginForm();">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="button primary-button" style="width: 100%; margin-top: 1rem;">
                Login
            </button>
        </form>

        <p style="text-align: center; margin-top: 2rem;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>

        <hr style="margin: 2rem 0;">

        <p style="text-align: center; font-weight: bold;">Demo Credentials:</p>
        <p style="text-align: center; color: #6c757d;">
            <strong>Email:</strong> admin@computerstore.com<br>
            <strong>Password:</strong> admin123
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
