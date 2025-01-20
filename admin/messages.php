<?php
require_once '../includes/config.php';

// Get messages from database
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#">
                <i class="fas fa-envelope me-2"></i>
                Messages Dashboard
            </a>
            <button id="theme-toggle" aria-label="Toggle Dark Mode">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <div class="container">
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stats-card">
                <h5>Total Messages</h5>
                <h2><?php echo count($messages); ?></h2>
            </div>
            <div class="stats-card">
                <h5>New Messages</h5>
                <h2><?php echo count(array_filter($messages, function($msg) {
                    return strtotime($msg['created_at']) > strtotime('-24 hours');
                })); ?></h2>
            </div>
            <div class="stats-card">
                <h5>This Week</h5>
                <h2><?php echo count(array_filter($messages, function($msg) {
                    return strtotime($msg['created_at']) > strtotime('-1 week');
                })); ?></h2>
            </div>
        </div>

        <!-- Messages List -->
        <?php foreach ($messages as $message): ?>
        <div class="message-card" id="message-<?php echo $message['id']; ?>">
            <div class="message-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($message['name']); ?></h5>
                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-decoration-none">
                            <i class="fas fa-envelope me-1"></i>
                            <?php echo htmlspecialchars($message['email']); ?>
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-3">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?>
                        </span>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="editMessage(<?php echo $message['id']; ?>)">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button class="btn btn-danger" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="message-content">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($messages)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
            <p class="text-muted">No messages yet</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Edit Message Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="editMessage" name="message" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateMessage()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggling
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        });

        document.getElementById('theme-toggle').addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            const icon = document.querySelector('#theme-toggle i');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Modal and CRUD operations
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        async function editMessage(id) {
            try {
                const response = await fetch('message_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `operation=get&id=${id}`
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('editId').value = id;
                    document.getElementById('editName').value = result.data.name;
                    document.getElementById('editEmail').value = result.data.email;
                    document.getElementById('editMessage').value = result.data.message;
                    editModal.show();
                } else {
                    alert('Error fetching message details');
                }
            } catch (error) {
                alert('Error fetching message details');
            }
        }

        async function updateMessage() {
            const form = document.getElementById('editForm');
            const formData = new FormData(form);
            formData.append('operation', 'update');
            
            try {
                const response = await fetch('message_operations.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error updating message');
                }
            } catch (error) {
                alert('Error updating message');
            }
        }

        async function deleteMessage(id) {
            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }
            
            try {
                const response = await fetch('message_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `operation=delete&id=${id}`
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById(`message-${id}`).remove();
                    // Update stats cards after deletion
                    const totalMessages = document.querySelectorAll('.message-card').length;
                    document.querySelector('.stats-card h2').textContent = totalMessages;
                } else {
                    alert('Error deleting message');
                }
            } catch (error) {
                alert('Error deleting message');
            }
        }
    </script>
</body>
</html>
