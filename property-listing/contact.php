<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $propertyId = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    $propertyTitle = filter_input(INPUT_POST, 'property_title', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    if ($propertyId && $name && $email && $message) {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO inquiries (property_id, name, email, phone, message) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$propertyId, $name, $email, $phone, $message]);
        
        // Send email (in a real application)
        $to = "seller@example.com"; // Would actually query seller's email from DB
        $subject = "New inquiry for: $propertyTitle";
        $emailMessage = "You have received a new inquiry for your property:\n\n";
        $emailMessage .= "Property: $propertyTitle\n";
        $emailMessage .= "From: $name\n";
        $emailMessage .= "Email: $email\n";
        $emailMessage .= "Phone: $phone\n\n";
        $emailMessage .= "Message:\n$message";
        
        $headers = "From: $email";
        
        // In production, use a proper mailer library
        mail($to, $subject, $emailMessage, $headers);
        
        $success = true;
    } else {
        $error = "Please fill in all required fields correctly.";
    }
}

$pageTitle = "Contact Seller";
?>

<section class="contact-confirmation">
    <?php if (isset($success) && $success): ?>
        <div class="alert success">
            <h2>Thank You!</h2>
            <p>Your message has been sent successfully. The seller will contact you soon.</p>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert error">
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="property.php?id=<?php echo $propertyId; ?>" class="btn">Go Back</a>
        </div>
    <?php else: ?>
        <div class="alert info">
            <h2>Invalid Request</h2>
            <p>Please submit the contact form from the property page.</p>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>