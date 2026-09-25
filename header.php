<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DairyFlow — <?php echo $page_title ?? 'Dairy Management System'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/dairy_management/css/style.css">
    <!-- Theme init: runs before body paints to avoid flash -->
    <script>
        if (localStorage.getItem('dairyflow_theme') === 'dark') {
            document.documentElement.classList.add('dark-init');
        }
    </script>
    <style>
        /* Applied instantly via documentElement before body exists */
        html.dark-init body { background: #111318 !important; }
    </style>
</head>
<body>

<script>
    // Transfer class from <html> to <body> now that body exists
    if (document.documentElement.classList.contains('dark-init')) {
        document.body.classList.add('dark');
    }
</script>

<nav class="navbar">
    <div class="nav-brand">
        <span class="brand-icon">🐄</span>
        <span class="brand-name">DairyFlow</span>
    </div>
    <ul class="nav-links">
        <li><a href="/dairy_management/index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Home</a></li>
        <li><a href="/dairy_management/pages/farmers.php" class="<?= $current_page=='farmers.php'?'active':'' ?>">Farmers</a></li>
        <li><a href="/dairy_management/pages/employees.php" class="<?= $current_page=='employees.php'?'active':'' ?>">Employees</a></li>
        <li><a href="/dairy_management/pages/production.php" class="<?= $current_page=='production.php'?'active':'' ?>">Production</a></li>
        <li><a href="/dairy_management/pages/products.php" class="<?= $current_page=='products.php'?'active':'' ?>">Products</a></li>
        <li><a href="/dairy_management/pages/bills.php" class="<?= $current_page=='bills.php'?'active':'' ?>">Bills</a></li>
    </ul>
    <div class="nav-auth">
        <?php if (is_admin()): ?>
            <span class="admin-badge">🔐 <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
            <a href="/dairy_management/logout.php" class="btn-nav-logout">Logout</a>
        <?php else: ?>
            <a href="/dairy_management/login.php" class="btn-nav-login">🔓 Admin Login</a>
        <?php endif; ?>
        <button class="theme-toggle" id="themeToggle" title="Switch to Dark Mode">🌙</button>
    </div>
    <div class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</div>
</nav>

<script>
(function () {
    var btn  = document.getElementById('themeToggle');
    var body = document.body;

    function updateBtn() {
        var isDark = body.classList.contains('dark');
        btn.textContent = isDark ? '☀️' : '🌙';
        btn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }

    // Set correct icon on load
    updateBtn();

    btn.addEventListener('click', function () {
        // Add transition class for smooth switch, remove after animation
        body.classList.add('theme-transitioning');
        setTimeout(function () { body.classList.remove('theme-transitioning'); }, 350);

        body.classList.toggle('dark');
        document.documentElement.classList.toggle('dark-init', body.classList.contains('dark'));
        localStorage.setItem('dairyflow_theme', body.classList.contains('dark') ? 'dark' : 'light');
        updateBtn();
    });
})();
</script>