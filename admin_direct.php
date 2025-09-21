<?php
session_start();

// Direct admin access for testing - bypasses database login
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin';
$_SESSION['user_email'] = 'admin@college.edu';
$_SESSION['user_role'] = 'admin';
$_SESSION['department_id'] = null;

// Redirect to admin dashboard
header('Location: admin_dashboard.php');
exit;
?>
