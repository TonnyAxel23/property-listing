    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>PropertyFinder helps you find your dream home with the most comprehensive listings in the region.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/search.html">Search Properties</a></li>
                    <li><a href="/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Property St, City</p>
                <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                <p><i class="fas fa-envelope"></i> info@propertyfinder.com</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> PropertyFinder. All rights reserved.</p>
        </div>
    </footer>
    <script src="/js/main.js"></script>
    <?php if(isset($mapScript)): ?>
        <script src="<?php echo $mapScript; ?>"></script>
    <?php endif; ?>
</body>
</html>