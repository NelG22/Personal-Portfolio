<?php
require_once '../includes/config.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';
    $message_id = $_POST['id'] ?? '';
    
    switch ($operation) {
        case 'delete':
            try {
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
                $stmt->execute([$message_id]);
                echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error deleting message']);
            }
            break;
            
        case 'update':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $message = $_POST['message'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE messages SET name = ?, email = ?, message = ? WHERE id = ?");
                $stmt->execute([$name, $email, $message, $message_id]);
                echo json_encode(['success' => true, 'message' => 'Message updated successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error updating message']);
            }
            break;
            
        case 'get':
            try {
                $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
                $stmt->execute([$message_id]);
                $message = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $message]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error fetching message']);
            }
            break;
    }
    exit;
}
?>
