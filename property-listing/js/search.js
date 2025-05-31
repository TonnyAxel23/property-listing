document.addEventListener('DOMContentLoaded', function() {
    // Price range slider functionality
    const priceRange = document.getElementById('price-range');
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    
    if (priceRange && minPriceInput && maxPriceInput) {
        noUiSlider.create(priceRange, {
            start: [parseInt(minPriceInput.value) || 0, parseInt(maxPriceInput.value) || 1000000],
            connect: true,
            range: {
                'min': 0,
                'max': 1000000
            },
            step: 10000
        });
        
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
    
    // Instant search filtering
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        // Listen to all filter changes
        const filterInputs = filterForm.querySelectorAll('input, select');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
        
        // For range sliders (like price)
        if (priceRange) {
            priceRange.noUiSlider.on('set', function() {
                filterForm.submit();
            });
        }
    }
});