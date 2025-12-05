<?php
// index.php: The main landing page with the video background
include 'db/db_connect.php'; 
$page_title = "Home | Online Computer Store"; 

$featured_products = [];
$db_error = false;

try {
    
    $sql_featured = "SELECT id, name, price, image_url, description FROM products ORDER BY id DESC LIMIT 4"; 
    $result_featured = $pdo->query($sql_featured);

    if ($result_featured && $result_featured->rowCount() > 0) {
        $featured_products = $result_featured->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $db_error = true;
}

$greeting = "Welcome to the Online Computer Store!";
if (isset($_SESSION['user_name'])) {
    $greeting = "Hello, " . htmlspecialchars($_SESSION['user_name']) . "!";
}

include 'header.php';
?>

<!-- Hero Video Section -->
<section class="hero-video-container">
    <video autoplay muted loop id="video-background" class="video-background">
        <source src="assets/videos/intro.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <div class="hero-content-overlay">
        <h2><?= htmlspecialchars($greeting) ?></h2>
        <p>Your one-stop shop for the latest in computer technology.</p>
        <a href="products.php" class="button primary-button">Browse Our Inventory</a>
    </div>
</section>

<!-- Database Setup Notice -->
<?php if ($db_error): ?>
    <div class="container" style="margin-top: 3rem;">
        <div class="alert alert-info" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
            <h4 style="margin-top: 0;">⚙️ Database Setup Required</h4>
            <p>The database needs to be initialized. Click the button below to set it up automatically.</p>
            <a href="setup.php" class="button primary-button">Initialize Database</a>
        </div>
    </div>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if (!empty($featured_products)): ?>
    <div class="container featured-section">
        <div class="section-header">
            <h2>🔥 Featured Products</h2>
            <p class="section-subtitle">Check out our best-selling items</p>
        </div>
        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <img src="<?= htmlspecialchars($product['image_url'] ?? 'assets/images/default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-content">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 80)) ?>...</p>
                        <p class="price">$<?= number_format($product['price'], 2) ?></p>
                        <div class="product-actions">
                            <a href="product.php?id=<?= $product['id'] ?>" class="button secondary-button">View Details</a>
                            <button class="button primary-button" onclick="StoreApp.addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to upgrade your setup?</h2>
            <p>Explore thousands of products with guaranteed quality and fast shipping.</p>
            <a href="products.php" class="button primary-button btn-lg">Shop Now</a>
        </div>
    </div>
</section>

<?php 
include 'footer.php';
?>