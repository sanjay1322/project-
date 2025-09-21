<?php
// Simple test register script
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Register Script</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Received</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    
    echo "<h3>Form Data:</h3>";
    echo "Name: " . htmlspecialchars($name) . "<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Password: " . (strlen($password) > 0 ? "***" : "empty") . "<br>";
    echo "Confirm Password: " . (strlen($confirm_password) > 0 ? "***" : "empty") . "<br>";
    
    if ($password === $confirm_password) {
        echo "<br>✅ Passwords match!";
    } else {
        echo "<br>❌ Passwords don't match!";
    }
    
    echo "<br><br><a href='../register.html'>Back to Register</a>";
} else {
    echo "<h3>GET Request - This should be POST</h3>";
    echo "<a href='../register.html'>Go to Register Form</a>";
}
?>
