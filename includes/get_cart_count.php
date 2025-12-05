<?php
// get_cart_count.php - returns JSON with current cart count
include '../db/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
$count = 0;
try {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // If cart table exists, use DB
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cart'");
        $stmt->execute();
        $has = (bool)$stmt->fetchColumn();
        if ($has) {
            $q = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
            $q->execute([$user_id]);
            $count = (int)$q->fetchColumn();
        } else {
            // session fallback
            $count = 0;
            if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $it) {
                    $count += (int)($it['qty'] ?? 0);
                }
            }
        }
    } else {
        // not logged in - session cart
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $it) {
                $count += (int)($it['qty'] ?? 0);
            }
        }
    }
} catch (PDOException $e) {
    // fallback: session
    $count = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $it) {
            $count += (int)($it['qty'] ?? 0);
        }
    }
}

echo json_encode(['count' => $count]);
