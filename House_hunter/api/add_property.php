<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'landlord') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
    
        $landlord_id = $_SESSION['user_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $type = $_POST['type'];
        $bedrooms = intval($_POST['bedrooms']);
        $bathrooms = intval($_POST['bathrooms']);
        $location = trim($_POST['location']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $status = $_POST['status'] ?? 'available';
        
        
        if (empty($title) || empty($description) || empty($location)) {
            throw new Exception('All required fields must be filled');
        }
        
       
        $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
        $amenities_json = json_encode($amenities);
        
        
        $images = [];
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = '../assets/images/properties/';
            
         
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    
                    $file_type = $_FILES['images']['type'][$key];
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        throw new Exception('Invalid file type: ' . $_FILES['images']['name'][$key]);
                    }
                    
                  
                    if ($_FILES['images']['size'][$key] > 5 * 1024 * 1024) {
                        throw new Exception('File too large: ' . $_FILES['images']['name'][$key]);
                    }
                    
                    
                    $file_extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $filename = uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $images[] = 'assets/images/properties/' . $filename;
                    } else {
                        throw new Exception('Failed to upload: ' . $_FILES['images']['name'][$key]);
                    }
                }
            }
        } else {
            throw new Exception('At least one image is required');
        }
        
        $images_json = json_encode($images);
        
      
        $query = "INSERT INTO properties (landlord_id, title, description, price, type, bedrooms, bathrooms, location, latitude, longitude, amenities, images, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        
        $result = $stmt->execute([
            $landlord_id, 
            $title, 
            $description, 
            $price, 
            $type, 
            $bedrooms, 
            $bathrooms, 
            $location, 
            $latitude, 
            $longitude, 
            $amenities_json, 
            $images_json, 
            $status
        ]);
        
        if ($result) {
            echo json_encode(['success' => 'Property added successfully!']);
        } else {
            throw new Exception('Failed to add property to database: ' . implode(', ', $stmt->errorInfo()));
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>