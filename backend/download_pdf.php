<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
require_once 'auth.php';


// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user = getCurrentUser(); // This correctly gets the user data as an array

$ticketId = (int)($_GET['id'] ?? 0);

if (!$ticketId) {
    http_response_code(400);
    echo 'Invalid ticket ID';
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT t.*, u.username as student_name, u.email as student_email, d.name as department_name FROM tickets t JOIN users u ON t.student_id = u.id LEFT JOIN departments d ON t.department_id = d.id WHERE t.id = ?');
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch();

    if (!$ticket || ($user['role'] === 'student' && $ticket['student_id'] !== $user['id'])) {
        die('Ticket not found or access denied.');
    }

    // Generate PDF using FPDF
    require_once __DIR__ . '/vender/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    $pdf->Cell(0, 10, 'SERVICE REQUEST TICKET', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'College Service Tracker System', 0, 1, 'C');
    $pdf->Cell(0, 10, '==================================================', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'TICKET INFORMATION:');
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Ticket ID:', 0, 0);
    $pdf->Cell(0, 7, '#' . $ticket['id'], 0, 1);

    $pdf->Cell(40, 7, 'Student Name:', 0, 0);
    $pdf->Cell(0, 7, $ticket['student_name'], 0, 1);

    $pdf->Cell(40, 7, 'Student Email:', 0, 0);
    $pdf->Cell(0, 7, $ticket['student_email'], 0, 1);

    $pdf->Cell(40, 7, 'Request Title:', 0, 0);
    $pdf->Cell(0, 7, $ticket['title'], 0, 1);

    $pdf->Cell(40, 7, 'Category:', 0, 0);
    $pdf->Cell(0, 7, $ticket['category'], 0, 1);

    $pdf->Cell(40, 7, 'Status:', 0, 0);
    $pdf->Cell(0, 7, htmlspecialchars($ticket['status'] ?? 'N/A'), 0, 1);

    $pdf->Cell(40, 7, 'Department:', 0, 0);
    $pdf->Cell(0, 7, htmlspecialchars($ticket['department_name'] ?? 'Not Assigned'), 0, 1);

    $pdf->Cell(40, 7, 'Created Date:', 0, 0);
    $pdf->Cell(0, 7, $ticket['created_at'], 0, 1);

    $pdf->Output('D', 'ticket_' . $ticketId . '.pdf');
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>
