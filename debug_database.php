<?php
require_once 'backend/db_connect.php';

echo "<h2>Database Analysis</h2>";

// Check database connection
try {
    echo "<h3>1. Database Connection: ✅ Connected</h3>";
    
    // Check users table structure
    echo "<h3>2. Users Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check all users
    echo "<h3>3. All Users in Database:</h3>";
    $stmt = $pdo->query('SELECT id, username, email, role, department_id, password FROM users ORDER BY role, id');
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: red;'>❌ NO USERS FOUND! Database is empty.</p>";
        echo "<a href='create_admin.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none;'>Create Users Now</a>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Dept ID</th><th>Password Hash</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . ($user['department_id'] ?? 'NULL') . "</td>";
            echo "<td>" . substr($user['password'], 0, 20) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test password verification
        echo "<h3>4. Password Verification Test:</h3>";
        foreach ($users as $user) {
            if ($user['role'] === 'admin') {
                $testPassword = 'admin123';
                $isValid = password_verify($testPassword, $user['password']);
                echo "<p>Admin ({$user['email']}) password 'admin123': " . ($isValid ? "✅ Valid" : "❌ Invalid") . "</p>";
            }
            if ($user['role'] === 'department') {
                $testPassword = 'library123';
                $isValid = password_verify($testPassword, $user['password']);
                echo "<p>Department ({$user['email']}) password 'library123': " . ($isValid ? "✅ Valid" : "❌ Invalid") . "</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<h3>5. Quick Actions:</h3>
<a href="create_admin.php" style="background: #28a745; color: white; padding: 10px; margin: 5px; text-decoration: none; display: inline-block;">Create/Reset Users</a>
<a href="login.php" style="background: #007bff; color: white; padding: 10px; margin: 5px; text-decoration: none; display: inline-block;">Go to Login</a>
