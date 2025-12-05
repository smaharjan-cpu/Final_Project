<?php
/**
 * Database Setup Script
 * Run this once to create all tables and populate sample data
 */

// Direct PDO connection for setup
$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    // First, connect without selecting a database
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS computer_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created successfully<br>";

    // Select the database
    $pdo->exec("USE computer_store");

    // Create tables
    $sql = <<<SQL
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Products table
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        category VARCHAR(50) NOT NULL,
        stock INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Cart table
    CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Orders table
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Order items table
    CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price_at_purchase DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    SQL;

    // Execute multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✓ All tables created successfully<br>";

    // Insert sample data
    $pdo->exec("USE computer_store");
    
    // Check if data already exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert users
        $pdo->exec("INSERT INTO users (name, email, password, is_admin) VALUES ('Admin User', 'admin@computerstore.com', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 1)");
        $pdo->exec("INSERT INTO users (name, email, password, is_admin) VALUES ('John Doe', 'john@example.com', '" . password_hash('user123', PASSWORD_BCRYPT) . "', 0)");
        echo "✓ Sample users created<br>";

        // Insert products
        $products = [
            ['ASUS VivoBook 15 E1504FA', 'Intel Core Ultra 7 135U Processor | 16GB RAM | 512GB SSD | 15.6" FHD Display | Windows 11 | Matte Black', 649.99, 'assets/images/asus-vivobook.jpg', 'laptops', 25],
            ['Dell Inspiron 15', 'High-performance laptop with Intel i7 processor | 16GB RAM | 512GB SSD | Windows 11', 799.99, 'assets/images/dell-inspiron.jpg', 'laptops', 10],
            ['HP Pavilion Desktop', 'Powerful desktop computer for gaming and work | Intel i5 | 16GB RAM | 256GB SSD', 1199.99, 'assets/images/hp-pavilion.jpg', 'desktops', 8],
            ['NVIDIA RTX 4070', 'Top-tier graphics card for gaming and professional work | 12GB GDDR6 Memory', 599.99, 'assets/images/rtx-4070.jpg', 'graphic-cards', 15],
            ['16GB DDR4 RAM', 'High-speed memory for optimal performance | 3200MHz | Corsair Vengeance', 89.99, 'assets/images/ddr4-ram.jpg', 'memories', 20],
            ['1TB SSD Samsung 870 EVO', 'Fast solid-state drive for storage | SATA III | 560MB/s Read Speed', 109.99, 'assets/images/samsung-ssd.jpg', 'storage', 12],
            ['Logitech Mechanical Keyboard RGB', 'Premium mechanical keyboard with customizable RGB lighting | Mechanical Switches | Programmable Keys', 159.99, 'assets/images/logitech-keyboard.jpg', 'accessories', 25],
            ['Corsair RM850x Power Supply', '850W 80+ Gold Certified Modular Power Supply | 10 Year Warranty', 149.99, 'assets/images/corsair-psu.jpg', 'power-supplies', 7],
        ];

        $insert_product = $pdo->prepare("INSERT INTO products (name, description, price, image_url, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $insert_product->execute($product);
        }
        echo "✓ Sample products created<br>";
    } else {
        echo "✓ Database already has data<br>";
    }

    echo "<br><strong style='color: green; font-size: 18px;'>✓ Database setup completed successfully!</strong><br>";
    echo "<p>You can now access the application at: <a href='../index.php'>../index.php</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Setup Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP.</p>";
}
?>
