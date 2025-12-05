<?php
// footer.php: Reusable HTML footer and closing tags
?>
    </div>
    <footer>
        <div class="container">
            <p>&copy; <?= date("Y") ?> Online Computer Store. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php 
// Close the database connection gracefully at the end of the script execution
if (isset($pdo)) {
    $pdo = null; 
}
?>