<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'landlord') {
    header("Location: pages/login.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Property</h4>
                </div>
                <div class="card-body">
                    <form id="addPropertyForm" enctype="multipart/form-data">
                      
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-info-circle text-primary"></i> Basic Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Title *</label>
                                    <input type="text" name="title" class="form-control" required 
                                           placeholder="e.g., Spacious 3 Bedroom Apartment in Westlands">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Type *</label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Select Property Type</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="house">House</option>
                                        <option value="studio">Studio</option>
                                        <option value="bedsitter">Bedsitter</option>
                                        <option value="cottage">Cottage</option>
                                        <option value="mansion">Mansion</option>
                                        <option value="townhouse">Townhouse</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="4" required 
                                              placeholder="Describe the property features, condition, neighborhood, and any unique selling points..."></textarea>
                                </div>
                            </div>
                        </div>

                       
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-tag text-primary"></i> Pricing & Details</h5>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Monthly Rent (KSh) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KSh</span>
                                        <input type="number" name="price" class="form-control" required 
                                               placeholder="25000" min="0" step="100">
                                    </div>
                                    <div class="form-text">Monthly rental price in Kenyan Shillings</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Bedrooms *</label>
                                    <select name="bedrooms" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="0">Studio</option>
                                        <option value="1">1 Bedroom</option>
                                        <option value="2">2 Bedrooms</option>
                                        <option value="3">3 Bedrooms</option>
                                        <option value="4">4 Bedrooms</option>
                                        <option value="5">5+ Bedrooms</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Bathrooms *</label>
                                    <select name="bathrooms" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="1">1 Bathroom</option>
                                        <option value="2">2 Bathrooms</option>
                                        <option value="3">3 Bathrooms</option>
                                        <option value="4">4+ Bathrooms</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-map-marker-alt text-primary"></i> Location Details</h5>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Location *</label>
                                    <div class="input-group">
                                        <input type="text" id="location" name="location" class="form-control" required 
                                               placeholder="Enter any location in Kenya (town, estate, street, landmark)...">
                                        <button type="button" class="btn btn-outline-primary" id="searchLocationBtn">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                    <div class="form-text">Type any location in Kenya - town, estate, street, or landmark</div>
                                    <input type="hidden" id="latitude" name="latitude" value="-1.286389">
                                    <input type="hidden" id="longitude" name="longitude" value="36.817223">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Property Location Map</label>
                                    <div id="map" style="height: 400px; border: 1px solid #ddd; border-radius: 8px;"></div>
                                    <div class="form-text">Click on the map to mark the exact property location or drag the marker. You can also use the search above.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-star text-primary"></i> Amenities & Features</h5>
                                <div class="form-text mb-3">Select all amenities available with this property</div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="wifi" id="wifi">
                                    <label class="form-check-label" for="wifi">
                                        <i class="fas fa-wifi"></i> WiFi Internet
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="parking" id="parking">
                                    <label class="form-check-label" for="parking">
                                        <i class="fas fa-car"></i> Parking Space
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="security" id="security">
                                    <label class="form-check-label" for="security">
                                        <i class="fas fa-shield-alt"></i> Security
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="water" id="water">
                                    <label class="form-check-label" for="water">
                                        <i class="fas fa-tint"></i> Running Water
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="electricity" id="electricity">
                                    <label class="form-check-label" for="electricity">
                                        <i class="fas fa-bolt"></i> Stable Electricity
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="garden" id="garden">
                                    <label class="form-check-label" for="garden">
                                        <i class="fas fa-tree"></i> Garden
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="balcony" id="balcony">
                                    <label class="form-check-label" for="balcony">
                                        <i class="fas fa-building"></i> Balcony
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="furnished" id="furnished">
                                    <label class="form-check-label" for="furnished">
                                        <i class="fas fa-couch"></i> Fully Furnished
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="ac" id="ac">
                                    <label class="form-check-label" for="ac">
                                        <i class="fas fa-wind"></i> Air Conditioning
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="heating" id="heating">
                                    <label class="form-check-label" for="heating">
                                        <i class="fas fa-fire"></i> Heating
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="laundry" id="laundry">
                                    <label class="form-check-label" for="laundry">
                                        <i class="fas fa-tshirt"></i> Laundry
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="pool" id="pool">
                                    <label class="form-check-label" for="pool">
                                        <i class="fas fa-swimming-pool"></i> Swimming Pool
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-camera text-primary"></i> Property Images</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Upload Property Photos *</label>
                                    <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle"></i> 
                                        Upload clear photos of the property (exterior, interior, rooms, amenities). 
                                        You can select multiple images. Maximum 10 images allowed. Supported formats: JPG, PNG, WebP.
                                    </div>
                                </div>
                                
                                <div class="image-preview-container mb-3" id="imagePreview" style="display: none;">
                                    <label class="form-label">Image Preview:</label>
                                    <div class="row mt-2" id="previewImages"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2"><i class="fas fa-clipboard-list text-primary"></i> Additional Information</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Property Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="available" value="available" checked>
                                        <label class="form-check-label text-success" for="available">
                                            <i class="fas fa-check-circle"></i> Available for Rent
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="rented" value="rented">
                                        <label class="form-check-label text-secondary" for="rented">
                                            <i class="fas fa-times-circle"></i> Currently Rented
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb"></i> 
                                    <strong>Tip:</strong> Provide detailed and accurate information to attract quality tenants faster. 
                                    High-quality photos and complete descriptions significantly improve response rates.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus-circle"></i> Add Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

document.getElementById('addPropertyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    const files = document.querySelector('input[name="images[]"]').files;
    const location = document.getElementById('location').value;
    
    if (!lat || !lng) {
        HouseHunterApp.showNotification('Please select a location on the map by clicking on the desired location.', 'error');
        return;
    }
        if (!location || location.includes('Location at')) {
        HouseHunterApp.showNotification('Please provide a proper location name, not just coordinates.', 'error');
        return;
    }
    
    if (files.length === 0) {
        HouseHunterApp.showNotification('Please upload at least one image of the property.', 'error');
        return;
    }
    
    if (files.length > 10) {
        HouseHunterApp.showNotification('Maximum 10 images allowed. Please select fewer images.', 'error');
        return;
    }

    for (let file of files) {
        if (!validateImageFile(file)) {
            return;
        }
    }
    
    const formData = new FormData(this);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Property...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('api/add_property.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if(data.success) {
            HouseHunterApp.showNotification('Property added successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        } else {
            HouseHunterApp.showNotification('Error: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        HouseHunterApp.showNotification('An error occurred while adding the property. Please try again.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function validateImageFile(file) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    
    if (file.size > maxSize) {
        HouseHunterApp.showNotification(`File ${file.name} is too large. Maximum size is 5MB.`, 'error');
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        HouseHunterApp.showNotification(`File ${file.name} is not a supported image format. Please use JPG, PNG, or WebP.`, 'error');
        return false;
    }
    
    return true;
}

function initializeImagePreview() {
    const fileInput = document.querySelector('input[name="images[]"]');
    const previewContainer = document.getElementById('imagePreview');
    const previewImages = document.getElementById('previewImages');
    
    if (!fileInput || !previewContainer || !previewImages) return;
    
    fileInput.addEventListener('change', function() {
        previewImages.innerHTML = '';
        
        if (this.files.length > 0) {
            previewContainer.style.display = 'block';
            
            Array.from(this.files).forEach((file, index) => {
                if (index >= 10) return;
                
                if (!validateImageFile(file)) {
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;" alt="Preview ${index + 1}">
                            <div class="card-body p-2">
                                <small class="text-muted">Image ${index + 1}</small>
                            </div>
                        </div>
                    `;
                    previewImages.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
            
            if (this.files.length > 10) {
                const warning = document.createElement('div');
                warning.className = 'alert alert-warning mt-2';
                warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Only the first 10 images will be uploaded.';
                previewContainer.appendChild(warning);
            }
        } else {
            previewContainer.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initializeImagePreview();
});
</script>

<style>
.location-suggestions {
    font-size: 14px;
}
.suggestion-item:hover {
    background-color: #007bff;
    color: white;
}
.leaflet-container {
    font-family: inherit;
}
.card-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
.border-bottom {
    border-color: #007bff !important;
}
.custom-search-control {
    background: white;
    padding: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.custom-search-control .form-control {
    width: 200px;
}
.property-image-preview {
    max-height: 100px;
    object-fit: cover;
}
</style>

<?php include 'includes/footer.php'; ?>