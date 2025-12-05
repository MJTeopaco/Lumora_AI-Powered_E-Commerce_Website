/**
 * Address Form JavaScript
 * app/public/js/address-form.js
 * Handles cascade dropdown functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    // Get existing values (set by view)
    const existingAddress = window.existingAddress || {};

    // Populate select options
    function populateSelect(selectElement, options, selectedValue = '') {
        const capitalizedName = selectElement.name.charAt(0).toUpperCase() + selectElement.name.slice(1);
        selectElement.innerHTML = `<option value="">Select ${capitalizedName}</option>`;
        
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
    regionSelect.addEventListener('change', function() {
        const selectedRegion = this.value;
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        if (selectedRegion && window.addressData[selectedRegion]) {
            const provinces = Object.keys(window.addressData[selectedRegion]);
            populateSelect(provinceSelect, provinces);
        }
    });

    // Province change handler
    provinceSelect.addEventListener('change', function() {
        const selectedRegion = regionSelect.value;
        const selectedProvince = this.value;
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        if (selectedRegion && selectedProvince && 
            window.addressData[selectedRegion] && 
            window.addressData[selectedRegion][selectedProvince]) {
            const cities = Object.keys(window.addressData[selectedRegion][selectedProvince]);
            populateSelect(citySelect, cities);
        }
    });

    // City change handler
    citySelect.addEventListener('change', function() {
        const selectedRegion = regionSelect.value;
        const selectedProvince = provinceSelect.value;
        const selectedCity = this.value;
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        if (selectedRegion && selectedProvince && selectedCity && 
            window.addressData[selectedRegion][selectedProvince] && 
            window.addressData[selectedRegion][selectedProvince][selectedCity]) {
            const barangays = window.addressData[selectedRegion][selectedProvince][selectedCity];
            populateSelect(barangaySelect, barangays);
        }
    });

    // Initialize for edit mode
    function initAddressForm() {
        if (existingAddress.province || existingAddress.city || existingAddress.barangay) {
            if (regionSelect.value) {
                const selectedRegion = regionSelect.value;
                
                if (window.addressData[selectedRegion]) {
                    const provinces = Object.keys(window.addressData[selectedRegion]);
                    populateSelect(provinceSelect, provinces, existingAddress.province);
                    
                    // Populate cities after delay
                    setTimeout(() => {
                        if (existingAddress.province && window.addressData[selectedRegion][existingAddress.province]) {
                            const cities = Object.keys(window.addressData[selectedRegion][existingAddress.province]);
                            populateSelect(citySelect, cities, existingAddress.city);
                            
                            // Populate barangays after delay
                            setTimeout(() => {
                                if (existingAddress.city && 
                                    window.addressData[selectedRegion][existingAddress.province][existingAddress.city]) {
                                    const barangays = window.addressData[selectedRegion][existingAddress.province][existingAddress.city];
                                    populateSelect(barangaySelect, barangays, existingAddress.barangay);
                                }
                            }, 100);
                        }
                    }, 100);
                }
            }
        }
    }

    // Run initialization
    initAddressForm();
});