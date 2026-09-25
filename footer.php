<?php
$current_page = basename($_SERVER['PHP_SELF']);
$pages = ['farmers.php','employees.php','production.php','products.php','bills.php'];
$base = in_array($current_page, $pages) ? '../' : '';
?>
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="brand-icon">🐄</span>
            <strong>DairyFlow</strong>
            <p>Modern dairy cooperative management made simple.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= $base ?>index.php">Home</a></li>
                <li><a href="<?= $base ?>pages/farmers.php">Farmers</a></li>
                <li><a href="<?= $base ?>pages/employees.php">Employees</a></li>
                <li><a href="<?= $base ?>pages/production.php">Daily Production</a></li>
                <li><a href="<?= $base ?>pages/products.php">Products</a></li>
                <li><a href="<?= $base ?>pages/bills.php">Bill Generator</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
            <p>📍 Dairy colony,Ujire, Karnataka, India</p>
            <p>📞 +91 7975344328</p>
            <p>📧 info@dairyflow.in</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> DairyFlow Management System. All rights reserved.</p>
    </div>
</footer>
</body>
</html>