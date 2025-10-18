
class HouseHunterApp {
    constructor() {
        this.map = null;
        this.marker = null;
        this.currentLocation = { lat: -1.286389, lng: 36.817223 };
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeTooltips();
            this.initializeEventListeners();
            this.initializeComponents();
        });
    }


    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        this.tooltipList = [...tooltips].map(el => new bootstrap.Tooltip(el));
    }

    initializeEventListeners() {
     
        document.addEventListener('click', (e) => {
            const target = e.target;
            
         
            if (target.closest('.favorite-btn')) {
                e.preventDefault();
                this.handleFavoriteClick(target.closest('.favorite-btn'));
            }
            
           
            if (target.closest('.search-form')) {
                e.preventDefault();
                this.handleSearch(target.closest('.search-form'));
            }
            
            if (target.closest('#mapSearchBtn')) {
                e.preventDefault();
                this.handleMapSearch();
            }
  
            if (target.closest('#searchLocationBtn')) {
                e.preventDefault();
                this.handleLocationSearch();
            }
            
        
            if (target.closest('a[href*="logout"]')) {
                e.preventDefault();
                this.handleLogout();
            }
            
           
            if (target.closest('#addPropertyForm')) {
                const submitBtn = target.closest('button[type="submit"]');
                if (submitBtn) {
                    e.preventDefault();
                    this.handlePropertyFormSubmit(target.closest('#addPropertyForm'));
                }
            }
        });

     
        document.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const input = e.target;
                if (input.closest('input[name="location"], input[name="max_price"]')) {
                    this.performSearch();
                }
                if (input.closest('#mapSearch')) {
                    this.handleMapSearch();
                }
               
                if (input.closest('#addPropertyForm input, #addPropertyForm select, #addPropertyForm textarea')) {
                    const form = input.closest('#addPropertyForm');
                    if (form) {
                        e.preventDefault();
                        this.handlePropertyFormSubmit(form);
                    }
                }
            }
        });

     
        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => this.loadMoreProperties());
        }
    }

    initializeComponents() {
        this.initializeImageGallery();
        this.checkLoginStatus();
        this.initializeMapIfNeeded();
        this.initializeImageValidation();
        this.initializePropertyForm();
    }

    initializePropertyForm() {
        const propertyForm = document.getElementById('addPropertyForm');
        if (propertyForm) {
          
            const submitBtn = propertyForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
            
           
            propertyForm.addEventListener('input', () => {
                this.validatePropertyForm(propertyForm);
            });
        }
    }

  
    validatePropertyForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
       
        const lat = document.getElementById('latitude')?.value;
        const lng = document.getElementById('longitude')?.value;
        const location = document.getElementById('location')?.value;
        
        if (!lat || !lng) {
            isValid = false;
        }
        
        if (!location || location.includes('Location at')) {
            isValid = false;
        }
        
        const files = document.querySelector('input[name="images[]"]')?.files;
        if (!files || files.length === 0) {
            isValid = false;
        }
        
        return isValid;
    }

    async handlePropertyFormSubmit(form) {
        console.log('Property form submission started...');
  
        const lat = document.getElementById('latitude')?.value;
        const lng = document.getElementById('longitude')?.value;
        const files = document.querySelector('input[name="images[]"]')?.files;
        const location = document.getElementById('location')?.value;
        
      
        if (!lat || !lng) {
            this.showNotification('Please select a location on the map by clicking on the desired location.', 'error');
            return;
        }
     
        if (!location || location.includes('Location at')) {
            this.showNotification('Please provide a proper location name, not just coordinates.', 'error');
            return;
        }
        
        if (!files || files.length === 0) {
            this.showNotification('Please upload at least one image of the property.', 'error');
            return;
        }
        
        if (files.length > 10) {
            this.showNotification('Maximum 10 images allowed. Please select fewer images.', 'error');
            return;
        }
        
        for (let file of files) {
            if (!this.validateImageFile(file)) {
                return;
            }
        }
        
        const formData = new FormData(form);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Property...';
        submitBtn.disabled = true;
        
        try {
            console.log('Sending form data to server...');
            const response = await fetch('api/add_property.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response received:', response);
            const data = await response.json();
            console.log('Response data:', data);
            
            if(data.success) {
                this.showNotification('Property added successfully!', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            } else {
                this.showNotification('Error: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while adding the property. Please try again.', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    validateImageFile(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        
        if (file.size > maxSize) {
            this.showNotification(`File ${file.name} is too large. Maximum size is 5MB.`, 'error');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            this.showNotification(`File ${file.name} is not a supported image format. Please use JPG, PNG, or WebP.`, 'error');
            return false;
        }
        
        return true;
    }

    initializeMapIfNeeded() {
        if (document.getElementById('map')) {
            this.initializeMap();
        }
    }

    initializeMap() {
        console.log('Initializing map...');
        this.map = L.map('map').setView([this.currentLocation.lat, this.currentLocation.lng], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(this.map);
        
        this.marker = L.marker([this.currentLocation.lat, this.currentLocation.lng], {
            draggable: true,
            title: "Drag me to exact location"
        }).addTo(this.map);
        
        this.marker.on('dragend', (e) => {
            const pos = this.marker.getLatLng();
            this.updateFormFields(pos.lat, pos.lng);
        });
        
        this.map.on('click', (e) => {
            this.updateMarker(e.latlng);
        });
        
        this.addCustomSearchControl();
        console.log('Map initialized successfully');
    }

    addCustomSearchControl() {
        const searchControl = L.control({ position: 'topright' });
        
        searchControl.onAdd = () => {
            const div = L.DomUtil.create('div', 'custom-search-control');
            div.innerHTML = `
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Search Kenya..." id="mapSearch">
                    <button class="btn btn-primary btn-sm" id="mapSearchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            `;
            return div;
        };
        
        searchControl.addTo(this.map);
    }

    handleMapSearch() {
        const query = document.getElementById('mapSearch')?.value;
        if (query?.trim()) this.geocodeLocation(query);
    }

    handleLocationSearch() {
        const locationInput = document.getElementById('location');
        if (locationInput?.value.trim()) {
            this.geocodeLocation(locationInput.value);
        } else {
            this.showNotification('Please enter a location to search.', 'warning');
        }
    }

    async geocodeLocation(location) {
        const searchBtn = document.querySelector('#mapSearchBtn, #searchLocationBtn');
        
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchBtn.disabled = true;
        }

        try {
            const proxyUrl = 'https://corsproxy.io/?';
            const targetUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location + ', Kenya')}&countrycodes=ke&limit=1`;
            
            const response = await fetch(proxyUrl + targetUrl);
            const data = await response.json();
            
            if (data?.[0]) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                this.map.setView([lat, lng], 16);
                this.updateMarker([lat, lng]);
                
                const locationInput = document.getElementById('location');
                if (locationInput) {
                    locationInput.value = result.display_name.split(',')[0] || location;
                }
                
                this.showNotification('Location found!', 'success');
            } else {
                this.showNotification('Location not found. Please try a different search.', 'warning');
            }
        } catch (error) {
            console.error('Geocoding error:', error);
            this.showNotification('Search service unavailable. Please select location manually.', 'error');
        } finally {
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-search"></i>';
                searchBtn.disabled = false;
            }
        }
    }

    updateMarker(latlng) {
        if (this.marker) {
            this.marker.setLatLng(latlng);
            this.updateFormFields(latlng.lat, latlng.lng);
        }
    }

    updateFormFields(lat, lng) {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        
        this.currentLocation = { lat, lng };
        
        const locationInput = document.getElementById('location');
        if (locationInput && (!locationInput.value || locationInput.value.includes('Location at'))) {
            locationInput.value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    }

    initializeImageGallery() {
        const mainImage = document.querySelector('.main-image');
        const thumbnails = document.querySelectorAll('.thumbnail');
        
        if (mainImage && thumbnails.length) {
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', () => {
                    mainImage.style.opacity = '0.7';
                    const img = new Image();
                    img.src = thumb.src;
                    img.onload = () => {
                        mainImage.src = thumb.src;
                        mainImage.style.opacity = '1';
                        thumbnails.forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                    };
                });
            });
        }
    }

    initializeImageValidation() {
        const imageInputs = document.querySelectorAll('input[type="file"][name="images[]"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', (e) => this.validateImageUpload(e.target));
        });
    }

    validateImageUpload(input) {
        const files = input.files;
        const maxSize = 5 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        
        for (let file of files) {
            if (file.size > maxSize) {
                this.showNotification(`File ${file.name} is too large. Max 5MB.`, 'error');
                input.value = '';
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                this.showNotification(`File ${file.name} is not a supported format.`, 'error');
                input.value = '';
                return;
            }
        }
        
        if (files.length > 10) {
            this.showNotification('Only first 10 images will be uploaded.', 'warning');
        }
    }

    async handleFavoriteClick(button) {
        const propertyId = button.dataset.propertyId;
        if (!propertyId || !this.isLoggedIn()) {
            this.showLoginPrompt();
            return;
        }

        const isFavorite = button.classList.contains('active');
        
        try {
            await this.toggleFavorite(propertyId, button, isFavorite);
        } catch (error) {
            this.showNotification('Error updating favorites.', 'error');
        }
    }

    async toggleFavorite(propertyId, button, isFavorite) {
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        try {
            const response = await fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    property_id: propertyId,
                    action: isFavorite ? 'remove' : 'add'
                })
            });

            const data = await response.json();

            if (data.success) {
                this.updateFavoriteUI(button, isFavorite);
                this.showNotification(
                    isFavorite ? 'Removed from favorites' : 'Added to favorites', 
                    'success'
                );
            } else {
                throw new Error(data.error);
            }
        } finally {
            button.innerHTML = `<i class="${isFavorite ? 'far' : 'fas'} fa-heart"></i>`;
            button.disabled = false;
        }
    }

    updateFavoriteUI(button, wasFavorite) {
        const icon = button.querySelector('i');
        if (wasFavorite) {
            button.classList.replace('btn-danger', 'btn-outline-danger');
            icon.classList.replace('fas', 'far');
            button.classList.remove('active');
        } else {
            button.classList.replace('btn-outline-danger', 'btn-danger');
            icon.classList.replace('far', 'fas');
            button.classList.add('active');
        }
    }

    async handleLogout() {
        if (!confirm('Are you sure you want to logout?')) return;

        try {
            const response = await fetch('pages/logout_ajax.php');
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Logged out successfully', 'success');
                setTimeout(() => window.location.href = 'pages/login.php', 1000);
            }
        } catch (error) {
            window.location.href = 'pages/logout.php';
        }
    }

    handleSearch(form) {
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData) {
            if (value) params.append(key, value);
        }
        
        const queryString = params.toString();
        window.location.href = `index.php?page=properties${queryString ? '&' + queryString : ''}`;
    }

    performSearch() {
        const location = document.querySelector('input[name="location"]')?.value;
        const type = document.querySelector('select[name="type"]')?.value;
        const maxPrice = document.querySelector('input[name="max_price"]')?.value;
        
        const params = new URLSearchParams();
        if (location) params.append('location', location);
        if (type) params.append('type', type);
        if (maxPrice) params.append('max_price', maxPrice);
        
        const queryString = params.toString();
        window.location.href = `index.php?page=properties${queryString ? '&' + queryString : ''}`;
    }

    async loadMoreProperties() {
        const loadMoreBtn = document.querySelector('.load-more-btn');
        const container = document.querySelector('#properties-container');
        const currentPage = parseInt(loadMoreBtn.dataset.page) || 1;
        
        if (!loadMoreBtn || !container) return;

        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;

        try {
            const response = await fetch(`api/load_more_properties.php?page=${currentPage + 1}`);
            const data = await response.json();
            
            if (data.properties?.length) {
                data.properties.forEach(property => {
                    container.insertAdjacentHTML('beforeend', this.createPropertyCard(property));
                });
                loadMoreBtn.dataset.page = currentPage + 1;
                if (!data.hasMore) loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'none';
            }
        } catch (error) {
            this.showNotification('Error loading properties', 'error');
        } finally {
            loadMoreBtn.innerHTML = 'Load More';
            loadMoreBtn.disabled = false;
        }
    }

    createPropertyCard(property) {
        const imageUrl = property.images ? JSON.parse(property.images)[0] : 'assets/images/default-property.jpg';
        const isFavorite = property.is_favorite ? 'active' : '';
        
        return `
            <div class="col-md-4 mb-4">
                <div class="card property-card h-100">
                    <img src="${imageUrl}" class="card-img-top" alt="${property.title}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">${this.escapeHtml(property.title)}</h5>
                        <p class="text-muted">${this.escapeHtml(property.location)}</p>
                        <p class="fw-bold text-primary">KSh ${property.price.toLocaleString()}/month</p>
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-primary">${property.type}</span>
                            <span class="badge bg-success">${property.bedrooms} Bed</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="property_details.php?id=${property.id}" class="btn btn-primary btn-sm">View Details</a>
                        <button class="btn btn-outline-danger btn-sm favorite-btn ${isFavorite}" data-property-id="${property.id}">
                            <i class="${isFavorite ? 'fas' : 'far'} fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    escapeHtml(unsafe) {
        return unsafe.replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[m]));
    }

    isLoggedIn() {
        return document.body.classList.contains('user-logged-in');
    }

    checkLoginStatus() {
        if (this.isLoggedIn()) {
            document.body.classList.add('user-logged-in');
        }
    }

    showLoginPrompt() {
        if (confirm('Please login to add favorites. Login now?')) {
            window.location.href = 'pages/login.php';
        }
    }

    showNotification(message, type = 'info') {
    
        document.querySelectorAll('.custom-notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;
        `;
        
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icons[type] || 'info-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
}


const houseHunterApp = new HouseHunterApp();
window.HouseHunterApp = houseHunterApp;

window.debounce = (func, wait) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

window.formatPrice = (price) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency', currency: 'KES', minimumFractionDigits: 0
    }).format(price);
};

window.validateForm = (formId) => {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    return isValid;
};

document.addEventListener('DOMContentLoaded', function() {

    const imageInput = document.querySelector('input[name="images[]"]');
    const previewContainer = document.getElementById('imagePreview');
    const previewImages = document.getElementById('previewImages');
    
    if (imageInput && previewContainer && previewImages) {
        imageInput.addEventListener('change', function() {
            previewImages.innerHTML = '';
            
            if (this.files.length > 0) {
                previewContainer.style.display = 'block';
                
                Array.from(this.files).forEach((file, index) => {
                    if (index >= 10) return;
                    
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
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }
});