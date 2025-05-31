<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$propertyId = (int)$_GET['id'];

try {
    // Get property details
    $stmt = $pdo->prepare("SELECT p.*, u.name as seller_name, u.email as seller_email 
                          FROM properties p
                          LEFT JOIN users u ON p.user_id = u.id
                          WHERE p.id = ?");
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch();

    if (!$property) {
        header("Location: index.php");
        exit();
    }

    // Get property images
    $stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$propertyId]);
    $images = $stmt->fetchAll();

    // Get similar properties
    $stmt = $pdo->prepare("SELECT p.*, pi.image_path 
                          FROM properties p 
                          LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
                          WHERE p.property_type = ? AND p.id != ? 
                          ORDER BY RAND() LIMIT 3");
    $stmt->execute([$property['property_type'], $propertyId]);
    $similarProperties = $stmt->fetchAll();

    // Set page title
    $pageTitle = htmlspecialchars($property['title']) . " | Property Listings";

    // For map integration
    $mapScript = "https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=places";
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}
?>

<!-- Add to the property info section -->
<div class="property-features">
    <h3>Features</h3>
    <ul>
        <?php 
        $features = explode(',', $property['features']);
        foreach($features as $feature): 
            if(trim($feature)): ?>
                <li><?php echo htmlspecialchars(trim($feature)); ?></li>
            <?php endif; 
        endforeach; ?>
    </ul>
</div>

<!-- Add similar properties section at the bottom -->
<?php if(count($similarProperties) > 0): ?>
<section class="similar-properties">
    <h2>Similar Properties</h2>
    <div class="property-grid">
        <?php foreach($similarProperties as $property): ?>
            <div class="property-card">
                <img src="/uploads/<?php echo htmlspecialchars($property['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>"
                     loading="lazy">
                <div class="property-info">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p class="price">$<?php echo number_format($property['price']); ?></p>
                    <p class="location"><?php echo htmlspecialchars($property['location']); ?></p>
                    <div class="property-meta">
                        <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> beds</span>
                        <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> baths</span>
                    </div>
                    <a href="property.php?id=<?php echo $property['id']; ?>" class="btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Enhanced map script -->
<script>
    function initMap() {
        const propertyLocation = { 
            lat: <?php echo $property['latitude']; ?>, 
            lng: <?php echo $property['longitude']; ?> 
        };
        
        const map = new google.maps.Map(document.getElementById("property-map"), {
            zoom: 15,
            center: propertyLocation,
            styles: [
                {
                    "featureType": "poi",
                    "stylers": [{"visibility": "off"}]
                }
            ]
        });
        
        const marker = new google.maps.Marker({
            position: propertyLocation,
            map: map,
            title: "<?php echo addslashes($property['title']); ?>"
        });
        
        // Add nearby places service
        const service = new google.maps.places.PlacesService(map);
        const request = {
            location: propertyLocation,
            radius: '500',
            type: ['school', 'train_station', 'park']
        };
        
        service.nearbySearch(request, (results, status) => {
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                results.forEach(place => {
                    new google.maps.Marker({
                        position: place.geometry.location,
                        map: map,
                        icon: {
                            url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                        },
                        title: place.name
                    });
                });
            }
        });
    }
</script>