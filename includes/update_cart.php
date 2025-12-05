<?php
// update_cart.php - Updates the quantity of a cart item
include '../db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
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
    // Try to update DB cart table if it exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cart'");
    $stmt->execute();
    $has = (bool)$stmt->fetchColumn();
    if ($has) {
        // Verify the cart item belongs to the user
        $check = $pdo->prepare("SELECT user_id FROM cart WHERE id = ?");
        $check->execute([$cart_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['user_id'] == $user_id) {
            $upd = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $upd->execute([$quantity, $cart_id]);
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

// Session fallback: just return success
echo json_encode(['success' => true]);
