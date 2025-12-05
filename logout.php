<?php
// logout.php - User logout
session_start();
session_destroy();

// Redirect to home page
header("Location: index.php?logout=1");
exit();
?>
