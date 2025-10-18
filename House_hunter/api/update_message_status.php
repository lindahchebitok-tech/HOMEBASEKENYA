<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'landlord') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$message_id = $input['message_id'] ?? null;
$status = $input['status'] ?? null;

if (!$message_id || !in_array($status, ['read', 'unread', 'replied'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

$database = new Database();
$db = $database->getConnection();


$verifyQuery = "SELECT c.id FROM contacts c 
                JOIN properties p ON c.property_id = p.id 
                WHERE c.id = ? AND p.landlord_id = ?";
$verifyStmt = $db->prepare($verifyQuery);
$verifyStmt->execute([$message_id, $_SESSION['user_id']]);

if ($verifyStmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'error' => 'Message not found']);
    exit();
}


$updateQuery = "UPDATE contacts SET status = ? WHERE id = ?";
$updateStmt = $db->prepare($updateQuery);
$result = $updateStmt->execute([$status, $message_id]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
}
?>