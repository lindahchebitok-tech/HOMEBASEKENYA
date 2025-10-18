<?php
echo "<h2>Setting up House Hunter Kenya Database</h2>";

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $sqlFile = 'database_schema.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠ Note: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
    echo "<p><strong>Sample data includes:</strong></p>";
    echo "<ul>";
    echo "<li>3 Users (including Owen as landlord)</li>";
    echo "<li>4 Properties</li>";
    echo "<li>3 Sample Messages</li>";
    echo "<li>2 Favorite Properties</li>";
    echo "</ul>";
    
    echo "<p><strong>Login credentials for testing:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Landlord:</strong> smartiphone361@gmail.com / password</li>";
    echo "<li><strong>Tenant:</strong> john.tenant@email.com / password</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error setting up database:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>