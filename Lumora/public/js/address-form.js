// js/address-form.js
// Address form handler with cascade dropdowns

document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    
    // Get existing address data for edit mode
    const existingAddress = window.existingAddress || {};
    
    // Function to populate select options
    function populateSelect(selectElement, options, selectedValue = '') {
        const placeholder = selectElement.getAttribute('name');
        const capitalizedPlaceholder = placeholder.charAt(0).toUpperCase() + placeholder.slice(1);
        
        selectElement.innerHTML = `<option value="">Select ${capitalizedPlaceholder}</option>`;
        
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option;
            optionElement.textContent = option;
            if (option === selectedValue) {
                optionElement.selected = true;
            }
            selectElement.appendChild(optionElement);
        });
    }
    
    // Region change handler
    if (regionSelect) {
        regionSelect.addEventListener('change', function() {
            const selectedRegion = this.value;
            
            // Reset dependent dropdowns
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (selectedRegion && addressData[selectedRegion]) {
                const provinces = Object.keys(addressData[selectedRegion]);
                populateSelect(provinceSelect, provinces);
            }
        });
    }
    
    // Province change handler
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const selectedRegion = regionSelect.value;
            const selectedProvince = this.value;
            
            // Reset dependent dropdowns
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (selectedRegion && selectedProvince && 
                addressData[selectedRegion] && 
                addressData[selectedRegion][selectedProvince]) {
                const cities = Object.keys(addressData[selectedRegion][selectedProvince]);
                populateSelect(citySelect, cities);
            }
        });
    }
    
    // City change handler
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const selectedRegion = regionSelect.value;
            const selectedProvince = provinceSelect.value;
            const selectedCity = this.value;
            
            // Reset barangay dropdown
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (selectedRegion && selectedProvince && selectedCity && 
                addressData[selectedRegion] && 
                addressData[selectedRegion][selectedProvince] && 
                addressData[selectedRegion][selectedProvince][selectedCity]) {
                const barangays = addressData[selectedRegion][selectedProvince][selectedCity];
                populateSelect(barangaySelect, barangays);
            }
        });
    }
    
    // Initialize selects for edit mode
    (function initAddressForm() {
        if (existingAddress.province || existingAddress.city || existingAddress.barangay) {
            if (regionSelect.value) {
                const selectedRegion = regionSelect.value;
                
                if (addressData[selectedRegion]) {
                    const provinces = Object.keys(addressData[selectedRegion]);
                    populateSelect(provinceSelect, provinces, existingAddress.province);
                    
                    // Populate cities after a delay
                    setTimeout(() => {
                        if (existingAddress.province && 
                            addressData[selectedRegion][existingAddress.province]) {
                            const cities = Object.keys(addressData[selectedRegion][existingAddress.province]);
                            populateSelect(citySelect, cities, existingAddress.city);
                            
                            // Populate barangays after another delay
                            setTimeout(() => {
                                if (existingAddress.city && 
                                    addressData[selectedRegion][existingAddress.province][existingAddress.city]) {
                                    const barangays = addressData[selectedRegion][existingAddress.province][existingAddress.city];
                                    populateSelect(barangaySelect, barangays, existingAddress.barangay);
                                }
                            }, 100);
                        }
                    }, 100);
                }
            }
        }
    })();
    
    // Form validation before submit
    const addressForm = document.getElementById('addressForm');
    if (addressForm) {
        addressForm.addEventListener('submit', function(e) {
            const requiredFields = ['region', 'province', 'city', 'barangay', 'address_line_1'];
            let isValid = true;
            
            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && !field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Remove error class on input
                    field.addEventListener('change', function() {
                        this.classList.remove('error');
                    }, { once: true });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    }
});