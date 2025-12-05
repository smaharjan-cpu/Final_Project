<?php
// checkout.php - Checkout page with shipping/billing form and order summary
include 'db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Checkout | Online Computer Store";

// Redirect to cart if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total_price = 0;

// Load cart items from DB or session
try {
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
    // Session fallback
    $cart_items = $_SESSION['cart'] ?? [];
    $total_price = 0;
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
            // leave cart_items as is
        }
    }
}

// If cart is empty, redirect back to cart page
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

include 'header.php';
?>

<div class="container" style="margin-top: 2rem; margin-bottom: 4rem;">
    <h2>Checkout</h2>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Left: Checkout Form -->
        <div>
            <form method="POST" action="includes/process_order.php">
                <!-- Shipping Address Section -->
                <div style="margin-bottom: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #212529;">Shipping Address</h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Full Name</label>
                        <input type="text" name="ship_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Email</label>
                        <input type="email" name="ship_email" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Phone</label>
                        <input type="tel" name="ship_phone" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Address</label>
                        <input type="text" name="ship_address" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">City</label>
                            <input type="text" name="ship_city" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">State/Province</label>
                            <input type="text" name="ship_state" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">ZIP/Postal Code</label>
                            <input type="text" name="ship_zip" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Country</label>
                            <input type="text" name="ship_country" value="United States" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                    </div>
                </div>

                <!-- Billing Address Section -->
                <div style="margin-bottom: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #212529;">Billing Address</h3>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #212529;">
                            <input type="checkbox" name="same_as_shipping" checked>
                            Same as Shipping Address
                        </label>
                    </div>
                    <div id="billing-fields" style="display: none;">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Address</label>
                            <input type="text" name="bill_address" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">City</label>
                                <input type="text" name="bill_city" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">State/Province</label>
                                <input type="text" name="bill_state" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #212529;">Payment Method</h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Card Number</label>
                        <input type="text" name="card_number" placeholder="1234 5678 9012 3456" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Expiration Date</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">CVV</label>
                            <input type="text" name="card_cvv" placeholder="123" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #212529;">Cardholder Name</label>
                        <input type="text" name="card_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                    </div>

                    <button type="submit" class="button primary-button" style="width: 100%; padding: 1rem; font-size: 1.1rem; background: #ff9900; border: none; cursor: pointer; margin-top: 1.5rem;">
                        Place Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Order Summary -->
        <div style="background-color: #ffffff; padding: 2rem; border-radius: 8px; height: fit-content; border: 1px solid #dee2e6;">
            <h3 style="color: #000000; margin-top: 0; margin-bottom: 1rem;">Order Summary</h3>
            <hr style="border-color: #dee2e6;">

            <!-- Order Items -->
            <div style="margin-bottom: 1.5rem; max-height: 300px; overflow-y: auto;">
                <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #dee2e6; color: #000000;">
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #212529;"><?= htmlspecialchars(substr($item['name'], 0, 25)) ?></p>
                            <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">Qty: <?= $item['quantity'] ?></p>
                        </div>
                        <p style="margin: 0; font-weight: 600; color: #212529;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="border-color: #dee2e6;">

            <!-- Totals -->
            <div style="margin-bottom: 1rem; color: #000000;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($total_price, 2) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                    <span>Shipping:</span>
                    <span style="color: #28a745; font-weight: 600;">Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #000000;">
                    <span>Tax (estimated):</span>
                    <span>$<?= number_format($total_price * 0.08, 2) ?></span>
                </div>
            </div>

            <hr style="border-color: #dee2e6;">

            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.3rem; color: #000000;">
                <span>Total:</span>
                <span style="color: #ff9900;">$<?= number_format($total_price * 1.08, 2) ?></span>
            </div>

            <p style="margin-top: 1.5rem; font-size: 0.85rem; color: #666; text-align: center;">
                ✓ Secure checkout with SSL encryption
            </p>
        </div>
    </div>
</div>

<script>
    // Toggle billing address fields
    document.querySelector('input[name="same_as_shipping"]').addEventListener('change', function() {
        document.getElementById('billing-fields').style.display = this.checked ? 'none' : 'block';
    });
</script>

<?php include 'footer.php'; ?>
