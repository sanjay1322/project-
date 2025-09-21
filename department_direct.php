<?php
session_start();

// Direct department access for testing - bypasses database login
$_SESSION['user_id'] = 2;
$_SESSION['user_name'] = 'Library Head';
$_SESSION['user_email'] = 'library@college.edu';
$_SESSION['user_role'] = 'department';
$_SESSION['department_id'] = 2; // Library department

// Redirect to department dashboard
header('Location: department_dashboard.php');
exit;
?>
