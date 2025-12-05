<?php
// add_to_wishlist.php - Adds a product to the user's wishlist (DB if available, else session fallback)
include '../db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required', 'redirect' => 'login.php']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Try DB insert into wishlist table if available
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'wishlist'");
    $stmt->execute();
    $has = (bool)$stmt->fetchColumn();
    if ($has) {
        // Check if already present
        $check = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$user_id, $product_id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => true, 'message' => 'Already in wishlist']);
            exit;
        }

        $ins = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, added_at) VALUES (?, ?, NOW())");
        $ins->execute([$user_id, $product_id]);
        echo json_encode(['success' => true]);
        exit;
    }
} catch (PDOException $e) {
    // ignore and fallback to session
}

// Fallback: use session-stored wishlist (array of product IDs)
if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}
if (!in_array($product_id, $_SESSION['wishlist'])) {
    $_SESSION['wishlist'][] = $product_id;
}

echo json_encode(['success' => true]);
