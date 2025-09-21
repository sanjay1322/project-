<?php
// Test register functionality
echo "<h2>Testing Register Functionality</h2>";

// Test 1: Check if register.php exists and is accessible
echo "<h3>1. Testing register.php file</h3>";
if (file_exists('backend/register.php')) {
    echo "✅ register.php file exists<br>";
} else {
    echo "❌ register.php file not found<br>";
}

// Test 2: Check database connection
echo "<h3>2. Testing database connection</h3>";
try {
    require_once 'backend/db_connect.php';
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check if we can access register.php via HTTP
echo "<h3>3. Testing HTTP access to register.php</h3>";
$url = 'http://localhost/service_tracker/backend/register.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => 'name=Test User&email=test@example.com&password=test123&confirm-password=test123'
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    echo "✅ register.php is accessible via HTTP<br>";
    echo "Response: " . htmlspecialchars($result) . "<br>";
} else {
    echo "❌ register.php is not accessible via HTTP<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
}

// Test 4: Check form action
echo "<h3>4. Testing form action</h3>";
$html = file_get_contents('register.html');
if (strpos($html, 'action="backend/register.php"') !== false) {
    echo "✅ Form action is correct<br>";
} else {
    echo "❌ Form action is incorrect<br>";
}

echo "<br><a href='register.html'>Go to Register Page</a>";
?>
