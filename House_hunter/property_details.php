<?php
session_start();
require_once 'config/database.php';

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$property_id) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, u.name as landlord_name, u.phone as landlord_phone, u.email as landlord_email 
          FROM properties p 
          JOIN users u ON p.landlord_id = u.id 
          WHERE p.id = ? AND p.status = 'available'";

$stmt = $db->prepare($query);
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header("Location: index.php");
    exit();
}

$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $favorite_query = "SELECT id FROM favorites WHERE user_id = ? AND property_id = ?";
    $favorite_stmt = $db->prepare($favorite_query);
    $favorite_stmt->execute([$_SESSION['user_id'], $property_id]);
    $is_favorite = $favorite_stmt->rowCount() > 0;
}

$images = json_decode($property['images'] ?? '[]', true);
$amenities = json_decode($property['amenities'] ?? '[]', true);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img id="mainImage" src="<?php echo !empty($images) ? $images[0] : 'assets/images/default-property.jpg'; ?>" 
                             class="img-fluid rounded main-image" alt="<?php echo htmlspecialchars($property['title']); ?>"
                             style="max-height: 500px; width: 100%; object-fit: cover;">
                    </div>
                    
                    <?php if (!empty($images) && count($images) > 1): ?>
                    <div class="thumbnails">
                        <div class="row">
                            <?php foreach ($images as $index => $image): ?>
                            <div class="col-3 col-md-2 mb-2">
                                <img src="<?php echo $image; ?>" 
                                     class="img-thumbnail thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;"
                                     onclick="changeMainImage('<?php echo $image; ?>', this)"
                                     alt="Property image <?php echo $index + 1; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Property Description</h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
            </div>

            <?php if (!empty($amenities)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Amenities & Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $amenity_icons = [
                            'wifi' => 'wifi',
                            'parking' => 'car',
                            'security' => 'shield-alt',
                            'water' => 'tint',
                            'electricity' => 'bolt',
                            'garden' => 'tree',
                            'balcony' => 'building',
                            'furnished' => 'couch',
                            'ac' => 'wind',
                            'heating' => 'fire',
                            'laundry' => 'tshirt',
                            'pool' => 'swimming-pool'
                        ];
                        
                        foreach ($amenities as $amenity): 
                            $icon = $amenity_icons[$amenity] ?? 'check';
                        ?>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-<?php echo $icon; ?> text-primary me-2"></i>
                            <?php echo ucfirst($amenity); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Location</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <i class="fas fa-location-dot text-danger me-2"></i>
                        <strong>Address:</strong> <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                    <div id="propertyMap" style="height: 300px; border-radius: 8px;"></div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Location map not available for this property.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tag"></i> Property Details</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary mb-3">KSh <?php echo number_format($property['price']); ?> <small class="text-muted">/ month</small></h4>
                    
                    <div class="property-details mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-home text-primary me-2"></i> Type:</span>
                            <span class="badge bg-primary"><?php echo ucfirst($property['type']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-bed text-primary me-2"></i> Bedrooms:</span>
                            <span class="badge bg-success"><?php echo $property['bedrooms']; ?> Bed<?php echo $property['bedrooms'] != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-bath text-primary me-2"></i> Bathrooms:</span>
                            <span class="badge bg-info"><?php echo $property['bathrooms']; ?> Bath<?php echo $property['bathrooms'] != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-calendar text-primary me-2"></i> Listed:</span>
                            <span><?php echo date('M j, Y', strtotime($property['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="fas fa-envelope me-2"></i> Contact Landlord
                        </button>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-outline-danger favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                                data-property-id="<?php echo $property['id']; ?>">
                            <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart me-2"></i>
                            <?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                        </button>
                        <?php else: ?>
                        <a href="pages/login.php" class="btn btn-outline-secondary">
                            <i class="fas fa-heart me-2"></i> Login to Add Favorites
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Landlord Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="mt-2 mb-1"><?php echo htmlspecialchars($property['landlord_name']); ?></h5>
                        <p class="text-muted">Property Owner</p>
                    </div>
                    
                    <div class="landlord-contact">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span><?php echo htmlspecialchars($property['landlord_phone']); ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <span><?php echo htmlspecialchars($property['landlord_email']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-share-alt"></i> Share This Property</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook me-2"></i> Share on Facebook
                        </button>
                        <button class="btn btn-outline-info" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter me-2"></i> Share on Twitter
                        </button>
                        <button class="btn btn-outline-success" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i> Share on WhatsApp
                        </button>
                        <button class="btn btn-outline-secondary" onclick="copyPropertyLink()">
                            <i class="fas fa-link me-2"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2"></i> Contact Landlord
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                    <input type="hidden" name="landlord_id" value="<?php echo $property['landlord_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-control" required 
                               value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>"
                               <?php echo isset($_SESSION['user_name']) ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Your Email *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>"
                               <?php echo isset($_SESSION['user_email']) ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Your Phone *</label>
                        <input type="tel" name="phone" class="form-control" required
                               value="<?php echo isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="I'm interested in this property...">Hi, I'm interested in your property "<?php echo htmlspecialchars($property['title']); ?>" located at <?php echo htmlspecialchars($property['location']); ?>. Please contact me for more details.</textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

<?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('propertyMap').setView([<?php echo $property['latitude']; ?>, <?php echo $property['longitude']; ?>], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    L.marker([<?php echo $property['latitude']; ?>, <?php echo $property['longitude']; ?>])
        .addTo(map)
        .bindPopup('<?php echo addslashes($property['title']); ?>')
        .openPopup();
});
<?php endif; ?>

function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

function shareOnTwitter() {
    const text = encodeURIComponent('Check out this property: <?php echo addslashes($property['title']); ?>');
    const url = encodeURIComponent(window.location.href);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
}

function shareOnWhatsApp() {
    const text = encodeURIComponent('Check out this property: <?php echo addslashes($property['title']); ?> - ' + window.location.href);
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function copyPropertyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        if (window.HouseHunterApp) {
            window.HouseHunterApp.showNotification('Property link copied to clipboard!', 'success');
        } else {
            alert('Property link copied to clipboard!');
        }
    });
}

document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    
    fetch('api/contact_landlord.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.HouseHunterApp) {
                window.HouseHunterApp.showNotification('Message sent successfully!', 'success');
            } else {
                alert('Message sent successfully!');
            }
            $('#contactModal').modal('hide');
            this.reset();
        } else {
            if (window.HouseHunterApp) {
                window.HouseHunterApp.showNotification('Error: ' + data.error, 'error');
            } else {
                alert('Error: ' + data.error);
            }
        }
    })
   
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.querySelector('.favorite-btn');
    if (favoriteBtn && window.HouseHunterApp) {
        favoriteBtn.addEventListener('click', function() {
            window.HouseHunterApp.handleFavoriteClick(this);
        });
    }
});
</script>

<style>
.property-card {
    transition: transform 0.2s;
}
.property-card:hover {
    transform: translateY(-2px);
}
.thumbnail {
    transition: all 0.2s;
}
.thumbnail:hover {
    border-color: #007bff;
    transform: scale(1.05);
}
.thumbnail.active {
    border-color: #007bff;
    border-width: 2px;
}
.main-image {
    transition: opacity 0.3s;
}
</style>

<?php include 'includes/footer.php'; ?>