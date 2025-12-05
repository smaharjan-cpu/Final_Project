<?php
// order_details.php - Display details for a specific order
include 'db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Order Details | Online Computer Store";

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=order_details.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = null;
$order_items = [];

if ($order_id <= 0) {
    header("Location: order_history.php");
    exit();
}

try {
    // Fetch order details
    $stmt = $pdo->prepare("
        SELECT id, order_date, total_amount, status, shipping_address, billing_address, payment_method
        FROM orders
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: order_history.php");
        exit();
    }

    // Fetch order items
    $items_stmt = $pdo->prepare("
        SELECT oi.product_id, p.name, p.price, oi.quantity, (oi.quantity * p.price) as subtotal
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: order_history.php");
    exit();
}

include 'header.php';
?>

<div class="container" style="margin-top: 2rem; margin-bottom: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Order #<?= htmlspecialchars($order['id']) ?></h2>
        <a href="order_history.php" style="color: #007bff; text-decoration: none;">← Back to Orders</a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Left: Order Details -->
        <div>
            <!-- Order Status -->
            <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">Order Status</p>
                        <h3 style="margin: 0.5rem 0 0 0; color: #212529;">
                            <?php 
                            $status = strtolower($order['status'] ?? 'pending');
                            if ($status === 'completed' || $status === 'delivered') {
                                echo '✓ ' . ucfirst($order['status']);
                            } elseif ($status === 'processing' || $status === 'shipped') {
                                echo '⏳ ' . ucfirst($order['status']);
                            } elseif ($status === 'cancelled' || $status === 'failed') {
                                echo '✕ ' . ucfirst($order['status']);
                            } else {
                                echo ucfirst($order['status']);
                            }
                            ?>
                        </h3>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">Order Date</p>
                        <p style="margin: 0.5rem 0 0 0; color: #212529; font-weight: 600;">
                            <?= date('M d, Y', strtotime($order['order_date'])) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #212529; margin-bottom: 1rem;">Items Ordered</h3>
                <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="text-align: left; padding: 1rem; color: #212529; font-weight: 600;">Product</th>
                                <th style="text-align: center; padding: 1rem; color: #212529; font-weight: 600;">Quantity</th>
                                <th style="text-align: right; padding: 1rem; color: #212529; font-weight: 600;">Price</th>
                                <th style="text-align: right; padding: 1rem; color: #212529; font-weight: 600;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 1rem; color: #212529;">
                                        <a href="product.php?id=<?= htmlspecialchars($item['product_id']) ?>" style="color: #007bff; text-decoration: none;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; color: #212529;">
                                        <?= $item['quantity'] ?>
                                    </td>
                                    <td style="text-align: right; padding: 1rem; color: #212529;">
                                        $<?= number_format($item['price'], 2) ?>
                                    </td>
                                    <td style="text-align: right; padding: 1rem; color: #212529; font-weight: 600;">
                                        $<?= number_format($item['subtotal'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shipping & Billing Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                    <h4 style="margin: 0 0 1rem 0; color: #212529;">Shipping Address</h4>
                    <p style="margin: 0; color: #212529; line-height: 1.6;">
                        <?= htmlspecialchars($order['shipping_address'] ?? 'Not provided') ?>
                    </p>
                </div>
                <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                    <h4 style="margin: 0 0 1rem 0; color: #212529;">Billing Address</h4>
                    <p style="margin: 0; color: #212529; line-height: 1.6;">
                        <?= htmlspecialchars($order['billing_address'] ?? 'Same as shipping') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary -->
        <div>
            <div style="background-color: #ffffff; padding: 2rem; border-radius: 8px; border: 1px solid #dee2e6; height: fit-content;">
                <h3 style="color: #000000; margin-top: 0; margin-bottom: 1rem;">Order Summary</h3>
                <hr style="border-color: #dee2e6;">

                <div style="margin-bottom: 1rem; color: #000000;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($order['total_amount'] / 1.08, 2) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                        <span>Shipping:</span>
                        <span style="color: #28a745; font-weight: 600;">Free</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                        <span>Tax (8%):</span>
                        <span>$<?= number_format($order['total_amount'] * 0.08 / 1.08, 2) ?></span>
                    </div>
                </div>

                <hr style="border-color: #dee2e6;">

                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.3rem; color: #000000; margin-bottom: 2rem;">
                    <span>Total:</span>
                    <span style="color: #ff9900;">$<?= number_format($order['total_amount'], 2) ?></span>
                </div>

                <div style="padding: 1rem; background: #e8f5e9; border-radius: 4px; border-left: 4px solid #28a745;">
                    <p style="margin: 0; color: #2e7d32; font-size: 0.9rem;">
                        <strong>Payment Method:</strong><br>
                        <?= htmlspecialchars($order['payment_method'] ?? 'Credit Card') ?>
                    </p>
                </div>

                <a href="products.php" class="button primary-button" style="width: 100%; text-align: center; display: block; margin-top: 1.5rem; padding: 0.75rem;">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
