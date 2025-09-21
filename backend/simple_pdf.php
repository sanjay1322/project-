<?php
class SimplePDF {
    private $content = '';
    private $title = '';
    
    public function __construct($title = 'Document') {
        $this->title = $title;
    }
    
    public function addText($text) {
        $this->content .= $text . "\n";
    }
    
    public function addLine($char = '-', $length = 50) {
        $this->content .= str_repeat($char, $length) . "\n";
    }
    
    public function addHeader($text) {
        $this->content .= "\n" . strtoupper($text) . "\n";
        $this->addLine('=', strlen($text));
    }
    
    public function generateHTML() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 16px; }
        .ticket-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .field { margin: 10px 0; }
        .label { font-weight: bold; color: #333; display: inline-block; width: 150px; }
        .value { color: #555; }
        .description { background: white; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-top: 10px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
        .status { padding: 5px 10px; border-radius: 15px; color: white; font-size: 12px; }
        .status-submitted { background: #ffc107; color: #000; }
        .status-assigned { background: #17a2b8; }
        .status-approved { background: #28a745; }
        .status-rejected { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SERVICE TRACKER</div>
        <div class="subtitle">College Service Request System</div>
    </div>
    
    <div class="ticket-info">
        ' . nl2br(htmlspecialchars($this->content)) . '
    </div>
    
    <div class="footer">
        Generated on ' . date('F j, Y \a\t g:i A') . '<br>
        This document was generated automatically by the College Service Tracker System.
    </div>
</body>
</html>';
        return $html;
    }
    
    public function output($filename = 'document.pdf') {
        $html = $this->generateHTML();
        
        // Set headers for PDF-like display
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $html;
    }
    
    public function save($filepath) {
        $html = $this->generateHTML();
        return file_put_contents($filepath, $html);
    }
}
?>
