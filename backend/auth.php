<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Check if user can access specific role pages
function requireRole($allowedRoles) {
    if (!isLoggedIn()) {
        header('Location: ../login.html');
        exit();
    }
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        header('Location: ../index.html');
        exit();
    }
}

// Login user
function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['department_id'] = $user['department_id'] ?? null;
}

// Logout user
function logoutUser() {
    session_destroy();
    header('Location: ../index.html');
    exit();
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'username' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'department_id' => $_SESSION['department_id']
    ];
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
