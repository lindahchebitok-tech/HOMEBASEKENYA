<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();


$location = isset($_GET['location']) ? $_GET['location'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$featured = isset($_GET['featured']) ? $_GET['featured'] : false;

$query = "SELECT p.*, u.name as landlord_name, u.phone as landlord_phone 
          FROM properties p 
          JOIN users u ON p.landlord_id = u.id 
          WHERE p.status = 'available'";

$params = [];

if(!empty($location)) {
    $query .= " AND p.location LIKE ?";
    $params[] = "%$location%";
}

if(!empty($type)) {
    $query .= " AND p.type = ?";
    $params[] = $type;
}

if(!empty($max_price)) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}

if($featured) {
    $query .= " ORDER BY p.created_at DESC LIMIT 6";
} else {
    $query .= " ORDER BY p.created_at DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);

$properties = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $properties[] = $row;
}

echo json_encode($properties);
?>