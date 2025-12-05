<?php
// products.php - Browse all products with categories
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

include 'db/db_connect.php';
$page_title = "Products | Online Computer Store";

$category_filter = $_GET['category'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sort_by = $_GET['sort_by'] ?? 'name';
$order = $_GET['order'] ?? 'ASC';

// Build query
$products = [];
$sql = "SELECT id, name, price, image_url, category, description, stock FROM products WHERE 1=1";
$params = [];

// Add category filter
if ($category_filter !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

// Add search filter
if (!empty($search_term)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search = "%" . $search_term . "%";
    $params[] = $search;
    $params[] = $search;
}

// Add price filters
if ($min_price !== null) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
}
if ($max_price !== null) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

// Add sorting
$allowed_sort = ['name', 'price', 'category'];
$allowed_order = ['ASC', 'DESC'];
$sort_column = in_array($sort_by, $allowed_sort) ? $sort_by : 'name';
$sort_order = in_array(strtoupper($order), $allowed_order) ? strtoupper($order) : 'ASC';
$sql .= " ORDER BY $sort_column $sort_order";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching products";
}

// Get unique categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

include 'header.php';
?>

<div class="container">
    <h2 style="margin: 2rem 0;">Browse Products</h2>

    <!-- Search and Filter Form -->
    <div style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; gap: 1rem;">
            <!-- Search Bar -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?= htmlspecialchars($search_term) ?>" 
                       style="flex: 1; min-width: 200px; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px;">
                <button type="submit" class="button primary-button">Search</button>
            </div>

            <!-- Filters Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <!-- Category Filter -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Category:</label>
                    <select name="category" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; background: white; color: #000;">
                        <option value="all" <?= $category_filter === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('-', ' ', htmlspecialchars($cat))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Min Price -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Min Price:</label>
                    <select name="min_price" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; background: white; color: #000;">
                        <option value="">No Min</option>
                        <option value="0" <?= $min_price === 0.0 ? 'selected' : '' ?>>$0</option>
                        <option value="100" <?= $min_price === 100.0 ? 'selected' : '' ?>>$100</option>
                        <option value="500" <?= $min_price === 500.0 ? 'selected' : '' ?>>$500</option>
                        <option value="1000" <?= $min_price === 1000.0 ? 'selected' : '' ?>>$1000</option>
                        <option value="2000" <?= $min_price === 2000.0 ? 'selected' : '' ?>>$2000</option>
                    </select>
                </div>

                <!-- Max Price -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Max Price:</label>
                    <select name="max_price" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; background: white; color: #000;">
                        <option value="">No Max</option>
                        <option value="500" <?= $max_price === 500.0 ? 'selected' : '' ?>>$500</option>
                        <option value="1000" <?= $max_price === 1000.0 ? 'selected' : '' ?>>$1000</option>
                        <option value="2000" <?= $max_price === 2000.0 ? 'selected' : '' ?>>$2000</option>
                        <option value="3000" <?= $max_price === 3000.0 ? 'selected' : '' ?>>$3000</option>
                        <option value="5000" <?= $max_price === 5000.0 ? 'selected' : '' ?>>$5000</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sort By:</label>
                    <select name="sort_by" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; background: white; color: #000;">
                        <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Name</option>
                        <option value="price" <?= $sort_by === 'price' ? 'selected' : '' ?>>Price</option>
                        <option value="category" <?= $sort_by === 'category' ? 'selected' : '' ?>>Category</option>
                    </select>
                </div>

                <!-- Order -->
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Order:</label>
                    <select name="order" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; background: white; color: #000;">
                        <option value="ASC" <?= strtoupper($order) === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                        <option value="DESC" <?= strtoupper($order) === 'DESC' ? 'selected' : '' ?>>Descending</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Products Display -->
    <?php if (!empty($products)): ?>
        <div style="margin-bottom: 2rem; color: #666;">
            Showing <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?>
            <?php if ($category_filter !== 'all'): ?>
                in <strong><?= ucfirst(str_replace('-', ' ', htmlspecialchars($category_filter))) ?></strong>
            <?php endif; ?>
        </div>

        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <?php foreach ($products as $product): ?>
                 <div class="product-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; transition: all 0.3s; cursor: pointer;" 
                     onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'; this.style.transform='translateY(-4px)';" 
                     onmouseout="this.style.boxShadow='none'; this.style.transform='translateY(0)';"
                     onclick="window.location='product.php?id=<?= $product['id'] ?>'">
                    
                    <!-- Image Container -->
                    <div style="background: #f8f9fa; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                        <?php
                        // Prefer the image_url if it points to an existing local asset, else try to derive a local filename, else use default
                        $primaryImage = 'assets/images/default.jpg';
                        if (!empty($product['image_url']) && file_exists(__DIR__ . '/' . $product['image_url'])) {
                            $primaryImage = $product['image_url'];
                        } else {
                            // Try to derive from the image_url or product name
                            if (!empty($product['image_url'])) {
                                $candidate = __DIR__ . '/' . $product['image_url'];
                                if (file_exists($candidate)) {
                                    $primaryImage = str_replace(__DIR__ . '/', '', $candidate);
                                }
                            }
                            if ($primaryImage === 'assets/images/default.jpg') {
                                // derive basename from product name
                                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $product['name']));
                                $candidatePath = __DIR__ . "/assets/images/{$slug}.jpg";
                                if (file_exists($candidatePath)) {
                                    $primaryImage = 'assets/images/' . basename($candidatePath);
                                }
                            }
                        }
                        ?>
                        <img src="<?= htmlspecialchars($primaryImage) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="max-width: 90%; max-height: 90%; object-fit: contain;">
                        <span style="position: absolute; top: 0.5rem; right: 0.5rem; background: #ff9900; color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">SALE</span>
                    </div>

                    <!-- Product Info -->
                    <div style="padding: 1rem;">
                        <!-- Rating -->
                        <div style="margin-bottom: 0.5rem;">
                            <span style="color: #ff9900; font-size: 0.9rem;">★★★★★</span>
                            <span style="color: #999; font-size: 0.8rem;">(<?= rand(100, 999) ?>)</span>
                        </div>

                        <!-- Product Name -->
                        <h3 style="margin: 0.5rem 0; font-size: 0.95rem; font-weight: 600; color: #333; min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>

                        <!-- Price -->
                        <div style="margin: 0.75rem 0;">
                            <span style="font-size: 1.5rem; font-weight: bold; color: #ff9900;">
                                $<?= number_format($product['price'], 2) ?>
                            </span>
                            <span style="font-size: 0.85rem; color: #999; text-decoration: line-through; margin-left: 0.5rem;">
                                $<?= number_format($product['price'] * 1.15, 2) ?>
                            </span>
                        </div>

                        <!-- Description (hidden on hover, shown otherwise) -->
                        <p style="font-size: 0.8rem; color: #666; margin: 0.75rem 0; min-height: 30px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars(substr($product['description'] ?? '', 0, 80)) ?>
                        </p>

                        <!-- Stock Status -->
                        <p style="font-size: 0.8rem; color: <?= $product['stock'] > 5 ? '#28a745' : '#ff9900' ?>; margin: 0.5rem 0; font-weight: 600;">
                            <?php if ($product['stock'] > 5): ?>
                                ✓ In Stock
                            <?php elseif ($product['stock'] > 0): ?>
                                ⚠ Only <?= $product['stock'] ?> left
                            <?php endif; ?>
                        </p>

                        <!-- Action Button: single full-width Add to cart -->
                        <div style="margin-top: 1rem;">
                            <button class="button primary-button" onclick="event.stopPropagation(); StoreApp.addToCart(<?= $product['id'] ?>);" 
                                    style="width: 100%; padding: 0.65rem; font-size: 0.95rem; background: #ff9900; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                🛒 Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem 2rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 3rem;">
            <p style="font-size: 1.3rem; color: #6c757d; margin-bottom: 1rem;">📦 No products found</p>
            <p style="color: #999; margin-bottom: 1.5rem;">Try adjusting your search filters</p>
            <a href="products.php" class="button primary-button">View All Products</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
