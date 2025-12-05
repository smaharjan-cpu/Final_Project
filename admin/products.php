<?php
// admin/products.php - Manage products
session_start();
include '../db/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Manage Products | Admin";
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;

// Handle product deletion
if ($action === 'delete' && $product_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['success'] = "Product deleted successfully!";
        header("Location: products.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to delete product.";
    }
}

// Handle product save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $specifications = $_POST['specifications'];
    $stock = $_POST['stock'];
    $image_url = $_POST['image_url'];
    
    try {
        if ($product_id) {
            // Update existing product
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, category = ?, 
                                   description = ?, specifications = ?, stock = ?, image_url = ? 
                                   WHERE id = ?");
            $stmt->execute([$name, $price, $category, $description, $specifications, $stock, $image_url, $product_id]);
            $_SESSION['success'] = "Product updated successfully!";
        } else {
            // Insert new product
            $stmt = $pdo->prepare("INSERT INTO products (name, price, category, description, 
                                   specifications, stock, image_url) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $category, $description, $specifications, $stock, $image_url]);
            $_SESSION['success'] = "Product added successfully!";
        }
        header("Location: products.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to save product.";
    }
}

// Get product for editing
$product = null;
if ($action === 'edit' && $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all products for listing
$products = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../header.php';
?>

<div class="container my-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Products List -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Products</h1>
            <div>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="products.php?action=add" class="btn btn-warning">Add New Product</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><?= $prod['id'] ?></td>
                            <td>
                                <img src="../<?= htmlspecialchars($prod['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($prod['name']) ?>"
                                     style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                            </td>
                            <td><?= htmlspecialchars($prod['name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($prod['category']) ?></span></td>
                            <td>$<?= number_format($prod['price'], 2) ?></td>
                            <td><?= $prod['stock'] ?></td>
                            <td>
                                <a href="products.php?action=edit&id=<?= $prod['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="products.php?action=delete&id=<?= $prod['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Add/Edit Product Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4"><?= $product ? 'Edit Product' : 'Add New Product' ?></h2>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= $product['name'] ?? '' ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" class="form-control" step="0.01"
                                           value="<?= $product['price'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control"
                                           value="<?= $product['stock'] ?? '0' ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="desktop" <?= ($product['category'] ?? '') === 'desktop' ? 'selected' : '' ?>>Desktop</option>
                                    <option value="laptop" <?= ($product['category'] ?? '') === 'laptop' ? 'selected' : '' ?>>Laptop</option>
                                    <option value="monitor" <?= ($product['category'] ?? '') === 'monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="keyboard" <?= ($product['category'] ?? '') === 'keyboard' ? 'selected' : '' ?>>Keyboard</option>
                                    <option value="mouse" <?= ($product['category'] ?? '') === 'mouse' ? 'selected' : '' ?>>Mouse</option>
                                    <option value="headphones" <?= ($product['category'] ?? '') === 'headphones' ? 'selected' : '' ?>>Headphones</option>
                                    <option value="storage" <?= ($product['category'] ?? '') === 'storage' ? 'selected' : '' ?>>Storage</option>
                                    <option value="graphics-card" <?= ($product['category'] ?? '') === 'graphics-card' ? 'selected' : '' ?>>Graphics Card</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image URL</label>
                                <input type="text" name="image_url" class="form-control"
                                       value="<?= $product['image_url'] ?? 'assets/images/default.jpg' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required><?= $product['description'] ?? '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Specifications (JSON format)</label>
                                <textarea name="specifications" class="form-control" rows="6"><?= $product['specifications'] ?? '{}' ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="save_product" class="btn btn-warning">
                                    <?= $product ? 'Update Product' : 'Add Product' ?>
                                </button>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
