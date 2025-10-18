<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'landlord') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$message_id = intval($_GET['id']);

$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, p.title as property_title, p.location as property_location 
          FROM contacts c 
          JOIN properties p ON c.property_id = p.id 
          WHERE c.id = ? AND p.landlord_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$message_id, $_SESSION['user_id']]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if ($message) {
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'error' => 'Message not found']);
}
?>