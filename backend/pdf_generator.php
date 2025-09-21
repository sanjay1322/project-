<?php
require_once 'db_connect.php';
require_once 'auth.php';
require_once 'simple_pdf.php';

class PDFGenerator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function generateTicketPDF($ticketId) {
        try {
            // Get ticket details
            $stmt = $this->pdo->prepare('
                SELECT t.*, u.name as student_name, u.email as student_email, d.name as department_name 
                FROM tickets t 
                LEFT JOIN users u ON t.student_id = u.id 
                LEFT JOIN departments d ON t.department_id = d.id 
                WHERE t.id = ?
            ');
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                throw new Exception('Ticket not found');
            }
            
            // Generate PDF content
            $pdfContent = $this->generatePDFContent($ticket);
            
            // Save PDF file
            $filename = 'ticket_' . $ticketId . '_' . date('Y-m-d_H-i-s') . '.txt';
            $uploadDir = '../uploads/tickets/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filepath = $uploadDir . $filename;
            
            if (file_put_contents($filepath, $pdfContent) === false) {
                throw new Exception('Failed to save PDF file');
            }
            
            // Update ticket with PDF path
            $stmt = $this->pdo->prepare('UPDATE tickets SET pdf_path = ? WHERE id = ?');
            $stmt->execute(['uploads/tickets/' . $filename, $ticketId]);
            
            return 'uploads/tickets/' . $filename;
            
        } catch (Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function generatePDFContent($ticket) {
        // Create a simple text-based PDF content for now
        // In production, you would use a library like TCPDF or FPDF
        $content = "SERVICE REQUEST TICKET\n";
        $content .= "College Service Tracker System\n";
        $content .= str_repeat("=", 50) . "\n\n";
        
        $content .= "Ticket ID: #" . $ticket['id'] . "\n";
        $content .= "Student Name: " . $ticket['student_name'] . "\n";
        $content .= "Student Email: " . $ticket['student_email'] . "\n";
        $content .= "Request Title: " . $ticket['title'] . "\n";
        $content .= "Category: " . $ticket['category'] . "\n";
        $content .= "Status: " . $ticket['status'] . "\n";
        $content .= "Department: " . ($ticket['department_name'] ?: 'Not assigned') . "\n";
        $content .= "Created Date: " . date('F j, Y \a\t g:i A', strtotime($ticket['created_at'])) . "\n\n";
        
        $content .= "DESCRIPTION:\n";
        $content .= str_repeat("-", 20) . "\n";
        $content .= $ticket['description'] . "\n\n";
        
        $content .= str_repeat("=", 50) . "\n";
        $content .= "Generated on: " . date('F j, Y \a\t g:i A') . "\n";
        $content .= "This ticket was generated automatically by the College Service Tracker System.\n";
        
        return $content;
    }
    
    public function downloadPDF($ticketId) {
        try {
            // Get ticket details
            $stmt = $this->pdo->prepare('
                SELECT t.*, u.username as student_name, u.email as student_email, d.name as department_name
                FROM tickets t 
                LEFT JOIN users u ON t.student_id = u.id 
                LEFT JOIN departments d ON t.department_id = d.id
                WHERE t.id = ?
            ');
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                throw new Exception('Ticket not found');
            }
            
            // Check if user has permission to download this PDF
            $user = getCurrentUser();
            $canDownload = false;
            
            if ($user['role'] === 'admin') {
                $canDownload = true;
            } elseif ($user['role'] === 'student' && $ticket['student_id'] == $user['id']) {
                $canDownload = true;
            } elseif ($user['role'] === 'department' && $ticket['department_id'] == $user['department_id']) {
                $canDownload = true;
            }
            
            if (!$canDownload) {
                throw new Exception('Access denied');
            }
            
            // Generate formatted PDF using SimplePDF
            $pdf = new SimplePDF('Service Request Ticket #' . $ticketId);
            
            $pdf->addHeader('SERVICE REQUEST TICKET');
            $pdf->addText('College Service Tracker System');
            $pdf->addText('');
            
            $pdf->addText('TICKET INFORMATION:');
            $pdf->addLine('-', 30);
            $pdf->addText('Ticket ID: #' . $ticket['id']);
            $pdf->addText('Student Name: ' . ($ticket['student_name'] ?: 'Unknown'));
            $pdf->addText('Student Email: ' . ($ticket['student_email'] ?: 'Unknown'));
            $pdf->addText('Request Title: ' . $ticket['title']);
            $pdf->addText('Category: ' . $ticket['category']);
            $pdf->addText('Status: ' . $ticket['status']);
            $pdf->addText('Department: ' . ($ticket['department_name'] ?: 'Not assigned'));
            $pdf->addText('Created Date: ' . date('F j, Y \a\t g:i A', strtotime($ticket['created_at'])));
            $pdf->addText('');
            
            $pdf->addText('DESCRIPTION:');
            $pdf->addLine('-', 30);
            $pdf->addText($ticket['description']);
            
            // Generate proper PDF using basic PDF structure
            $pdfContent = $this->generateBasicPDF($ticket, $ticketId);
            
            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="ticket_' . $ticketId . '.pdf"');
            header('Content-Length: ' . strlen($pdfContent));
            
            echo $pdfContent;
            
        } catch (Exception $e) {
            http_response_code(404);
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }
    
    private function generateBasicPDF($ticket, $ticketId) {
        // Create text content
        $textContent = "SERVICE REQUEST TICKET\n\n";
        $textContent .= "College Service Tracker System\n";
        $textContent .= str_repeat("=", 50) . "\n\n";
        
        $textContent .= "TICKET INFORMATION:\n";
        $textContent .= str_repeat("-", 30) . "\n";
        $textContent .= "Ticket ID: #" . $ticket['id'] . "\n";
        $textContent .= "Student Name: " . ($ticket['student_name'] ?: 'Unknown') . "\n";
        $textContent .= "Student Email: " . ($ticket['student_email'] ?: 'Unknown') . "\n";
        $textContent .= "Request Title: " . $ticket['title'] . "\n";
        $textContent .= "Category: " . $ticket['category'] . "\n";
        $textContent .= "Status: " . $ticket['status'] . "\n";
        $textContent .= "Department: " . ($ticket['department_name'] ?: 'Not assigned') . "\n";
        $textContent .= "Created Date: " . date('F j, Y g:i A', strtotime($ticket['created_at'])) . "\n\n";
        
        $textContent .= "DESCRIPTION:\n";
        $textContent .= str_repeat("-", 30) . "\n";
        $textContent .= $ticket['description'] . "\n\n";
        
        $textContent .= str_repeat("=", 50) . "\n";
        $textContent .= "Generated on: " . date('F j, Y g:i A') . "\n";
        $textContent .= "College Service Tracker System\n";
        
        // Create basic PDF structure
        $pdfContent = "%PDF-1.4\n";
        $pdfContent .= "1 0 obj\n";
        $pdfContent .= "<< /Type /Catalog /Pages 2 0 R >>\n";
        $pdfContent .= "endobj\n";
        
        $pdfContent .= "2 0 obj\n";
        $pdfContent .= "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\n";
        $pdfContent .= "endobj\n";
        
        $pdfContent .= "3 0 obj\n";
        $pdfContent .= "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\n";
        $pdfContent .= "endobj\n";
        
        $streamContent = "BT\n";
        $streamContent .= "/F1 12 Tf\n";
        $streamContent .= "50 750 Td\n";
        
        $lines = explode("\n", $textContent);
        foreach ($lines as $line) {
            $streamContent .= "(" . addslashes($line) . ") Tj\n";
            $streamContent .= "0 -15 Td\n";
        }
        $streamContent .= "ET\n";
        
        $pdfContent .= "4 0 obj\n";
        $pdfContent .= "<< /Length " . strlen($streamContent) . " >>\n";
        $pdfContent .= "stream\n";
        $pdfContent .= $streamContent;
        $pdfContent .= "endstream\n";
        $pdfContent .= "endobj\n";
        
        $pdfContent .= "5 0 obj\n";
        $pdfContent .= "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\n";
        $pdfContent .= "endobj\n";
        
        $pdfContent .= "xref\n";
        $pdfContent .= "0 6\n";
        $pdfContent .= "0000000000 65535 f \n";
        $pdfContent .= "0000000010 00000 n \n";
        $pdfContent .= "0000000053 00000 n \n";
        $pdfContent .= "0000000100 00000 n \n";
        $pdfContent .= "0000000200 00000 n \n";
        $pdfContent .= "0000000300 00000 n \n";
        
        $pdfContent .= "trailer\n";
        $pdfContent .= "<< /Size 6 /Root 1 0 R >>\n";
        $pdfContent .= "startxref\n";
        $pdfContent .= "400\n";
        $pdfContent .= "%%EOF\n";
        
        return $pdfContent;
    }
}
?>
