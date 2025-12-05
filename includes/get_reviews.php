<?php
// get_reviews.php - Get product reviews
include '../db/db_connect.php';

header('Content-Type: application/json');

$product_id = intval($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

try {
    // Check if reviews table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => true,
            'reviews' => [],
            'average_rating' => 0,
            'total_reviews' => 0
        ]);
        exit();
    }
    
    // Get reviews with user information
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get average rating and count
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total 
        FROM reviews 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'average_rating' => round($stats['avg_rating'] ?? 0, 1),
        'total_reviews' => $stats['total'] ?? 0
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch reviews']);
}
