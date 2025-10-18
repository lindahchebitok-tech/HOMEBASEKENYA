<?php

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {

    $query = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        user_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $db->exec($query);
    echo "Contacts table created successfully!<br>";
    
    $checkColumns = $db->query("DESCRIBE contacts")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('name', $checkColumns)) {
        $db->exec("ALTER TABLE contacts ADD COLUMN name VARCHAR(255) NOT NULL AFTER user_id");
        echo "Added 'name' column to contacts table<br>";
    }
    
    if (!in_array('email', $checkColumns)) {
        $db->exec("ALTER TABLE contacts ADD COLUMN email VARCHAR(255) NOT NULL AFTER name");
        echo "Added 'email' column to contacts table<br>";
    }
    
    if (!in_array('phone', $checkColumns)) {
        $db->exec("ALTER TABLE contacts ADD COLUMN phone VARCHAR(20) NOT NULL AFTER email");
        echo "Added 'phone' column to contacts table<br>";
    }
    
    if (!in_array('message', $checkColumns)) {
        $db->exec("ALTER TABLE contacts ADD COLUMN message TEXT NOT NULL AFTER phone");
        echo "Added 'message' column to contacts table<br>";
    }
    
    if (!in_array('status', $checkColumns)) {
        $db->exec("ALTER TABLE contacts ADD COLUMN status ENUM('unread', 'read', 'replied') DEFAULT 'unread' AFTER message");
        echo "Added 'status' column to contacts table<br>";
    }
    
    echo "Database setup completed successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>