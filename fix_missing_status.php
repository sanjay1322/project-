<?php
require_once 'backend/db_connect.php';

echo "<h1>Fixing Missing Ticket Statuses...</h1>";

try {
    // Find tickets with NULL or empty status
    $stmt = $pdo->prepare("SELECT id FROM tickets WHERE status IS NULL OR status = ''");
    $stmt->execute();
    $ticketsToFix = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($ticketsToFix)) {
        echo "<p style='color: green;'>No tickets with missing status found. Everything looks good!</p>";
    } else {
        $count = count($ticketsToFix);
        echo "<p>Found {$count} ticket(s) with a missing status. Updating them now...</p>";

        // Update status to 'Submitted'
        $updateStmt = $pdo->prepare("UPDATE tickets SET status = 'Submitted' WHERE id = ?");
        
        $updatedCount = 0;
        foreach ($ticketsToFix as $ticketId) {
            if ($updateStmt->execute([$ticketId])) {
                $updatedCount++;
                echo "<p>- Fixed ticket #{$ticketId}</p>";
            }
        }

        echo "<p style='color: green; font-weight: bold;'>Successfully updated {$updatedCount} ticket(s)!</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>You can now safely delete this file and refresh your student dashboard.</p>";
?>
