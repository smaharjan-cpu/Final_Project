<?php
// submit_review.php - Submit product review
session_start();
include '../db/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

// Validation
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5 stars']);
    exit();
}

if (empty($comment) || strlen($comment) < 10) {
    echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters']);
    exit();
}

try {
    // Check if reviews table exists, create if not
    $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }
    
    // Check if user already reviewed this product
    $check = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    
    if ($check->rowCount() > 0) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() 
                               WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$rating, $comment, $user_id, $product_id]);
        echo json_encode(['success' => true, 'message' => 'Review updated successfully!']);
    } else {
        // Insert new review
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
}
