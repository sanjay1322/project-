<?php
/**
 * Remove All Enhanced Files
 * This script removes all enhanced files created today
 */

echo "<h2>Removing All Enhanced Files</h2>";

// List of enhanced files to remove
$enhancedFiles = [
    'migrate_to_enhanced.php',
    'admin_dashboard_enhanced.php', 
    'debug_login.php',
    'check_users.php',
    'quick_debug.php',
    'test_login.php',
    'restore_original.php',
    'ENHANCED_FEATURES.md',
    'backend/enhanced_schema.sql'
];

echo "<h3>Removing Enhanced Files:</h3>";

foreach ($enhancedFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✓ Removed: $file<br>";
        } else {
            echo "❌ Failed to remove: $file<br>";
        }
    } else {
        echo "⚠ Not found: $file<br>";
    }
}

echo "<br><h3>Testing Login System:</h3>";

try {
    require_once 'backend/db_connect.php';
    
    // Test admin login
    $stmt = $pdo->prepare('SELECT id, email, role FROM users WHERE email = ? AND role = ?');
    $stmt->execute(['admin@college.edu', 'admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Admin user exists<br>";
    } else {
        echo "❌ Admin user missing<br>";
    }
    
    // Test password
    $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
    $stmt->execute(['admin@college.edu']);
    $user = $stmt->fetch();
    
    if ($user && password_verify('admin123', $user['password'])) {
        echo "✓ Admin password works<br>";
    } else {
        echo "❌ Admin password issue<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br>=== Cleanup Complete ===<br>";
echo "All enhanced files removed. Try logging in now:<br>";
echo "• <a href='login.php'>Login Page</a><br>";
echo "• Email: admin@college.edu<br>";
echo "• Password: admin123<br>";
echo "• Role: Administrator<br>";
?>
