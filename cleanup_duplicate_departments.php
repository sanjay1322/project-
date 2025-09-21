<?php
require_once 'backend/db_connect.php';

echo "<h2>Cleaning up duplicate departments...</h2>";

try {
    // First, let's see what we have
    $stmt = $pdo->query('SELECT id, name FROM departments ORDER BY name, id');
    $allDepts = $stmt->fetchAll();
    
    echo "<h3>Current departments in database:</h3>";
    foreach ($allDepts as $dept) {
        echo "ID: {$dept['id']} - Name: {$dept['name']}<br>";
    }
    
    // Find duplicates and keep only the first occurrence of each name
    $stmt = $pdo->query('
        SELECT name, MIN(id) as keep_id, COUNT(*) as count 
        FROM departments 
        GROUP BY name 
        HAVING COUNT(*) > 1
    ');
    $duplicates = $stmt->fetchAll();
    
    if (empty($duplicates)) {
        echo "<br>✅ No duplicates found!<br>";
    } else {
        echo "<br><h3>Found duplicates:</h3>";
        foreach ($duplicates as $dup) {
            echo "Department '{$dup['name']}' appears {$dup['count']} times, keeping ID {$dup['keep_id']}<br>";
        }
        
        // Remove duplicates
        foreach ($duplicates as $dup) {
            $stmt = $pdo->prepare('DELETE FROM departments WHERE name = ? AND id != ?');
            $stmt->execute([$dup['name'], $dup['keep_id']]);
            echo "Removed duplicates of '{$dup['name']}'<br>";
        }
    }
    
    // Show final result
    echo "<br><h3>Final departments list:</h3>";
    $stmt = $pdo->query('SELECT id, name FROM departments ORDER BY name');
    $finalDepts = $stmt->fetchAll();
    
    foreach ($finalDepts as $dept) {
        echo "ID: {$dept['id']} - Name: {$dept['name']}<br>";
    }
    
    echo "<br><strong>✅ Cleanup completed!</strong><br>";
    echo "<br><a href='admin_dashboard.php'>Go to Admin Dashboard</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
