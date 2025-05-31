// Property Gallery Image Switcher
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const thumbnails = document.querySelectorAll('.thumbnail img');
    const mainImage = document.querySelector('.main-image img');
    
    if (thumbnails.length > 0 && mainImage) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImage.src = this.src;
                mainImage.alt = this.alt;
            });
        });
    }
    
    // Mobile menu toggle (would need HTML element)
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Form validation
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = this.querySelector('#name');
            const email = this.querySelector('#email');
            const message = this.querySelector('#message');
            let isValid = true;
            
            if (!name.value.trim()) {
                isValid = false;
                name.classList.add('error');
            } else {
                name.classList.remove('error');
            }
            
            if (!email.value.trim() || !email.validity.valid) {
                isValid = false;
                email.classList.add('error');
            } else {
                email.classList.remove('error');
            }
            
            if (!message.value.trim()) {
                isValid = false;
                message.classList.add('error');
            } else {
                message.classList.remove('error');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });
    }
});

// Lazy loading images
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    // Typeahead search
const heroLocationInput = document.getElementById('hero-location');
const locationSuggestions = document.getElementById('location-suggestions');

if (heroLocationInput && locationSuggestions) {
    heroLocationInput.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if (query.length < 2) {
            locationSuggestions.innerHTML = '';
            return;
        }
        
        fetch(`api/locations.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                locationSuggestions.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const suggestion = document.createElement('div');
                        suggestion.className = 'suggestion-item';
                        suggestion.textContent = item;
                        suggestion.addEventListener('click', function() {
                            heroLocationInput.value = item;
                            locationSuggestions.innerHTML = '';
                        });
                        locationSuggestions.appendChild(suggestion);
                    });
                } else {
                    const noResults = document.createElement('div');
                    noResults.className = 'suggestion-item no-results';
                    noResults.textContent = 'No locations found';
                    locationSuggestions.appendChild(noResults);
                }
            });
    }, 300));
    
    document.addEventListener('click', function(e) {
        if (e.target !== heroLocationInput) {
            locationSuggestions.innerHTML = '';
        }
    });
}

function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}
    lazyImages.forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.dataset.src = img.src;
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
            imageObserver.observe(img);
        }
    });
});