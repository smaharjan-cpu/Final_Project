<?php
// process_order.php - Process checkout and create order
session_start();
include '../db/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get form data
$shipping_name = $_POST['shipping_name'] ?? '';
$shipping_email = $_POST['shipping_email'] ?? '';
$shipping_address = $_POST['shipping_address'] ?? '';
$shipping_city = $_POST['shipping_city'] ?? '';
$shipping_state = $_POST['shipping_state'] ?? '';
$shipping_zip = $_POST['shipping_zip'] ?? '';
$shipping_phone = $_POST['shipping_phone'] ?? '';

$same_as_shipping = isset($_POST['same_as_shipping']) ? 1 : 0;

if ($same_as_shipping) {
    $billing_name = $shipping_name;
    $billing_address = $shipping_address;
    $billing_city = $shipping_city;
    $billing_state = $shipping_state;
    $billing_zip = $shipping_zip;
} else {
    $billing_name = $_POST['billing_name'] ?? '';
    $billing_address = $_POST['billing_address'] ?? '';
    $billing_city = $_POST['billing_city'] ?? '';
    $billing_state = $_POST['billing_state'] ?? '';
    $billing_zip = $_POST['billing_zip'] ?? '';
}

$payment_method = $_POST['payment_method'] ?? 'credit_card';
$card_number = $_POST['card_number'] ?? '';
$card_expiry = $_POST['card_expiry'] ?? '';
$card_cvv = $_POST['card_cvv'] ?? '';

try {
    // Get cart items
    $cart_items = [];
    $total_price = 0;

    // Try to get from database first
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image_url 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fallback to session
    if (empty($cart_items) && !empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $cart_items[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'quantity' => $_SESSION['cart'][$product['id']]
                ];
            }
        }
    }

    if (empty($cart_items)) {
        $_SESSION['error'] = "Your cart is empty.";
        header("Location: cart.php");
        exit();
    }

    // Calculate total
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // Add tax (8%)
    $tax = $total_price * 0.08;
    $total_with_tax = $total_price + $tax;

    // Begin transaction
    $pdo->beginTransaction();

    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, shipping_name, shipping_email, 
                           shipping_address, shipping_city, shipping_state, shipping_zip, shipping_phone,
                           billing_name, billing_address, billing_city, billing_state, billing_zip,
                           payment_method, status, order_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    $stmt->execute([
        $user_id, $total_with_tax, $shipping_name, $shipping_email,
        $shipping_address, $shipping_city, $shipping_state, $shipping_zip, $shipping_phone,
        $billing_name, $billing_address, $billing_city, $billing_state, $billing_zip,
        $payment_method
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order items and update inventory
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES (?, ?, ?, ?)");
    
    $update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    
    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
        
        // Auto-update inventory: decrease stock by quantity ordered
        $update_stock->execute([
            $item['quantity'],
            $item['product_id'],
            $item['quantity']
        ]);
    }

    // Clear cart from database
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Clear cart from session
    unset($_SESSION['cart']);

    // Commit transaction
    $pdo->commit();

    // Redirect to order confirmation
    $_SESSION['success'] = "Order placed successfully!";
    header("Location: order_details.php?id=" . $order_id);
    exit();

} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error'] = "Failed to place order. Please try again.";
    header("Location: checkout.php");
    exit();
}
