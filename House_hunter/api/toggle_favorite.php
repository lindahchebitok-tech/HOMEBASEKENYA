<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $property_id = $input['property_id'] ?? null;
    $action = $input['action'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$property_id || !in_array($action, ['add', 'remove'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    try {
        if ($action === 'add') {
           
            $checkQuery = "SELECT id FROM favorites WHERE user_id = ? AND property_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$user_id, $property_id]);

            if ($checkStmt->rowCount() === 0) {
                $query = "INSERT INTO favorites (user_id, property_id) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$user_id, $property_id]);
            }
        } else {
            $query = "DELETE FROM favorites WHERE user_id = ? AND property_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$user_id, $property_id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>