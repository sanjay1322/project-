<?php
require_once 'backend/db_connect.php';
require_once 'backend/auth.php';

// Require student role
requireRole(['student']);

$user = getCurrentUser();
$error = '';
$success = '';

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');

    if (empty($title) || empty($category) || empty($description)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Step 1: Insert the ticket with a 'Submitted' status
            $stmt = $pdo->prepare('INSERT INTO tickets (student_id, title, category, description, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $title, $category, $description, 'Submitted']);
            $ticketId = $pdo->lastInsertId();

            // Step 2: Create a dummy file path to make the download button appear
            // The actual PDF is generated on-the-fly by download_pdf.php
            $dummyFilePath = 'uploads/ticket_' . $ticketId . '.pdf';

            // Step 3: Update the ticket with the dummy file path
            $stmt = $pdo->prepare('UPDATE tickets SET pdf_path = ? WHERE id = ?');
            $stmt->execute([$dummyFilePath, $ticketId]);

            // Step 4: Log the creation in ticket_history
            $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, comment) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$ticketId, $user['id'], null, 'Submitted', 'Ticket submitted by student']);

            $success = 'Request submitted successfully!';
        } catch (PDOException $e) {
            $error = 'Error submitting request: ' . $e->getMessage();
        }
    }
}

// Get user's tickets
try {
    $stmt = $pdo->prepare('
        SELECT 
            t.*,
            d.name as department_name
        FROM tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        WHERE t.student_id = ?
        ORDER BY t.created_at DESC
    ');
    $stmt->execute([$user['id']]);
    $tickets = $stmt->fetchAll();

} catch (PDOException $e) {
    $tickets = [];
    $error = 'Error loading tickets.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Service Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .status-submitted { @apply bg-yellow-400 text-black; }
        .status-assigned { @apply bg-blue-500 text-white; }
        .status-under-review { @apply bg-purple-500 text-white; }
        .status-approved { @apply bg-green-500 text-white; }
        .status-rejected { @apply bg-red-500 text-white; }
        
        .back-home {
            position: fixed;
            bottom: 15px;
            left: 15px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            color: #3b82f6;
            transition: 0.3s;
        }
        .back-home:hover {
            color: #f97316;
            text-decoration: underline;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-white text-xl font-bold">
                        <i class="bi bi-person-workspace mr-2"></i>Service Tracker - Student
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white/90">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="backend/logout.php" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                        <i class="bi bi-box-arrow-right mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-400 mr-3"></i>
                    <p class="text-red-700"><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-400 mr-3"></i>
                    <p class="text-green-700"><?php echo $success; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Submit New Request -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <i class="bi bi-plus-circle mr-2"></i>Submit New Request
                        </h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Request Title</label>
                                <input type="text" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                       id="title" name="title" required
                                       placeholder="Enter your request title">
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                        id="category" name="category" required>
                                    <option value="">-- Choose a Category --</option>
                                    <option value="ID Card Re-issue">ID Card Re-issue</option>
                                    <option value="Fee Payment Receipt">Fee Payment Receipt</option>
                                    <option value="Library Book Renewal">Library Book Renewal</option>
                                    <option value="Character Certificate">Character Certificate</option>
                                    <option value="Transfer Certificate">College Leaving / Transfer Certificate</option>
                                    <option value="Bonafide Certificate">Bonafide Certificate</option>
                                    <option value="Course Completion">Course Completion Certificate</option>
                                    <option value="Hostel Room">Hostel Room Allocation / Change</option>
                                    <option value="Mess Change">Mess Refund / Change of Mess</option>
                                    <option value="Maintenance">Maintenance Issues</option>
                                    <option value="Wi-Fi">Wi-Fi / Internet Access</option>
                                    <option value="Password Reset">Password Reset</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                          id="description" name="description" rows="4" required 
                                          placeholder="Please provide detailed information about your request..."></textarea>
                            </div>
                            
                            <button type="submit" name="submit_ticket" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 flex items-center justify-center">
                                <i class="bi bi-send mr-2"></i>Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Your Requests -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <i class="bi bi-list-ul mr-2"></i>Your Requests
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($tickets)): ?>
                            <div class="text-center py-12">
                                <i class="bi bi-inbox text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg">No requests found.</p>
                                <p class="text-gray-400">Submit a new request to get started!</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PDF</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    #<?php echo $ticket['id']; ?>
                                                </td>
                                                <td class="px-4 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($ticket['title']); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($ticket['category']); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                                        <?php echo $ticket['status']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $ticket['department_name'] ?: 'Not assigned'; ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                    <?php if ($ticket['pdf_path']): ?>
                                                        <a href="backend/download_pdf.php?id=<?php echo $ticket['id']; ?>" 
                                                           class="inline-flex items-center px-3 py-1 border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors duration-150" 
                                                           target="_blank">
                                                            <i class="bi bi-download mr-1"></i>Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-gray-400">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="index.html" class="back-home">← Back to Home</a>
</body>
</html>
