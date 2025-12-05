<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$host = 'localhost';
$db   = 'computer_store'; 
$user = 'root';           
$pass = '';               
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     error_log("Database Connection Failed: " . $e->getMessage());
     die("<h1>Database Connection Error</h1><p>The online store is currently experiencing technical difficulties. Please try again later.</p>");
}

?>