<?php
include '../db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cart'");
        $stmt->execute();
        $has = (bool)$stmt->fetchColumn();
        if ($has) {
            $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            $row = $check->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $newQty = max(1, $row['quantity'] + $quantity);
                $upd = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $upd->execute([$newQty, $row['id']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$user_id, $product_id, $quantity]);
            }
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (PDOException $e) {
    }
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
for ($i = 0; $i < count($_SESSION['cart']); $i++) {
    if ($_SESSION['cart'][$i]['product_id'] == $product_id) {
        $_SESSION['cart'][$i]['qty'] = max(1, $_SESSION['cart'][$i]['qty'] + $quantity);
        $found = true;
        break;
    }
}
if (!$found) {
    $_SESSION['cart'][] = ['product_id' => $product_id, 'qty' => $quantity];
}

echo json_encode(['success' => true]);
