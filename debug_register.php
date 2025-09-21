<?php
// Debug register functionality
echo "<h2>Debug Register Functionality</h2>";

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Received</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test the register.php file
    echo "<h3>Testing register.php</h3>";
    try {
        require_once 'backend/register.php';
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<h3>GET Request - Show Form</h3>";
    ?>
    <form method="POST">
        <div>
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="Test User" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="test@example.com" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="test123" required>
        </div>
        <div>
            <label for="confirm-password">Confirm Password:</label>
            <input type="password" id="confirm-password" name="confirm-password" value="test123" required>
        </div>
        <div>
            <button type="submit">Test Register</button>
        </div>
    </form>
    <?php
}

echo "<br><a href='register.html'>Go to Original Register Page</a>";
?>
