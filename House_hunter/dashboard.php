<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if($user_type == 'landlord') {
    $query = "SELECT * FROM properties WHERE landlord_id = ? ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if($user_type == 'tenant') {
    $query = "SELECT p.* FROM properties p 
              JOIN favorites f ON p.id = f.property_id 
              WHERE f.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
    
    <?php if($user_type == 'landlord'): ?>

        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Your Properties</h4>
                    <a href="add_property.php" class="btn btn-primary">Add New Property</a>
                </div>
                
                <div class="row">
                    <?php foreach($properties as $property): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo $property['images'] ? json_decode($property['images'])[0] : 'assets/images/default-property.jpg'; ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $property['title']; ?></h5>
                                <p class="card-text">KSh <?php echo number_format($property['price']); ?> / month</p>
                                <p class="card-text"><small class="text-muted"><?php echo $property['location']; ?></small></p>
                                <span class="badge bg-<?php echo $property['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($property['status']); ?>
                                </span>
                                <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($properties)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            You haven't listed any properties yet. <a href="add_property.php">Add your first property</a>.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-envelope"></i> Recent Messages</h5>
            </div>
            <div class="card-body">
                <?php

                $messageQuery = "SELECT c.*, p.title as property_title 
                                FROM contacts c 
                                JOIN properties p ON c.property_id = p.id 
                                WHERE p.landlord_id = ? 
                                ORDER BY c.created_at DESC 
                                LIMIT 5";
                $messageStmt = $db->prepare($messageQuery);
                $messageStmt->execute([$user_id]);
                $recent_messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if(empty($recent_messages)): ?>
                    <p class="text-muted">No messages yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach($recent_messages as $message): ?>
                        <a href="landlord_messages.php" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($message['property_title']); ?></h6>
                                <small class="text-<?php echo $message['status'] == 'unread' ? 'danger' : 'muted'; ?>">
                                    <?php echo $message['status'] == 'unread' ? 'New' : 'Read'; ?>
                                </small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($message['name']); ?> - <?php echo substr($message['message'], 0, 50); ?>...</p>
                            <small class="text-muted"><?php echo date('M j, g:i A', strtotime($message['created_at'])); ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="landlord_messages.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-inbox"></i> View All Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
        
    <?php else: ?>

        <div class="row">
    <div class="col-md-12">
        <h4>Your Favorite Properties</h4>
        
        <div class="row">
            <?php foreach($favorites as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo $property['images'] ? json_decode($property['images'])[0] : 'assets/images/default-property.jpg'; ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $property['title']; ?></h5>
                        <p class="card-text">KSh <?php echo number_format($property['price']); ?> / month</p>
                        <p class="card-text"><small class="text-muted"><?php echo $property['location']; ?></small></p>
                        <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($favorites)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    You haven't added any properties to favorites yet. <a href="index.php">Browse properties</a>.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
</div>

<style>
    
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .container {
        flex: 1;
    }
</style>

<?php include 'includes/footer.php'; ?>