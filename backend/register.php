<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $role = 'student'; // Default role for registration

    if (!$username || !$email || !$password || !$confirm_password) {
        header('Location: ../register.php?error=' . urlencode('All fields are required.'));
        exit;
    }
    if ($password !== $confirm_password) {
        header('Location: ../register.php?error=' . urlencode('Passwords do not match.'));
        exit;
    }

    try {
        // Check if user exists using PDO
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            header('Location: ../register.php?error=' . urlencode('Username or email already exists.'));
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user using PDO
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $hashed_password, $role]);
        
        header('Location: ../register.php?success=' . urlencode('Registration successful! You can now login.'));
        exit;

    } catch (PDOException $e) {
        header('Location: ../register.php?error=' . urlencode('Registration failed. Please try again.'));
        exit;
    }
} else {
    header('Location: ../register.php');
    exit;
}
