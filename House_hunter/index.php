<?php
session_start();
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$where_conditions = ["p.status = 'available'"];
$params = [];

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_conditions[] = "p.location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_conditions[] = "p.type = ?";
    $params[] = $_GET['type'];
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
}

$where_clause = implode(" AND ", $where_conditions);

$is_search_page = isset($_GET['location']) || isset($_GET['type']) || isset($_GET['max_price']);

if ($is_search_page) {
    $query = "SELECT p.*, u.name as landlord_name 
              FROM properties p 
              JOIN users u ON p.landlord_id = u.id 
              WHERE $where_clause 
              ORDER BY p.created_at DESC 
              LIMIT 12";
} else {

    $query = "SELECT p.*, u.name as landlord_name 
              FROM properties p 
              JOIN users u ON p.landlord_id = u.id 
              WHERE p.status = 'available' 
              ORDER BY p.created_at DESC 
              LIMIT 6";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $favorite_query = "SELECT property_id FROM favorites WHERE user_id = ?";
    $favorite_stmt = $db->prepare($favorite_query);
    $favorite_stmt->execute([$_SESSION['user_id']]);
    $user_favorites = $favorite_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<nav
class="hero-section text-white py-5  style="background: rgba(0, 0, 0, 0.7) ">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Find Your Dream Home in Kenya</h1>
                <p class="lead">Discover the perfect rental property across major cities in Kenya</p>
                <div class="search-box mt-4">
                    <form action="index.php" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="location" class="form-control" placeholder="Enter location..." 
                                   value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                                <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                                <option value="studio" <?php echo (isset($_GET['type']) && $_GET['type'] == 'studio') ? 'selected' : ''; ?>>Studio</option>
                                <option value="bedsitter" <?php echo (isset($_GET['type']) && $_GET['type'] == 'bedsitter') ? 'selected' : ''; ?>>Bedsitter</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="max_price" class="form-control" placeholder="Max Price" 
                                   value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-warning w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<section class="py-5">
    <div class="container">
        <?php if ($is_search_page): ?>
            <h2 class="text-center mb-5">
                <?php 
                echo count($properties) . ' Property' . (count($properties) != 1 ? 's' : '') . ' Found';
                if (isset($_GET['location']) && !empty($_GET['location'])) {
                    echo ' in ' . htmlspecialchars($_GET['location']);
                }
                ?>
            </h2>
        <?php else: ?>
            <h2 class="text-center mb-5">Featured Properties</h2>
        <?php endif; ?>

        <div class="row">
            <?php if (empty($properties)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php if ($is_search_page): ?>
                            No properties found matching your criteria. Try adjusting your search filters.
                        <?php else: ?>
                            No properties available at the moment. Please check back later.
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): 
  
                    $images = json_decode($property['images'] ?? '[]', true);
                    $main_image = !empty($images) ? $images[0] : 'assets/images/default-property.jpg';

                    $is_favorite = in_array($property['id'], $user_favorites);
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card property-card h-100">
                            <img src="<?php echo htmlspecialchars($main_image); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <?php echo htmlspecialchars($property['location']); ?>
                                </p>
                                <p class="card-text fw-bold text-primary">
                                    KSh <?php echo number_format($property['price']); ?> / month
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?php echo ucfirst($property['type']); ?></span>
                                    <span class="badge bg-success"><?php echo $property['bedrooms']; ?> Bed<?php echo $property['bedrooms'] != 1 ? 's' : ''; ?></span>
                                    <span class="badge bg-info"><?php echo $property['bathrooms']; ?> Bath<?php echo $property['bathrooms'] != 1 ? 's' : ''; ?></span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-outline-danger btn-sm favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                                        data-property-id="<?php echo $property['id']; ?>">
                                    <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                                <?php else: ?>
                                <a href="pages/login.php" class="btn btn-outline-secondary btn-sm" title="Login to add favorites">
                                    <i class="far fa-heart"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if ($is_search_page && count($properties) >= 12): ?>
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary load-more-btn" data-page="1">
                    <i class="fas fa-spinner d-none"></i> Load More Properties
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How HomeBase Kenya Works</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-search text-white" style="font-size: 2rem;"></i>
                </div>
                <h4>Search</h4>
                <p class="text-muted">Find properties by location, price, and type that match your preferences.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-eye text-white" style="font-size: 2rem;"></i>
                </div>
                <h4>View Details</h4>
                <p class="text-muted">See complete property information, photos, and contact the landlord directly.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-home text-white" style="font-size: 2rem;"></i>
                </div>
                <h4>Move In</h4>
                <p class="text-muted">Contact landlords, schedule viewings, and find your perfect home.</p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            const isActive = this.classList.contains('active');
            const icon = this.querySelector('i');
            
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId,
                    action: isActive ? 'remove' : 'add'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {

                    if (isActive) {
                        this.classList.remove('active');
                        icon.classList.replace('fas', 'far');
                    } else {
                        this.classList.add('active');
                        icon.classList.replace('far', 'fas');
                    }
                    
                    if (window.HouseHunterApp) {
                        window.HouseHunterApp.showNotification(
                            isActive ? 'Removed from favorites' : 'Added to favorites', 
                            'success'
                        );
                    } else {
                        alert(isActive ? 'Removed from favorites' : 'Added to favorites');
                    }
                } else {
                    throw new Error(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.HouseHunterApp) {
                    window.HouseHunterApp.showNotification('Error updating favorites', 'error');
                } else {
                    alert('Error updating favorites');
                }
            })
            .finally(() => {
                this.innerHTML = originalHtml;
                this.disabled = false;
            });
        });
    });
    
    const loadMoreBtn = document.querySelector('.load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.dataset.page) || 1;
            const spinner = this.querySelector('.fa-spinner');
            const originalText = this.innerHTML;
            
            spinner.classList.remove('d-none');
            this.disabled = true;
            
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', currentPage + 1);
            
            fetch(`api/load_more_properties.php?${urlParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.properties && data.properties.length > 0) {
                    const propertiesContainer = document.querySelector('.row');
                    data.properties.forEach(property => {
                        const propertyHTML = createPropertyCard(property);
                        propertiesContainer.insertAdjacentHTML('beforeend', propertyHTML);
                    });

                    this.dataset.page = currentPage + 1;
                    
                    if (!data.hasMore) {
                        this.style.display = 'none';
                    }
                } else {
                    this.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading more properties:', error);
                if (window.HouseHunterApp) {
                    window.HouseHunterApp.showNotification('Error loading more properties', 'error');
                }
            })
            .finally(() => {
                spinner.classList.add('d-none');
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
});

function createPropertyCard(property) {
    const images = property.images ? JSON.parse(property.images) : [];
    const mainImage = images.length > 0 ? images[0] : 'assets/images/default-property.jpg';
    const isFavorite = property.is_favorite || false;
    
    return `
        <div class="col-md-4 mb-4">
            <div class="card property-card h-100">
                <img src="${mainImage}" 
                     class="card-img-top" alt="${escapeHtml(property.title)}" 
                     style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title">${escapeHtml(property.title)}</h5>
                    <p class="card-text text-muted">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        ${escapeHtml(property.location)}
                    </p>
                    <p class="card-text fw-bold text-primary">
                        KSh ${property.price.toLocaleString()} / month
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">${property.type}</span>
                        <span class="badge bg-success">${property.bedrooms} Bed${property.bedrooms != 1 ? 's' : ''}</span>
                        <span class="badge bg-info">${property.bathrooms} Bath${property.bathrooms != 1 ? 's' : ''}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="property_details.php?id=${property.id}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                    ${property.user_id ? `
                    <button class="btn btn-outline-danger btn-sm favorite-btn ${isFavorite ? 'active' : ''}" 
                            data-property-id="${property.id}">
                        <i class="${isFavorite ? 'fas' : 'far'} fa-heart"></i>
                    </button>
                    ` : `
                    <a href="pages/login.php" class="btn btn-outline-secondary btn-sm" title="Login to add favorites">
                        <i class="far fa-heart"></i>
                    </a>
                    `}
                </div>
            </div>
        </div>
    `;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<style>
.property-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.search-box {
    background: rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}
.hero-section {
    background:url(h1.jpg);
    
}
nav{
    background:rgba(0, 0, 0, 0.81);
}
</style>

<?php include 'includes/footer.php'; ?>