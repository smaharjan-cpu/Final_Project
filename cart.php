<?php
// cart.php - Shopping cart page
include 'db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Shopping Cart | Online Computer Store";

// If user not logged in: show inline login form (post to login.php with redirect back to cart)
if (!isset($_SESSION['user_id'])) {
    include 'header.php';
    ?>
    <div class="container mt-4">
        <h2>Shopping Cart</h2>
        <div class="card bg-light mt-3">
            <div class="card-body p-4">
                <p class="fs-5 mb-3">Please log in to view your cart and wishlist.</p>
                <form method="POST" action="login.php" style="max-width:420px;">
                    <input type="hidden" name="redirect" value="cart.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" required class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-warning" type="submit">Login</button>
                        <a class="btn btn-secondary" href="register.php">Register</a>
                    </div>
                </form>
                <p class="mt-3 text-muted">After you log in you'll be returned to this page to see your saved cart and wishlist.</p>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$wishlist_items = [];
$total_price = 0;

try {
    // Load cart items from DB (if cart table exists)
    $stmt = $pdo->prepare(
        "SELECT c.id as cart_id, p.id, p.name, p.price, p.image_url, c.quantity
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.user_id = ?
         ORDER BY c.added_at DESC"
    );
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    // Fallback to session cart if DB operation fails
    $cart_items = $_SESSION['cart'] ?? [];
    $total_price = 0;
    // If session cart contains ids, fetch product info
    if (!empty($cart_items) && isset($cart_items[0]['product_id'])) {
        $ids = array_map('intval', array_column($cart_items, 'product_id'));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $byId = [];
            foreach ($products as $p) $byId[$p['id']] = $p;
            $tmp = [];
            foreach ($cart_items as $it) {
                $pid = (int)$it['product_id'];
                $qty = (int)$it['qty'];
                $prod = $byId[$pid] ?? null;
                if (!$prod) continue;
                $prod['quantity'] = $qty;
                $tmp[] = $prod;
                $total_price += $prod['price'] * $qty;
            }
            $cart_items = $tmp;
        } catch (PDOException $ex) {
            // leave cart_items as session data
        }
    }
}

// Load wishlist items: try DB 'wishlist' table, otherwise session fallback
try {
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.added_at DESC");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // session fallback
    $sess_w = $_SESSION['wishlist'] ?? [];
    if (!empty($sess_w) && isset($sess_w[0])) {
        $ids = array_map('intval', $sess_w);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $wishlist_items = [];
        }
    }
}

include 'header.php';
?>

<div class="container">
    <h2 class="my-4">Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <p class="fs-5 text-muted">Your cart is empty</p>
            <a href="products.php" class="btn btn-warning">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="border-bottom">
                            <tr>
                                <th class="text-start p-3">Product</th>
                                <th class="text-center p-3">Price</th>
                                <th class="text-center p-3">Quantity</th>
                                <th class="text-center p-3">Total</th>
                                <th class="text-center p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="border-bottom">
                                    <td class="p-3">
                                        <div class="d-flex gap-3 align-items-center">
                                            <img src="<?= htmlspecialchars($item['image_url'] ?? 'assets/images/default.jpg') ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                            <div>
                                                <a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none text-primary">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center p-3">
                                        $<?= number_format($item['price'], 2) ?>
                                    </td>
                                    <td class="text-center p-3">
                                        <input type="number" value="<?= $item['quantity'] ?>" min="1" 
                                               onchange="StoreApp.updateCartQuantity(<?= $item['cart_id'] ?>, this.value)"
                                               class="form-control text-center" style="width: 80px; display: inline-block;">
                                    </td>
                                    <td class="text-center p-3 fw-bold">
                                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </td>
                                    <td class="text-center p-3">
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="StoreApp.removeFromCart(<?= $item['cart_id'] ?>)">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card border shadow-sm">
                    <div class="card-body p-4 bg-light" style="color: #000000 !important;">
                        <h3 class="mb-3" style="color: #000000 !important;">Order Summary</h3>
                        <hr class="border-secondary">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color: #000000 !important;">Subtotal:</span>
                                <span style="color: #000000 !important;">$<?= number_format($total_price, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color: #000000 !important;">Shipping:</span>
                                <span style="color: #000000 !important;">Free</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color: #000000 !important;">Tax (estimated):</span>
                                <span style="color: #000000 !important;">$<?= number_format($total_price * 0.08, 2) ?></span>
                            </div>
                        </div>
                        <hr class="border-secondary">
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                            <span style="color: #000000 !important;">Total:</span>
                            <span style="color: #000000 !important;">$<?= number_format($total_price * 1.08, 2) ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-warning w-100 mb-3">
                            Proceed to Checkout
                        </a>

                        <a href="products.php" class="btn btn-secondary w-100">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
