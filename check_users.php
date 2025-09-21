<?php
require_once 'backend/db_connect.php';

echo "<h2>Current Users in Database</h2>";

try {
    $stmt = $pdo->query("SELECT id, username, email, role, department_id FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Dept ID</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['department_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><h3>Test Login for Each User:</h3>";
        
        // Test each user's password
        foreach ($users as $user) {
            echo "<strong>User: " . htmlspecialchars($user['email']) . " (Role: " . htmlspecialchars($user['role']) . ")</strong><br>";
            
            // Try common passwords
            $testPasswords = ['admin123', 'library123', 'it123', 'hostel123', 'vinay123', '123456', 'password'];
            $passwordFound = false;
            
            foreach ($testPasswords as $testPass) {
                $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
                $stmt->execute([$user['email']]);
                $userRecord = $stmt->fetch();
                
                if ($userRecord && password_verify($testPass, $userRecord['password'])) {
                    echo "&nbsp;&nbsp;✓ Password '$testPass' works<br>";
                    $passwordFound = true;
                    break;
                } 
            }
            
            if (!$passwordFound) {
                echo "&nbsp;&nbsp;❌ None of the common passwords work<br>";
            }
            echo "<br>";
        }
        
        echo "<h3>Expected Default Users:</h3>";
        echo "<ul>";
        echo "<li>admin@college.edu (admin role) - Password: admin123</li>";
        echo "<li>library@college.edu (department role) - Password: library123</li>";
        echo "<li>it@college.edu (department role) - Password: it123</li>";
        echo "<li>hostel@college.edu (department role) - Password: hostel123</li>";
        echo "</ul>";
        
    } else {
        echo "❌ No users found in database<br>";
        echo "<p><strong>Your database is empty!</strong></p>";
        echo "<p>Run <a href='setup_database.php'>setup_database.php</a> to create default users</p>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
