<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

try {
    // Get featured properties
    $stmt = $pdo->query("SELECT p.*, pi.image_path 
                        FROM properties p 
                        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
                        WHERE p.featured = 1 
                        ORDER BY p.created_at DESC
                        LIMIT 6");
    $featuredProperties = $stmt->fetchAll();

    // Get recent properties
    $stmt = $pdo->query("SELECT p.*, pi.image_path 
                        FROM properties p 
                        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
                        ORDER BY p.created_at DESC
                        LIMIT 3");
    $recentProperties = $stmt->fetchAll();

    // Get property counts by type
    $propertyTypes = $pdo->query("SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type")->fetchAll();
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

$pageTitle = "Find Your Dream Home";
?>

<!-- Enhanced hero section with typeahead search -->
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Home</h1>
        <p>Browse thousands of properties across the country</p>
        
        <form action="search.php" method="get" class="search-form" id="hero-search">
            <div class="search-input-container">
                <input type="text" name="location" id="hero-location" placeholder="Enter city, neighborhood, or address" autocomplete="off">
                <div class="suggestions" id="location-suggestions"></div>
            </div>
            <select name="type">
                <option value="">Any Type</option>
                <?php foreach($propertyTypes as $type): ?>
                    <option value="<?php echo htmlspecialchars($type['property_type']); ?>">
                        <?php echo ucfirst(htmlspecialchars($type['property_type'])); ?> (<?php echo $type['count']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>

<!-- Property type quick links -->
<section class="property-types">
    <h2>Browse by Property Type</h2>
    <div class="type-grid">
        <?php foreach($propertyTypes as $type): ?>
            <a href="search.php?type=<?php echo urlencode($type['property_type']); ?>" class="type-card">
                <div class="type-icon">
                    <?php switch($type['property_type']) {
                        case 'house': echo '<i class="fas fa-home"></i>'; break;
                        case 'apartment': echo '<i class="fas fa-building"></i>'; break;
                        case 'condo': echo '<i class="fas fa-city"></i>'; break;
                        case 'land': echo '<i class="fas fa-tree"></i>'; break;
                        case 'commercial': echo '<i class="fas fa-store"></i>'; break;
                        default: echo '<i class="fas fa-key"></i>';
                    } ?>
                </div>
                <h3><?php echo ucfirst(htmlspecialchars($type['property_type'])); ?></h3>
                <p><?php echo $type['count']; ?> properties</p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Properties section -->
<section class="featured-properties">
    <h2>Featured Properties</h2>
    <div class="property-grid">
        <?php foreach($featuredProperties as $property): ?>
            <div class="property-card">
                <a href="property.php?id=<?php echo $property['id']; ?>">
                    <img src="/uploads/<?php echo htmlspecialchars($property['image_path']); ?>" 
                        alt="<?php echo htmlspecialchars($property['title']); ?>" loading="lazy">
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                        <p><?php echo htmlspecialchars($property['location']); ?></p>
                        <p class="price">Ksh <?php echo number_format($property['price']); ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Recently Added Properties -->
<section class="recent-properties">
    <h2>Recently Added</h2>
    <div class="property-grid">
        <?php foreach($recentProperties as $property): ?>
            <div class="property-card">
                <div class="new-badge">New</div>
                <a href="property.php?id=<?php echo $property['id']; ?>">
                    <img src="/uploads/<?php echo htmlspecialchars($property['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>" loading="lazy">
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                        <p><?php echo htmlspecialchars($property['location']); ?></p>
                        <p class="price">Ksh <?php echo number_format($property['price']); ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
