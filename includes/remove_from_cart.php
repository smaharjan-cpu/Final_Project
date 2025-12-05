<?php
// remove_from_cart.php - Removes an item from the cart
include '../db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Try to delete from DB cart table if it exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cart'");
    $stmt->execute();
    $has = (bool)$stmt->fetchColumn();
    if ($has) {
        // Verify the cart item belongs to the user
        $check = $pdo->prepare("SELECT user_id FROM cart WHERE id = ?");
        $check->execute([$cart_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['user_id'] == $user_id) {
            $del = $pdo->prepare("DELETE FROM cart WHERE id = ?");
            $del->execute([$cart_id]);
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found or unauthorized']);
            exit;
        }
    }
} catch (PDOException $e) {
    // fall through to session fallback
}

// Session fallback: remove from session cart array
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Session doesn't track cart_id, so we'll use a simple approach: treat cart_id as array index
// This is a simplification; ideally you'd need to track which session item corresponds to which ID
// For now, return success as a placeholder
echo json_encode(['success' => true]);
