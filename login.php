<?php
if (session_status() === PHP_SESSION_NONE) session_start();
 
// Already logged in — go home
if (!empty($_SESSION['admin_logged_in'])) {
    $home = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
    header('Location: ' . $home);
    exit;
}
 
define('ADMIN_USERNAME', 'sdm');
define('ADMIN_PASSWORD', '1234');
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === ADMIN_USERNAME && $p === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $u;
        $home = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
        header('Location: ' . $home);
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DairyFlow — Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #FDF6EC; --warm-bg: #F5EDD8; --brown: #3D2B1F;
            --amber: #C8762B; --amber-lt: #E8A855; --green: #3A6B35;
            --red: #C0392B; --text: #2C1E14; --text-muted: #7A6555;
            --border: #DDD0B8;
            --font-display: 'DM Serif Display', Georgia, serif;
            --font-body: 'DM Sans', sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, var(--brown) 0%, #6B4226 55%, #3A6B35 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .wrap { width: 100%; max-width: 420px; }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand .icon { font-size: 3.5rem; display: block; margin-bottom: 0.5rem; }
        .brand h1 { font-family: var(--font-display); font-size: 2.2rem; color: var(--amber-lt); }
        .brand p { color: rgba(255,255,255,0.6); font-size: 0.88rem; margin-top: 0.3rem; }
        .card { background: var(--cream); border-radius: 16px; padding: 2.5rem; box-shadow: 0 8px 40px rgba(61,43,31,0.18); }
        .card h2 { font-family: var(--font-display); font-size: 1.4rem; color: var(--brown); margin-bottom: 0.3rem; }
        .subtitle { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.8rem; }
        .badge { display: inline-block; background: rgba(200,118,43,0.15); color: var(--amber); border: 1px solid var(--amber); padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 1rem; }
        .fg { display: flex; flex-direction: column; gap: 5px; margin-bottom: 1.1rem; }
        .fg label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .fg input { padding: 11px 14px; border: 1.5px solid var(--border); border-radius: 8px; font-family: var(--font-body); font-size: 0.95rem; background: white; color: var(--text); transition: border-color 0.2s; }
        .fg input:focus { outline: none; border-color: var(--amber); }
        .btn { width: 100%; padding: 13px; background: var(--brown); color: white; border: none; border-radius: 8px; font-family: var(--font-body); font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; transition: background 0.2s; }
        .btn:hover { background: var(--amber); }
        .error { background: #FDECEA; color: #9B2335; border-left: 4px solid var(--red); padding: 10px 14px; border-radius: 6px; font-size: 0.88rem; font-weight: 500; margin-bottom: 1.2rem; }
        .hint { margin-top: 1.5rem; padding: 1rem; background: var(--warm-bg); border-radius: 8px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.7; border: 1px dashed var(--border); }
        .hint strong { color: var(--brown); }
        .back { display: block; text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.6); font-size: 0.88rem; text-decoration: none; }
        .back:hover { color: var(--amber-lt); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <span class="icon">🐄</span>
        <h1>DairyFlow</h1>
        <p>Dairy Management System</p>
    </div>
    <div class="card">
        <span class="badge">🔐 ADMIN ACCESS</span>
        <h2>Welcome back</h2>
        <p class="subtitle">Sign in to manage farmers, employees, production and billing.</p>
        <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="fg">
                <label>Username</label>
                <input type="text" name="username" required autofocus placeholder="admin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn">🔓 Sign In as Admin</button>
        </form>
        
    </div>
    <a href="index.php" class="back">← Back to homepage (view-only)</a>
</div>
</body>
</html>