<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dairy_management');
 
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;color:red;padding:20px;'>
        <h2>Database Connection Failed</h2>
        <p>" . $conn->connect_error . "</p>
        <p>Please ensure XAMPP is running and the database is set up using <strong>setup.sql</strong>.</p>
    </div>");
}
 
$conn->set_charset("utf8");
 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Helper: check if current user is admin
function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?>