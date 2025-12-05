<?php
if (!defined('ERROR_REPORTING_SET')) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    define('ERROR_REPORTING_SET', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin_page = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$path_prefix = $is_admin_page ? '../' : ''; 
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Online Computer Store'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $path_prefix ?>assets/css/style.css">
    <script>
        window.IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>
    <script src="<?= $path_prefix ?>assets/js/script.js" defer></script>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light border-bottom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= $path_prefix ?>index.php">
                <span style="color: #007bff; font-size: 1.5rem;">🖥️ Computer Store</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <nav class="navbar-nav me-auto">
                    <a class="nav-link" href="<?= $path_prefix ?>index.php">Home</a>
                    <a class="nav-link" href="<?= $path_prefix ?>products.php">Products</a>
                    <a class="nav-link" href="<?= $path_prefix ?>cart.php">Cart</a>
                </nav>

                <div class="d-flex align-items-center gap-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-theme-value="light" aria-pressed="true" aria-label="Light theme">
                            ☀️
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-theme-value="dark" aria-pressed="false" aria-label="Dark theme">
                            🌙
                        </button>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>cart.php">🛒 Cart</a></li>
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>order_history.php">📦 Orders</a></li>
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= $path_prefix ?>admin/dashboard.php">⚙️ Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>logout.php">🚪 Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $path_prefix ?>login.php" class="btn btn-sm btn-primary">Login</a>
                        <a href="<?= $path_prefix ?>register.php" class="btn btn-sm btn-outline-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $path_prefix ?>assets/js/darkmodetoggle.js"></script>

    <div class="main-content">