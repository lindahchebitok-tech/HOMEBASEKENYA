<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $property_id = intval($_POST['property_id']);
        $landlord_id = intval($_POST['landlord_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $message = trim($_POST['message']);
        
      
        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            throw new Exception('All fields are required');
        }
        
     
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
     
        $propertyQuery = "SELECT id FROM properties WHERE id = ? AND landlord_id = ?";
        $propertyStmt = $db->prepare($propertyQuery);
        $propertyStmt->execute([$property_id, $landlord_id]);
        
        if ($propertyStmt->rowCount() === 0) {
            throw new Exception('Invalid property or landlord');
        }
        
     
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
     
        $query = "INSERT INTO contacts (property_id, user_id, name, email, phone, message, status) 
                  VALUES (?, ?, ?, ?, ?, ?, 'unread')";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$property_id, $user_id, $name, $email, $phone, $message]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Your message has been sent to the landlord!']);
        } else {
            throw new Exception('Failed to send message');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>