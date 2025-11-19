<?php
// app/Views/profile/address-form.view.php - REFACTORED CONTENT ONLY
// This content is designed to be injected into the $content placeholder of profile.layout.php
// Variables $isEdit and $address are assumed to be passed from the controller
?>
<div class="content-header">
    <h1 class="content-title"><?= isset($isEdit) && $isEdit ? 'Edit' : 'Add New' ?> Address</h1>
    <p class="content-subtitle">Please fill in your complete delivery address</p>
</div>

<form method="POST" action="<?= isset($isEdit) && $isEdit ? '/profile/addresses/edit/' . htmlspecialchars($address['address_id'] ?? '') : '/profile/addresses/add' ?>" id="addressForm">
    <div class="form-group">
        <label class="form-label">
            Address Line 1 <span class="required">*</span>
        </label>
        <input 
            type="text" 
            name="address_line_1" 
            class="form-input" 
            placeholder="House No., Building, Street Name"
            value="<?= htmlspecialchars($address['address_line_1'] ?? '') ?>"
            required
        >
    </div>

    <div class="form-group">
        <label class="form-label">
            Address Line 2 (Optional)
        </label>
        <textarea 
            name="address_line_2" 
            class="form-textarea" 
            placeholder="Unit number, floor, nearby landmark"
        ><?= htmlspecialchars($address['address_line_2'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label">
            Region <span class="required">*</span>
        </label>
        <select name="region" id="region" class="form-select" required>
            <option value="">Select Region</option>
            <option value="NCR" <?= (isset($address['region']) && $address['region'] === 'NCR') ? 'selected' : '' ?>>National Capital Region (NCR)</option>
            <option value="Region I" <?= (isset($address['region']) && $address['region'] === 'Region I') ? 'selected' : '' ?>>Region I - Ilocos Region</option>
            <option value="Region II" <?= (isset($address['region']) && $address['region'] === 'Region II') ? 'selected' : '' ?>>Region II - Cagayan Valley</option>
            <option value="Region III" <?= (isset($address['region']) && $address['region'] === 'Region III') ? 'selected' : '' ?>>Region III - Central Luzon</option>
            <option value="Region IV-A" <?= (isset($address['region']) && $address['region'] === 'Region IV-A') ? 'selected' : '' ?>>Region IV-A - CALABARZON</option>
            <option value="Region IV-B" <?= (isset($address['region']) && $address['region'] === 'Region IV-B') ? 'selected' : '' ?>>Region IV-B - MIMAROPA</option>
            <option value="Region V" <?= (isset($address['region']) && $address['region'] === 'Region V') ? 'selected' : '' ?>>Region V - Bicol Region</option>
            <option value="Region VI" <?= (isset($address['region']) && $address['region'] === 'Region VI') ? 'selected' : '' ?>>Region VI - Western Visayas</option>
            <option value="Region VII" <?= (isset($address['region']) && $address['region'] === 'Region VII') ? 'selected' : '' ?>>Region VII - Central Visayas</option>
            <option value="Region VIII" <?= (isset($address['region']) && $address['region'] === 'Region VIII') ? 'selected' : '' ?>>Region VIII - Eastern Visayas</option>
            <option value="Region IX" <?= (isset($address['region']) && $address['region'] === 'Region IX') ? 'selected' : '' ?>>Region IX - Zamboanga Peninsula</option>
            <option value="Region X" <?= (isset($address['region']) && $address['region'] === 'Region X') ? 'selected' : '' ?>>Region X - Northern Mindanao</option>
            <option value="Region XI" <?= (isset($address['region']) && $address['region'] === 'Region XI') ? 'selected' : '' ?>>Region XI - Davao Region</option>
            <option value="Region XII" <?= (isset($address['region']) && $address['region'] === 'Region XII') ? 'selected' : '' ?>>Region XII - SOCCSKSARGEN</option>
            <option value="Region XIII" <?= (isset($address['region']) && $address['region'] === 'Region XIII') ? 'selected' : '' ?>>Region XIII - Caraga</option>
            <option value="CAR" <?= (isset($address['region']) && $address['region'] === 'CAR') ? 'selected' : '' ?>>Cordillera Administrative Region (CAR)</option>
            <option value="BARMM" <?= (isset($address['region']) && $address['region'] === 'BARMM') ? 'selected' : '' ?>>Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">
                Province <span class="required">*</span>
            </label>
            <select name="province" id="province" class="form-select" required>
                <option value="">Select Province</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">
                City/Municipality <span class="required">*</span>
            </label>
            <select name="city" id="city" class="form-select" required>
                <option value="">Select City/Municipality</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">
                Barangay <span class="required">*</span>
            </label>
            <select name="barangay" id="barangay" class="form-select" required>
                <option value="">Select Barangay</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">
                Postal Code
            </label>
            <input 
                type="text" 
                name="postal_code" 
                class="form-input" 
                placeholder="Enter postal code"
                value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>"
                maxlength="4"
                pattern="[0-9]{4}"
            >
        </div>
    </div>

    <div class="form-group">
        <div class="checkbox-group">
            <input 
                type="checkbox" 
                id="is_default" 
                name="is_default" 
                value="1"
                <?= (isset($address['is_default']) && $address['is_default'] == 1) ? 'checked' : '' ?>
            >
            <label for="is_default">Set as default address</label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            <?= isset($isEdit) && $isEdit ? 'Update' : 'Save' ?> Address
        </button>
        <a href="/profile/addresses" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
    </div>
</form>

<!-- Scripts for Address Data Handling (These MUST remain in the view, below the form) -->
<script>
    // Philippine Address Data - Large JSON object kept here
    const addressData = {
        "NCR": {
            "Metro Manila": {
                "Manila": ["Binondo", "Ermita", "Intramuros", "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", "Sampaloc", "San Andres", "San Miguel", "San Nicolas", "Santa Ana", "Santa Cruz", "Santa Mesa", "Tondo"],
                "Quezon City": ["Bagong Pag-asa", "Bahay Toro", "Balingasa", "Batasan Hills", "Commonwealth", "Culiat", "Fairview", "Kamuning", "Novaliches", "Project 4", "Project 6", "Tandang Sora", "White Plains"],
                "Makati": ["Bel-Air", "Cembo", "Dasmarinas", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Magallanes", "Olympia", "Palanan", "Poblacion", "Salcedo Village", "San Lorenzo", "Urdaneta"],
                "Pasig": ["Bagong Ilog", "Bagong Katipunan", "Bambang", "Caniogan", "Kapitolyo", "Manggahan", "Maybunga", "Oranbo", "Palatiw", "Pinagbuhatan", "Rosario", "Sagad", "San Antonio", "Santolan","Santa Lucia"],
                "Las Piñas": ["Almanza Dos", "Almanza Uno", "BF International", "Daniel Fajardo", "Elias Aldana", "Ilaya", "Manuyo Dos", "Manuyo Uno", "Pamplona Dos", "Pamplona Tres", "Pamplona Uno", "Pilar", "Pulang Lupa Dos", "Pulang Lupa Uno", "Talon Dos", "Talon Kuatro", "Talon Singko", "Talon Tres", "Talon Uno", "Zapote"],
                "Taguig": ["Bagumbayan", "Bambang", "Calzada", "Central Bicutan", "Central Signal Village", "Fort Bonifacio", "Hagonoy", "Ibayo-Tipas", "Katuparan", "Ligid-Tipas", "Lower Bicutan", "Maharlika Village", "Napindan", "New Lower Bicutan", "North Daang Hari", "North Signal Village", "Palingon", "Pinagsama", "San Miguel", "Santa Ana", "South Daang Hari", "South Signal Village", "Tanyag", "Tuktukan", "Upper Bicutan", "Ususan", "Wawa", "Western Bicutan"],
                "Parañaque": ["Baclaran", "BF Homes", "Don Bosco", "Don Galo", "La Huerta", "Marcelo Green", "Merville", "Moonwalk", "San Antonio", "San Dionisio", "San Isidro", "San Martin de Porres", "Santo Niño", "Sun Valley", "Tambo", "Vitalez"]
            },
            "Region IV-A": {
                "Cavite": {
                    "Bacoor": ["Habay I", "Habay II", "Molino I", "Molino II", "Molino III", "Molino IV", "Molino V", "Molino VI", "Molino VII", "Niog I", "Niog II", "Niog III", "Panapaan I", "Panapaan II", "Panapaan III", "Panapaan IV", "Panapaan V", "Panapaan VI", "Panapaan VII", "Panapaan VIII"],
                    "Imus": ["Alapan I-A", "Alapan I-B", "Alapan I-C", "Alapan II-A", "Alapan II-B", "Anabu I-A", "Anabu I-B", "Anabu I-C", "Anabu II-A", "Anabu II-B", "Anabu II-C", "Bagong Silang", "Bayan Luma I", "Bayan Luma II", "Bayan Luma III"],
                    "Dasmariñas": ["Burol", "Emmanuel Bergado I", "Emmanuel Bergado II", "Langkaan", "Salitran I", "Salitran II", "Salitran III", "Salitran IV", "Sampaloc I", "Sampaloc II", "Sampaloc III", "Sampaloc IV", "Victoria Reyes"],
                    "Cavite City": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10"]
                },
                "Laguna": {
                    "Santa Rosa": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Kanluran", "Labas", "Macabling", "Malitlit", "Malusak", "Market Area", "Pooc", "Pulong Santa Cruz", "Sinalhan", "Tagapo"],
                    "Biñan": ["Biñan", "Bungahan", "Canlalay", "Casile", "De La Paz", "Ganado", "Langkiwa", "Loma", "Malaban", "Malamig", "Mamplasan", "Platero", "Poblacion", "San Antonio", "San Francisco", "San Jose", "San Vicente", "Santo Domingo", "Santo Niño", "Santo Tomas", "Soro-soro", "Timbao", "Tubigan", "Zapote"],
                    "Calamba": ["Bagong Kalsada", "Banlic", "Barandal", "Batino", "Bubuyan", "Bucal", "Bunggo", "Burol", "Camaligan", "Canlubang", "Halang", "Hornalan", "Kay-Anlog", "La Mesa", "Laguerta", "Lawa", "Lecheria", "Lingga", "Looc", "Mabato", "Majada Out", "Makiling", "Mapagong", "Masili", "Maunong", "Mayapa", "Milagrosa", "Paciano Rizal", "Palingon", "Palo-Alto", "Pansol", "Parian", "Prinza", "Punta", "Puting Lupa", "Real", "Saimsim", "Sampiruhan", "San Cristobal", "San Jose", "San Juan", "Sirang Lupa", "Sucol", "Turbina", "Ulango", "Uwisan"],
                    "San Pedro": ["Bagong Silang", "Calendola", "Cuyab", "Estrella", "G.S.I.S.", "Landayan", "Langgam", "Laram", "Magsaysay", "Maharlika", "Narra", "Nueva", "Pacita I", "Pacita II", "Poblacion", "Riverside", "Rosario", "Sampaguita Village", "San Antonio", "San Lorenzo Ruiz", "San Roque", "San Vicente", "Santo Niño", "United Bayanihan", "United Better Living"]
                },
                "Rizal": {
                    "Antipolo": ["Bagong Nayon", "Beverly Hills", "Calawis", "Cupang", "Dalig", "Dela Paz", "Inarawan", "Mayamot", "Muntindilaw", "San Isidro", "San Jose", "San Juan", "San Luis", "San Roque", "Santa Cruz", "Santo Niño", "Silangan"],
                    "Cainta": ["San Andres", "San Isidro", "San Juan", "San Roque", "Santo Domingo", "Santo Niño"],
                    "Taytay": ["Dolores", "Muzon", "San Isidro", "San Juan", "Santa Ana", "Santo Niño"],
                    "Angono": ["Bagumbayan", "Kalayaan", "Mahabang Parang", "Poblacion Ibaba", "Poblacion Itaas", "San Isidro", "San Pedro", "San Roque", "San Vicente", "Santo Niño"],
                    "Binangonan": ["Bangad", "Bilibiran", "Boso-Boso", "Calumpang", "Ithan", "Janosa", "Kalawaan", "Kalinawan", "Kasile", "Layunan", "Libid", "Lunsad", "Malakaban", "Malanggam", "Mambog", "Pag-Asa", "Palangoy", "Pantok", "Pila-Pila", "Pipindan", "Poblacion", "Rayap", "Libis ng Nayon", "San Carlos", "Sapang", "Tagpos", "Tatala"]
                },
                "Batangas": {
                    "Batangas City": ["Alangilan", "Balagtas", "Balete", "Banaba Center", "Banaba Ibaba", "Banaba Silangan", "Bolbok", "Conde Itaas", "Conde Labac", "Cuta", "Kumintang Ibaba", "Kumintang Ilaya", "Libjo", "Maapas", "Mabacong", "Mahabang Dahilig", "Mahabang Parang", "Malagonlong", "Malitam", "Pallocan East", "Pallocan West", "Pinamucan", "Pinamucan Ibaba", "San Agapito", "San Agustin", "San Andres", "San Antonio", "San Isidro", "San Jose", "San Miguel", "Santa Clara", "Santa Rita Aplaya", "Santa Rita Karsada", "Santo Domingo", "Santo Niño", "Simlong", "Tabangao Ambulong", "Tabangao Aplaya", "Tabangao Dao", "Talahib Pandayan", "Talahib Payapa", "Talumpok Kanluran", "Talumpok Silangan", "Tinga Itaas", "Tinga Labac", "Tulo"],
                    "Lipa": ["Adya", "Anilao", "Anilao-Labac", "Antipolo del Norte", "Antipolo del Sur", "Bagong Pook", "Balintawak", "Banaybanay", "Bolbok", "Bugtong na Pulo", "Bulacnin", "Bulaklakan", "Calamias", "Cumba", "Dagatan", "Duhatan", "Halang", "Inosloban", "Kayumanggi", "Latag", "Lodlod", "Lumbang", "Mabini", "Malagonlong", "Malitlit", "Marauoy", "Mataas na Lupa", "Munting Pulo", "Pagolingin Bata", "Pagolingin East", "Pagolingin West", "Pangao", "Pinagkawitan", "Pinagtongulan", "Plaridel", "Poblacion Barangay 1", "Poblacion Barangay 2", "Poblacion Barangay 3", "Poblacion Barangay 4", "Poblacion Barangay 5", "Poblacion Barangay 6", "Poblacion Barangay 7", "Poblacion Barangay 8", "Poblacion Barangay 9", "Poblacion Barangay 9-A", "Poblacion Barangay 10", "Poblacion Barangay 11", "Poblacion Barangay 12", "Pusil", "Quezon", "Rizal", "Sabang", "Sampaguita", "San Benito", "San Carlos", "San Celestino", "San Francisco", "San Guillermo", "San Jose", "San Lucas", "San Salvador", "San Sebastian", "Santa Catalina", "Santa Cruz", "Santo Niño", "Santo Toribio", "Sapac", "Sico", "Talisay", "Tambo", "Tangob", "Tanguay", "Tibig", "Tipacan"],
                    "Tanauan": ["Altura Bata", "Altura Matanda", "Altura-South", "Ambulong", "Bagbag", "Bagumbayan", "Balele", "Banjo East", "Banjo Laurel", "Banjo West", "Bilog-Bilog", "Boot", "Cale", "Darasa", "Gonzales", "Hernandez", "Janopol", "Janopol Oriental", "Laurel", "Luyos", "Mabini", "Malaking Pulo", "Maria Paz", "Maugat", "Montana", "Natatas", "Pagaspas", "Pantay Matanda", "Pantay na Matanda", "Poblacion Barangay 1", "Poblacion Barangay 2", "Poblacion Barangay 3", "Poblacion Barangay 4", "Poblacion Barangay 5", "Poblacion Barangay 6", "Poblacion Barangay 7", "Sambat", "San Jose", "Santor", "Santol", "Sulpoc", "Suplang", "Trapiche", "Ulango", "Wawa"]
                }
            }
        },
        "Region I": {}, 
        "Region II": {}, 
        "Region III": {}, 
        "Region IV-B": {},
        "Region V": {},
        "Region VI": {},
        "Region VII": {},
        "Region VIII": {},
        "Region IX": {},
        "Region X": {},
        "Region XI": {},
        "Region XII": {},
        "Region XIII": {},
        "CAR": {},
        "BARMM": {}
    };

    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    // Store existing values for edit mode
    const existingProvince = '<?= htmlspecialchars($address["province"] ?? "") ?>';
    const existingCity = '<?= htmlspecialchars($address["city"] ?? "") ?>';
    const existingBarangay = '<?= htmlspecialchars($address["barangay"] ?? "") ?>';

    // Function to populate select options
    function populateSelect(selectElement, options, selectedValue = '') {
        selectElement.innerHTML = '<option value="">Select ' + selectElement.name.charAt(0).toUpperCase() + selectElement.name.slice(1) + '</option>';
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
        
        if (selectedRegion && addressData[selectedRegion]) {
            const provinces = Object.keys(addressData[selectedRegion]);
            populateSelect(provinceSelect, provinces);
        }
    });

    // Province change handler
    provinceSelect.addEventListener('change', function() {
        const selectedRegion = regionSelect.value;
        const selectedProvince = this.value;
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        
        if (selectedRegion && selectedProvince && addressData[selectedRegion] && addressData[selectedRegion][selectedProvince]) {
            const cities = Object.keys(addressData[selectedRegion][selectedProvince]);
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
            addressData[selectedRegion][selectedProvince] && addressData[selectedRegion][selectedProvince][selectedCity]) {
            const barangays = addressData[selectedRegion][selectedProvince][selectedCity];
            populateSelect(barangaySelect, barangays);
        }
    });

    // Initialize selects for edit mode
    (function initAddressForm() {
        if (existingProvince || existingCity || existingBarangay) {
            if (regionSelect.value) {
                const selectedRegion = regionSelect.value;
                if (addressData[selectedRegion]) {
                    const provinces = Object.keys(addressData[selectedRegion]);
                    populateSelect(provinceSelect, provinces, existingProvince);
                    
                    setTimeout(() => {
                        if (existingProvince && addressData[selectedRegion][existingProvince]) {
                            const cities = Object.keys(addressData[selectedRegion][existingProvince]);
                            populateSelect(citySelect, cities, existingCity);
                            
                            setTimeout(() => {
                                if (existingCity && addressData[selectedRegion][existingProvince][existingCity]) {
                                    const barangays = addressData[selectedRegion][existingProvince][existingCity];
                                    populateSelect(barangaySelect, barangays, existingBarangay);
                                }
                            }, 100);
                        }
                    }, 100);
                }
            }
        }
    })();
</script>