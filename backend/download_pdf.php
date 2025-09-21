<?php
require_once 'db_connect.php';
require_once 'auth.php';
require_once 'pdf_generator.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.html');
    exit;
}

$ticketId = (int)($_GET['id'] ?? 0);

if (!$ticketId) {
    http_response_code(400);
    echo 'Invalid ticket ID';
    exit;
}

try {
    $pdfGenerator = new PDFGenerator($pdo);
    $pdfGenerator->downloadPDF($ticketId);
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>
