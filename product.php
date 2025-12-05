<?php
// product.php - Product detail page
include 'db/db_connect.php';

$product_id = $_GET['id'] ?? 0;
$product = null;
$error_message = '';

if ($product_id <= 0) {
    $error_message = "Invalid product ID";
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, category, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error_message = "Product not found";
        } else {
            $page_title = htmlspecialchars($product['name']) . " | Online Computer Store";
        }
    } catch (PDOException $e) {
        $error_message = "Error loading product";
    }
}

if (!isset($page_title)) {
    $page_title = "Product Details | Online Computer Store";
}

include 'header.php';
?>


<div class="container">
    <?php if ($error_message): ?>
        <div class="alert alert-danger" style="margin-top: 2rem;">
            <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
        </div>
        <a href="products.php" class="button primary-button">Back to Products</a>
    <?php else: ?>
        <div style="margin-top: 2rem;">
            <!-- Breadcrumb Navigation -->
            <nav style="margin-bottom: 2rem; color: var(--dark-text); font-size: 0.9rem;">
                <a href="index.php" style="color: #66b3ff; text-decoration: none;">Home</a> /
                <a href="products.php" style="color: #66b3ff; text-decoration: none;">Products</a> /
                <span style="color: var(--dark-text);"><?= ucfirst(str_replace('-', ' ', htmlspecialchars($product['category']))) ?></span>
            </nav>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 3rem; margin-bottom: 3rem;">
                <!-- Product Image Section (Left) - Amazon Style -->
                <div style="display: flex; gap: 1rem;">
                    <!-- Thumbnail Gallery (Left side) -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 80px;">
                        <?php
                        // Generate thumbnail gallery: build primary + up to 4 variants
                        $primaryCandidate = $product['image_url'] ?? '';
                        $primaryImage = 'assets/images/default.jpg';
                        if (!empty($primaryCandidate) && file_exists(__DIR__ . '/' . $primaryCandidate)) {
                            $primaryImage = $primaryCandidate;
                        } else {
                            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $product['name']));
                            $possible = __DIR__ . "/assets/images/{$slug}.jpg";
                            if (file_exists($possible)) {
                                $primaryImage = 'assets/images/' . basename($possible);
                            }
                        }

                        $baseName = pathinfo($primaryImage, PATHINFO_FILENAME);

                        // Full gallery: primary then -1..-4 (fallback to primary if missing)
                        $gallery = [];
                        $gallery[] = $primaryImage;
                        for ($i = 1; $i <= 4; $i++) {
                            $candidate = __DIR__ . "/assets/images/{$baseName}-{$i}.jpg";
                            if (file_exists($candidate)) {
                                $gallery[] = 'assets/images/' . basename($candidate);
                            } else {
                                $gallery[] = $primaryImage;
                            }
                        }

                        // We want to display only 4 images starting from the 2nd one (i.e., skip primary in thumbnails)
                        if (count($gallery) >= 2) {
                            $displayGallery = array_slice($gallery, 1, 4);
                        } else {
                            // Fallback: use what's available (up to 4)
                            $displayGallery = array_slice($gallery, 0, 4);
                        }

                        // Determine the initial main image to show on page: first of displayGallery if available, otherwise primary
                        $initialMainImage = $displayGallery[0] ?? $gallery[0];

                        // Render thumbnails (displayGallery)
                        foreach ($displayGallery as $didx => $thumbImage) {
                            $activeStyle = $didx === 0 ? 'border: 2px solid #007bff;' : 'border: 2px solid #ddd;';
                            $alt = $didx + 1; // 1-based index for alt
                        ?>
                            <div class="thumbnail-item" style="background: #e8e8e8; width: 80px; height: 80px; border-radius: 4px; cursor: pointer; <?= $activeStyle ?> display: flex; align-items: center; justify-content: center; overflow: hidden; transition: all 0.2s;" onmouseover="this.style.borderColor='#999'" onmouseout="this.style.borderColor='<?= ($didx === 0) ? '#007bff' : '#ddd' ?>'" onclick="updateMainImage(this)">
                                <img src="<?= htmlspecialchars($thumbImage) ?>" 
                                     alt="thumbnail-<?= $alt ?>"
                                     style="max-width: 90%; max-height: 90%; object-fit: contain;"
                                     onerror="this.src='<?= htmlspecialchars($primaryImage) ?>'">
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Main Product Image (Center) -->
                    <div style="flex: 1;">
                           <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; min-height: 350px; cursor: zoom-in; position: relative; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#efefef'" onmouseout="this.style.backgroundColor='#f8f9fa'" onclick="openImageModal()">
                           <img id="mainProductImage" src="<?= htmlspecialchars($initialMainImage) ?>" 
                               alt="<?= htmlspecialchars($product['name']) ?>"
                               style="max-width: 100%; max-height: 320px; object-fit: contain;">
                        </div>
                        <p style="color: var(--secondary-color); font-size: 0.85rem; margin-top: 1rem; text-align: center;">Click to enlarge</p>
                    </div>
                </div>

                <!-- Image Enlargement Modal -->
                <div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 2rem;">
                    <div style="position: relative; max-width: 90%; max-height: 90%; display: flex; align-items: center; justify-content: center;">
                            <img id="enlargedImage" src="<?= htmlspecialchars($initialMainImage ?? 'assets/images/default.jpg') ?>" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <button onclick="closeImageModal()" style="position: absolute; top: 1rem; right: 1rem; background: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; font-weight: bold; color: #333; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#fff'">✕</button>
                    </div>
                </div>

                <!-- Product Details Section (Right) -->
                <div>
                    <!-- Product Title -->
                    <h1 style="font-size: 2rem; font-weight: 600; color: var(--dark-text); margin: 0 0 1rem 0; line-height: 1.3;">
                        <?= htmlspecialchars($product['name']) ?>
                    </h1>

                    <!-- Rating Section -->
                    <div id="rating-summary" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                        <div class="stars-display" style="color: #ff9900; font-size: 1.2rem; font-weight: bold;">
                            ☆☆☆☆☆
                        </div>
                        <span style="color: #666; font-size: 0.9rem;">
                            <span id="avg-rating">0</span> out of 5 
                        </span>
                        <span style="color: #66b3ff; font-size: 0.9rem;">
                            <a href="#reviews" style="text-decoration: none; color: #66b3ff;">
                                <span id="total-reviews">0</span> customer reviews
                            </a>
                        </span>
                    </div>

                    <!-- Category Badge -->
                    <p style="color: var(--secondary-color); font-size: 0.9rem; margin: 0.5rem 0;">
                        <span style="background: #eee; padding: 0.25rem 0.75rem; border-radius: 4px; color: #333;">
                            <?= ucfirst(str_replace('-', ' ', htmlspecialchars($product['category']))) ?>
                        </span>
                    </p>

                    <!-- Price Section -->
                    <div style="margin: 2rem 0; padding: 1.5rem; background: #f0f2f5; border-radius: 8px; color: var(--dark-text);">
                        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 2.5rem; font-weight: bold; color: #007bff;">
                                $<?= number_format($product['price'], 2) ?>
                            </span>
                            <span style="color: var(--secondary-color); text-decoration: line-through; font-size: 1rem;">
                                $<?= number_format($product['price'] * 1.15, 2) ?>
                            </span>
                        </div>
                        <p style="color: #28a745; font-weight: bold; margin: 0.5rem 0;">
                            Save $<?= number_format($product['price'] * 0.15, 2) ?> (13% off)
                        </p>
                    </div>

                    <!-- Description -->
                    <div style="margin: 2rem 0; padding: 1rem 0; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                        <h3 style="font-size: 1rem; font-weight: 600; color: var(--dark-text); margin-bottom: 1rem;">About this item:</h3>
                        <p style="line-height: 1.7; color: var(--dark-text); font-size: 0.95rem;">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                    </div>

                    <!-- Stock Status -->
                    <div style="margin: 1.5rem 0; padding: 1rem; background: <?= $product['stock'] > 0 ? '#e8f5e9' : '#ffebee' ?>; border-radius: 4px; border-left: 4px solid <?= $product['stock'] > 0 ? '#4caf50' : '#f44336' ?>;">
                        <p style="margin: 0; color: <?= $product['stock'] > 0 ? '#2e7d32' : '#c62828' ?>; font-weight: 600;">
                            <?php if ($product['stock'] > 10): ?>
                                ✓ In Stock (<?= $product['stock'] ?> available)
                            <?php elseif ($product['stock'] > 0): ?>
                                ⚠ Only <?= $product['stock'] ?> left in stock
                            <?php else: ?>
                                ✗ Out of Stock
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Add to Cart Section -->
                    <div style="margin: 2rem 0; padding: 2rem; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="quantity" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Quantity:</label>
                            <select id="quantity" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                <?php for ($i = 1; $i <= min($product['stock'], 10); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="button" class="button primary-button" 
                                onclick="StoreApp.addToCart(<?= $product['id'] ?>)"
                                style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-bottom: 0.75rem; background: #ff9900; border: none; cursor: <?= $product['stock'] <= 0 ? 'not-allowed' : 'pointer' ?>; opacity: <?= $product['stock'] <= 0 ? '0.5' : '1' ?>;"
                                <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            🛒 Add to Cart
                        </button>

                        <button type="button" style="width: 100%; padding: 1rem; font-size: 1rem; background: #fff; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; color: #333;" onclick="StoreApp.addToWishlist(<?= $product['id'] ?>)">
                            ♡ Add to Wishlist
                        </button>
                    </div>

                    <!-- Shipping Info -->
                    <div style="margin: 1.5rem 0; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                        <p style="margin: 0.5rem 0; color: #333;"><strong>📦 Free Shipping</strong> on orders over $50</p>
                        <p style="margin: 0.5rem 0; color: #333;"><strong>🔄 Easy Returns</strong> - 30 day return policy</p>
                        <p style="margin: 0.5rem 0; color: #333;"><strong>✓ Secure Checkout</strong> - SSL Encrypted</p>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <div style="margin-top: 4rem; padding-top: 2rem; border-top: 2px solid #ddd;">
                <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Related Products</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
                    <?php 
                    try {
                        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE category = ? AND id != ? LIMIT 4");
                        $stmt->execute([$product['category'], $product_id]);
                        $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($related as $rel_product):
                    ?>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; transition: transform 0.2s;">
                            <a href="product.php?id=<?= $rel_product['id'] ?>" style="text-decoration: none; color: inherit;">
                                <img src="<?= htmlspecialchars($rel_product['image_url'] ?? 'assets/images/default.jpg') ?>" 
                                     alt="<?= htmlspecialchars($rel_product['name']) ?>"
                                     style="max-height: 120px; object-fit: contain; margin-bottom: 0.5rem;">
                                <p style="font-weight: 600; color: #333; font-size: 0.9rem; margin: 0.5rem 0;">
                                    <?= htmlspecialchars(substr($rel_product['name'], 0, 30)) ?>...
                                </p>
                                <p style="color: #007bff; font-weight: bold; margin: 0;">
                                    $<?= number_format($rel_product['price'], 2) ?>
                                </p>
                            </a>
                        </div>
                    <?php 
                        endforeach;
                    } catch (PDOException $e) {
                        // Silent fail for related products
                    }
                    ?>
                </div>
            </div>

            <a href="products.php" class="button secondary-button" style="margin-top: 2rem;">
                ← Back to Products
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Image modal functions
function openImageModal() {
    const modal = document.getElementById('imageModal');
    const mainImage = document.getElementById('mainProductImage').src;
    document.getElementById('enlargedImage').src = mainImage;
    modal.style.display = 'flex';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside the image
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
    }
    
    // Keyboard support (ESC to close)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
});

// Update main image when clicking thumbnails
function updateMainImage(thumbnail) {
    const mainImage = document.getElementById('mainProductImage');
    const imgSrc = thumbnail.querySelector('img').src;
    mainImage.src = imgSrc;
    
    // Update thumbnail borders
    const allThumbnails = document.querySelectorAll('[onclick="updateMainImage(this)"]');
    allThumbnails.forEach(thumb => {
        thumb.style.borderColor = '#ddd';
    });
    thumbnail.style.borderColor = '#ff9900';
}

// Load and display reviews
function loadReviews() {
    const productId = <?= $product_id ?>;
    fetch(`includes/get_reviews.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update rating summary
                document.getElementById('avg-rating').textContent = data.average_rating;
                document.getElementById('total-reviews').textContent = data.total_reviews;
                
                // Update stars display
                const starsDisplay = document.querySelector('.stars-display');
                const rating = Math.round(data.average_rating);
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += i <= rating ? '★' : '☆';
                }
                starsDisplay.textContent = stars;
                
                // Display reviews
                const reviewsList = document.getElementById('reviews-list');
                if (data.reviews.length === 0) {
                    reviewsList.innerHTML = '<p style="color: #999; text-align: center; padding: 2rem;">No reviews yet. Be the first to review this product!</p>';
                } else {
                    reviewsList.innerHTML = data.reviews.map(review => `
                        <div style="border-bottom: 1px solid #ddd; padding: 1.5rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <strong style="color: var(--dark-text);">${review.user_name}</strong>
                                <span style="color: #ff9900; font-size: 1.1rem;">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</span>
                            </div>
                            <p style="color: #666; font-size: 0.85rem; margin: 0.25rem 0;">${new Date(review.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                            <p style="margin: 0.75rem 0 0 0; line-height: 1.6;">${review.comment}</p>
                        </div>
                    `).join('');
                }
            }
        });
}

// Submit review
function submitReview(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('includes/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            form.reset();
            loadReviews(); // Reload reviews
        } else {
            alert(data.message);
        }
    });
}

// Load reviews when page loads
document.addEventListener('DOMContentLoaded', loadReviews);
</script>

<!-- Reviews Section -->
<div id="reviews" class="container" style="margin: 4rem auto; max-width: 1200px;">
    <h2 style="font-size: 1.8rem; margin-bottom: 2rem; color: var(--dark-text);">Customer Reviews</h2>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Review Form -->
        <div style="background: #ffffff; padding: 2rem; border-radius: 8px; margin-bottom: 3rem; border: 1px solid #ddd;">
            <h3 style="margin-top: 0; color: #000;">Write a Review</h3>
            <form onsubmit="submitReview(event)">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #000;">Rating *</label>
                    <div class="star-rating" style="font-size: 2rem; color: #ff9900; cursor: pointer;">
                        <span onclick="setRating(1)" onmouseover="hoverRating(1)" onmouseout="resetRating()">☆</span>
                        <span onclick="setRating(2)" onmouseover="hoverRating(2)" onmouseout="resetRating()">☆</span>
                        <span onclick="setRating(3)" onmouseover="hoverRating(3)" onmouseout="resetRating()">☆</span>
                        <span onclick="setRating(4)" onmouseover="hoverRating(4)" onmouseout="resetRating()">☆</span>
                        <span onclick="setRating(5)" onmouseover="hoverRating(5)" onmouseout="resetRating()">☆</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" required>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #000;">Your Review *</label>
                    <textarea name="comment" rows="4" required 
                              style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #000;"
                              placeholder="Share your experience with this product..."></textarea>
                </div>
                
                <button type="submit" class="button primary-button">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; margin-bottom: 3rem; border-left: 4px solid #ffc107;">
            <p style="margin: 0;">Please <a href="login.php" style="color: #0066cc;">login</a> to write a review.</p>
        </div>
    <?php endif; ?>
    
    <!-- Reviews List -->
    <div id="reviews-list">
        <p style="text-align: center; color: #999;">Loading reviews...</p>
    </div>
</div>

<script>
let selectedRating = 0;

function setRating(rating) {
    selectedRating = rating;
    document.getElementById('rating-input').value = rating;
    updateStars(rating);
}

function hoverRating(rating) {
    updateStars(rating);
}

function resetRating() {
    updateStars(selectedRating);
}

function updateStars(rating) {
    const stars = document.querySelectorAll('.star-rating span');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.textContent = '★';
            star.style.color = '#ff9900';
        } else {
            star.textContent = '☆';
            star.style.color = '#ddd';
        }
    });
}
</script>

<?php include 'footer.php'; ?>
