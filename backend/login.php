<?php
require 'db_connect.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$email || !$password || !$role) {
        header('Location: ../login.php?error=' . urlencode('All fields are required.'));
        exit;
    }

    try {
        // Find user with PDO
        $stmt = $pdo->prepare('SELECT id, username, email, password, role, department_id FROM users WHERE email = ? AND role = ?');
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: ../login.php?error=' . urlencode('Invalid email, password, or role.'));
            exit;
        }

        // Login user and create session
        loginUser([
            'id' => $user['id'],
            'name' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'department_id' => $user['department_id']
        ]);

        // Redirect to appropriate dashboard
        switch ($user['role']) {
            case 'student':
                header('Location: ../student_dashboard.php');
                break;
            case 'admin':
                header('Location: ../admin_dashboard.php');
                break;
            case 'department':
                header('Location: ../department_dashboard.php');
                break;
            default:
                header('Location: ../index.html');
        }
        exit;

    } catch (PDOException $e) {
        header('Location: ../login.php?error=' . urlencode('Database error. Please try again.'));
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
