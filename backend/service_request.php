<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $title = $_POST['service'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!$student_id || !$department_id || !$title || !$description) {
        echo 'All fields are required.';
        exit;
    }
    $stmt = $conn->prepare('INSERT INTO service_requests (student_id, department_id, title, description) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiss', $student_id, $department_id, $title, $description);
    if ($stmt->execute()) {
        echo 'Service request submitted successfully!';
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request.';
}
