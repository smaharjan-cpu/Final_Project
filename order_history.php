<?php
// order_history.php - Display user's order history
include 'db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Order History | Online Computer Store";

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=order_history.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

try {
    // Try to fetch orders from DB orders table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'orders'");
    $stmt->execute();
    $has_orders_table = (bool)$stmt->fetchColumn();
    
    if ($has_orders_table) {
        $query = $pdo->prepare("
            SELECT id, order_date, total_amount, status, shipping_address
            FROM orders
            WHERE user_id = ?
            ORDER BY order_date DESC
        ");
        $query->execute([$user_id]);
        $orders = $query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Table doesn't exist or query failed
    $orders = [];
}

include 'header.php';
?>

<div class="container" style="margin-top: 2rem; margin-bottom: 4rem;">
    <h2>Order History</h2>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 8px; margin-top: 2rem;">
            <p style="font-size: 1.2rem; color: #6c757d;">📦 No orders yet</p>
            <p style="color: #999; margin: 1rem 0;">You haven't placed any orders yet.</p>
            <a href="products.php" class="button primary-button">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="margin-top: 2rem;">
            <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="text-align: left; padding: 1.25rem; color: #212529; font-weight: 600;">Order ID</th>
                        <th style="text-align: left; padding: 1.25rem; color: #212529; font-weight: 600;">Date</th>
                        <th style="text-align: left; padding: 1.25rem; color: #212529; font-weight: 600;">Total</th>
                        <th style="text-align: center; padding: 1.25rem; color: #212529; font-weight: 600;">Status</th>
                        <th style="text-align: center; padding: 1.25rem; color: #212529; font-weight: 600;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        // Status badge styling
                        $status = strtolower($order['status'] ?? 'pending');
                        $status_color = '#ffc107'; // warning/pending (yellow)
                        $status_bg = '#fff3cd';
                        if ($status === 'completed' || $status === 'delivered') {
                            $status_color = '#28a745'; // success (green)
                            $status_bg = '#d4edda';
                        } elseif ($status === 'cancelled' || $status === 'failed') {
                            $status_color = '#dc3545'; // danger (red)
                            $status_bg = '#f8d7da';
                        } elseif ($status === 'processing' || $status === 'shipped') {
                            $status_color = '#0056b3'; // info (blue)
                            $status_bg = '#d1ecf1';
                        }
                    ?>
                        <tr style="border-bottom: 1px solid #dee2e6; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 1.25rem; color: #212529; font-weight: 600;">
                                #<?= htmlspecialchars($order['id']) ?>
                            </td>
                            <td style="padding: 1.25rem; color: #212529;">
                                <?= date('M d, Y', strtotime($order['order_date'])) ?>
                            </td>
                            <td style="padding: 1.25rem; color: #212529; font-weight: 600; font-size: 1.1rem; color: #ff9900;">
                                $<?= number_format($order['total_amount'], 2) ?>
                            </td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <span style="background-color: <?= $status_bg ?>; color: <?= $status_color ?>; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 600; display: inline-block;">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 1.25rem; text-align: center;">
                                <a href="order_details.php?id=<?= htmlspecialchars($order['id']) ?>" class="button primary-button" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
        <p style="margin: 0; color: #666; font-size: 0.95rem;">
            <strong>Need help with an order?</strong> 
            <a href="contact.php" style="color: #007bff; text-decoration: none;">Contact us</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
