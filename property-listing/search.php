<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get search parameters with validation
$location = isset($_GET['location']) ? htmlspecialchars(trim($_GET['location'])) : '';
$type = isset($_GET['type']) && in_array($_GET['type'], ['house', 'apartment', 'condo', 'land', 'commercial']) 
    ? $_GET['type'] : '';
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : null;
$bathrooms = isset($_GET['bathrooms']) ? (int)$_GET['bathrooms'] : null;
$features = isset($_GET['features']) ? (array)$_GET['features'] : [];

try {
    // Build query
    $query = "SELECT p.*, pi.image_path FROM properties p 
              LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
              WHERE 1=1";
    $params = [];
    
    if (!empty($location)) {
        $query .= " AND (p.location LIKE :location OR p.city LIKE :location OR p.zipcode LIKE :location)";
        $params[':location'] = "%$location%";
    }
    
    if (!empty($type)) {
        $query .= " AND p.property_type = :type";
        $params[':type'] = $type;
    }
    
    if ($minPrice !== null) {
        $query .= " AND p.price >= :min_price";
        $params[':min_price'] = $minPrice;
    }
    
    if ($maxPrice !== null) {
        $query .= " AND p.price <= :max_price";
        $params[':max_price'] = $maxPrice;
    }
    
    if ($bedrooms !== null) {
        $query .= " AND p.bedrooms >= :bedrooms";
        $params[':bedrooms'] = $bedrooms;
    }
    
    if ($bathrooms !== null) {
        $query .= " AND p.bathrooms >= :bathrooms";
        $params[':bathrooms'] = $bathrooms;
    }
    
    if (!empty($features)) {
        foreach ($features as $i => $feature) {
            $query .= " AND FIND_IN_SET(:feature$i, p.features)";
            $params[":feature$i"] = $feature;
        }
    }
    
    // Add sorting
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';
    switch($sort) {
        case 'price_desc': $query .= " ORDER BY p.price DESC"; break;
        case 'newest': $query .= " ORDER BY p.created_at DESC"; break;
        case 'oldest': $query .= " ORDER BY p.created_at ASC"; break;
        case 'area_desc': $query .= " ORDER BY p.area_sqft DESC"; break;
        default: $query .= " ORDER BY p.price ASC"; break;
    }
    
    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
    
    // Get distinct features for filter
    $stmt = $pdo->query("SELECT DISTINCT feature FROM property_features ORDER BY feature");
    $allFeatures = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

$pageTitle = "Search Properties";
?>

<!-- Enhanced filters section -->
<div class="filters">
    <h3>Filters</h3>
    <form id="filter-form">
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>"
                   placeholder="City, neighborhood or ZIP">
        </div>
        
        <div class="form-group">
            <label for="type">Property Type</label>
            <select id="type" name="type">
                <option value="">Any Type</option>
                <option value="house" <?php echo $type == 'house' ? 'selected' : ''; ?>>House</option>
                <option value="apartment" <?php echo $type == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                <option value="condo" <?php echo $type == 'condo' ? 'selected' : ''; ?>>Condo</option>
                <option value="land" <?php echo $type == 'land' ? 'selected' : ''; ?>>Land</option>
                <option value="commercial" <?php echo $type == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Price Range</label>
            <div class="price-range-slider" id="price-range"></div>
            <div class="price-inputs">
                <input type="number" id="min-price" name="min_price" placeholder="Min" 
                       value="<?php echo $minPrice !== null ? $minPrice : ''; ?>">
                <span>to</span>
                <input type="number" id="max-price" name="max_price" placeholder="Max" 
                       value="<?php echo $maxPrice !== null ? $maxPrice : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="bedrooms">Bedrooms</label>
            <select id="bedrooms" name="bedrooms">
                <option value="">Any</option>
                <option value="1" <?php echo $bedrooms == 1 ? 'selected' : ''; ?>>1+</option>
                <option value="2" <?php echo $bedrooms == 2 ? 'selected' : ''; ?>>2+</option>
                <option value="3" <?php echo $bedrooms == 3 ? 'selected' : ''; ?>>3+</option>
                <option value="4" <?php echo $bedrooms == 4 ? 'selected' : ''; ?>>4+</option>
                <option value="5" <?php echo $bedrooms == 5 ? 'selected' : ''; ?>>5+</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="bathrooms">Bathrooms</label>
            <select id="bathrooms" name="bathrooms">
                <option value="">Any</option>
                <option value="1" <?php echo $bathrooms == 1 ? 'selected' : ''; ?>>1+</option>
                <option value="2" <?php echo $bathrooms == 2 ? 'selected' : ''; ?>>2+</option>
                <option value="3" <?php echo $bathrooms == 3 ? 'selected' : ''; ?>>3+</option>
            </select>
        </div>
        
        <?php if(!empty($allFeatures)): ?>
        <div class="form-group">
            <label>Features</label>
            <div class="feature-checkboxes">
                <?php foreach($allFeatures as $feature): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="features[]" value="<?php echo htmlspecialchars($feature); ?>"
                           <?php echo in_array($feature, $features) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($feature); ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="sort">Sort By</label>
            <select id="sort" name="sort">
                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                <option value="area_desc" <?php echo $sort == 'area_desc' ? 'selected' : ''; ?>>Largest</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Apply Filters</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </form>
</div>

<!-- Add to search.js -->
document.addEventListener('DOMContentLoaded', function() {
    // Initialize price range slider
    const priceRange = document.getElementById('price-range');
    if (priceRange) {
        noUiSlider.create(priceRange, {
            start: [
                <?php echo $minPrice !== null ? $minPrice : 0; ?>, 
                <?php echo $maxPrice !== null ? $maxPrice : 1000000; ?>
            ],
            connect: true,
            range: {
                'min': 0,
                'max': 1000000
            },
            step: 10000,
            tooltips: [true, true],
            format: {
                to: function(value) {
                    return '$' + Math.round(value).toLocaleString();
                },
                from: function(value) {
                    return Number(value.replace(/\D/g, ''));
                }
            }
        });
        
        const minPriceInput = document.getElementById('min-price');
        const maxPriceInput = document.getElementById('max-price');
        
        priceRange.noUiSlider.on('update', function(values, handle) {
            const value = values[handle];
            if (handle) {
                maxPriceInput.value = Math.round(value);
            } else {
                minPriceInput.value = Math.round(value);
            }
        });
        
        minPriceInput.addEventListener('change', function() {
            priceRange.noUiSlider.set([this.value, null]);
        });
        
        maxPriceInput.addEventListener('change', function() {
            priceRange.noUiSlider.set([null, this.value]);
        });
    }
    
    // Debounce form submission
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        let timeout;
        const inputs = filterForm.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        });
        
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            fetch('search.php?' + params.toString())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newResults = doc.querySelector('.search-results').innerHTML;
                    document.querySelector('.search-results').innerHTML = newResults;
                    history.pushState({}, '', 'search.php?' + params.toString());
                });
        });
    }
});