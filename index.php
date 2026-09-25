<?php
$page_title = 'Home';
require 'includes/db.php';
require 'includes/header.php';

// Quick Stats
$farmers_count  = $conn->query("SELECT COUNT(*) FROM farmers WHERE status='active'")->fetch_row()[0];
$employees_count = $conn->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetch_row()[0];
$products_count = $conn->query("SELECT COUNT(*) FROM products WHERE status='available'")->fetch_row()[0];
$today_prod     = $conn->query("SELECT COALESCE(SUM(total_qty),0) FROM daily_production WHERE production_date=CURDATE()")->fetch_row()[0];
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">🥛 Dairy Management System</div>
        <h1>From Farm to Table,<br><em>Seamlessly Managed</em></h1>
        <p>DairyFlow helps you track farmers, employees, daily milk production, dairy products and generate accurate bills — all in one place.</p>
        <div class="hero-btns">
            <a href="/dairy_management/pages/production.php" class="btn btn-primary">Today's Production</a>
            <a href="/dairy_management/pages/bills.php" class="btn btn-outline">Generate Bill</a>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat-item">
        <span class="stat-value"><?= $farmers_count ?></span>
        <span class="stat-label">Active Farmers</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $employees_count ?></span>
        <span class="stat-label">Employees</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $products_count ?></span>
        <span class="stat-label">Products Available</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= number_format($today_prod, 1) ?>L</span>
        <span class="stat-label">Today's Production</span>
    </div>
</div>

<!-- SERVICES -->
<section class="section">
    <div class="section-header">
        <h2>Our Services</h2>
        <p>Everything you need to run your dairy cooperative efficiently</p>
        <div class="section-line"></div>
    </div>
    <div class="services-grid">
        <a href="/dairy_management/pages/farmers.php" class="service-card">
            <div class="service-icon">👨‍🌾</div>
            <h3>Farmer Management</h3>
            <p>Register and manage farmer profiles including bank details, village info and milk supply history.</p>
            <span class="service-link">Manage Farmers →</span>
        </a>
        <a href="/dairy_management/pages/employees.php" class="service-card">
            <div class="service-icon">👷</div>
            <h3>Employee Records</h3>
            <p>Track employee details, roles, salaries and status across your dairy operation.</p>
            <span class="service-link">View Employees →</span>
        </a>
        <a href="/dairy_management/pages/production.php" class="service-card">
            <div class="service-icon">🥛</div>
            <h3>Daily Production Log</h3>
            <p>Record morning and evening milk collection per farmer with fat %, SNF % and rate calculation.</p>
            <span class="service-link">Log Production →</span>
        </a>
        <a href="/dairy_management/pages/products.php" class="service-card">
            <div class="service-icon">🧀</div>
            <h3>Dairy Products</h3>
            <p>Manage your product catalogue — milk, butter, ghee, paneer, curd and more with stock levels.</p>
            <span class="service-link">View Products →</span>
        </a>
        <a href="/dairy_management/pages/bills.php" class="service-card">
            <div class="service-icon">🧾</div>
            <h3>Bill Generator</h3>
            <p>Generate monthly payment bills for any farmer by Farmer ID with full itemised breakdown.</p>
            <span class="service-link">Generate Bill →</span>
        </a>
    </div>
</section>

<!-- ABOUT -->
<section class="section section-alt">
    <div class="about-grid">
        <div class="about-text">
            <h2>About DairyFlow</h2>
            <p>DairyFlow is a comprehensive dairy cooperative management system built for modern dairy businesses in India. It streamlines the entire workflow from farmer milk collection to final payment processing.</p>
            <p>Designed to run locally via XAMPP, it's fast, secure, and works without internet access — ideal for cooperative offices in rural areas.</p>
            <ul class="about-features">
                <li>Complete farmer registration and profile management</li>
                <li>Morning & evening milk collection tracking</li>
                <li>Fat % and SNF % quality recording</li>
                <li>Auto-calculated amounts and monthly bills</li>
                <li>Product stock and pricing management</li>
                <li>Printable bill statements for farmers</li>
                <li>Works offline via XAMPP/MySQL</li>
            </ul>
        </div>
        <div class="about-visual">🐄</div>
    </div>
</section>

<!-- QUICK ACTIONS -->
<section class="section">
    <div class="section-header">
        <h2>Quick Actions</h2>
        <p>Frequently used tasks — get there in one click</p>
        <div class="section-line"></div>
    </div>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1rem;">
        <a href="/dairy_management/pages/farmers.php?action=add" class="btn btn-primary">➕ Add Farmer</a>
        <a href="/dairy_management/pages/employees.php?action=add" class="btn btn-success">➕ Add Employee</a>
        <a href="/dairy_management/pages/production.php?action=add" class="btn btn-outline" style="border-color:var(--brown);color:var(--brown);">🥛 Log Today's Milk</a>
        <a href="/dairy_management/pages/bills.php" class="btn btn-secondary">🧾 Generate Bill</a>
        <a href="/dairy_management/pages/products.php?action=add" class="btn btn-outline" style="border-color:var(--green);color:var(--green);">🧀 Add Product</a>
    </div>
</section>

<?php
// Footer needs relative path adjusted for root level
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
                <li><a href="/dairy_management/index.php">Home</a></li>
                <li><a href="/dairy_management/pages/farmers.php">Farmers</a></li>
                <li><a href="/dairy_management/pages/employees.php">Employees</a></li>
                <li><a href="/dairy_management/pages/production.php">Daily Production</a></li>
                <li><a href="/dairy_management/pages/products.php">Products</a></li>
                <li><a href="/dairy_management/pages/bills.php">Bill Generator</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
            <p>📍 Dairy Colony, Karnataka, India</p>
            <p>📞 +91 98765 43210</p>
            <p>📧 info@dairyflow.in</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> DairyFlow Management System. All rights reserved.</p>
    </div>
</footer>
</body>
</html>