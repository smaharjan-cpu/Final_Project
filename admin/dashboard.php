<?php
// admin/dashboard.php - Admin dashboard
session_start();
include '../db/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Admin Dashboard | Online Computer Store";

// Get statistics
$stats = [];
try {
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_revenue'] = $result['total'] ?? 0;

    // Recent orders
    $stmt = $pdo->prepare("
        SELECT o.id, u.name, o.total_price, o.order_date, o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $stats['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

include '../header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <h1>Admin Dashboard</h1>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 2rem 0;">
        <!-- Total Products -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Total Products</h3>
            <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                <?= $stats['total_products'] ?? 0 ?>
            </p>
            <a href="products.php" style="color: white; text-decoration: underline;">Manage Products →</a>
        </div>

        <!-- Total Orders -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Total Orders</h3>
            <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                <?= $stats['total_orders'] ?? 0 ?>
            </p>
            <a href="orders.php" style="color: white; text-decoration: underline;">View Orders →</a>
        </div>

        <!-- Total Users -->
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Total Users</h3>
            <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                <?= $stats['total_users'] ?? 0 ?>
            </p>
            <p style="margin: 0;">Registered Users</p>
        </div>

        <!-- Total Revenue -->
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Total Revenue</h3>
            <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                $<?= number_format($stats['total_revenue'] ?? 0, 2) ?>
            </p>
            <p style="margin: 0;">All Orders</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 style="margin-top: 3rem; margin-bottom: 1rem;">Quick Actions</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="products.php" class="button primary-button">Manage Products</a>
        <a href="products.php?action=add" class="button primary-button">Add New Product</a>
        <a href="orders.php" class="button secondary-button">View All Orders</a>
        <a href="../logout.php" class="button danger-button">Logout</a>
    </div>

    <!-- Recent Orders -->
    <?php if (!empty($stats['recent_orders'])): ?>
        <h2 style="margin-top: 3rem;">Recent Orders</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Order ID</th>
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Customer</th>
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Total</th>
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Status</th>
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Date</th>
                        <th style="text-align: left; padding: 1rem; font-weight: bold;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 1rem;">#<?= $order['id'] ?></td>
                            <td style="padding: 1rem;"><?= htmlspecialchars($order['name']) ?></td>
                            <td style="padding: 1rem;">$<?= number_format($order['total_price'], 2) ?></td>
                            <td style="padding: 1rem;">
                                <span style="background-color: <?= $order['status'] === 'completed' ? '#d4edda' : '#fff3cd' ?>; 
                                           color: <?= $order['status'] === 'completed' ? '#155724' : '#856404' ?>;
                                           padding: 0.25rem 0.75rem; border-radius: 4px;">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                            <td style="padding: 1rem;">
                                <a href="orders.php?id=<?= $order['id'] ?>" class="button secondary-button" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
