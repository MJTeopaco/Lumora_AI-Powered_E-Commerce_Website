<?php
// app/Views/layouts/profile/address-form.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($isEdit) && $isEdit ? 'Edit' : 'Add' ?> Address - Lumora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #1f2937;
        }

        .header {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .back-link {
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .alert {
            max-width: 1400px;
            margin: 1.5rem auto;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .profile-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .profile-sidebar {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: sticky;
            top: 100px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .sidebar-avatar-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
        }

        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: #ffffff;
            border: 3px solid #e5e7eb;
            overflow: hidden;
        }

        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-username {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .sidebar-email {
            font-size: 13px;
            color: #6b7280;
        }

        .sidebar-menu {
            padding: 0.5rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .menu-item:hover {
            background-color: #f9fafb;
            color: #1e4d3d;
        }

        .menu-item.active {
            background-color: #f0f5f3;
            color: #1e4d3d;
            border-left-color: #2d5a4a;
            font-weight: 600;
        }

        .menu-item i {
            width: 20px;
            font-size: 18px;
        }

        .menu-item span {
            font-size: 14px;
        }

        .menu-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.5rem 0;
        }

        .menu-item.logout {
            color: #ef4444;
        }

        .menu-item.logout:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .profile-content {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .content-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .content-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
            font-family: inherit;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #2d5a4a;
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #2d5a4a;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1e4d3d;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        @media (max-width: 1024px) {
            .profile-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .profile-sidebar {
                position: relative;
                top: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 0 1rem;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
            }

            .profile-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <a href="/profile/addresses" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Addresses
            </a>
        </nav>
    </header>

    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <strong><?= $statusType === 'success' ? '✓' : '✗' ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar-container">
                    <div class="sidebar-avatar">
                        <?php if (!empty($profile['profile_pic'])): ?>
                            <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Picture">
                        <?php else: ?>
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($user['username']) ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <nav class="sidebar-menu">
                <a href="/profile" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Personal Information</span>
                </a>
                <a href="/profile/addresses" class="menu-item active">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>My Addresses</span>
                </a>
                <a href="/profile/orders" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>My Orders</span>
                </a>
                <a href="/profile/settings" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Account Settings</span>
                </a>
                
                <div class="menu-divider"></div>
                
                <form method="POST" action="/logout" style="margin: 0;">
                    <button type="submit" class="menu-item logout" style="background: none; border: none; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <main class="profile-content">
            <div class="content-header">
                <h1 class="content-title"><?= isset($isEdit) && $isEdit ? 'Edit' : 'Add New' ?> Address</h1>
                <p class="content-subtitle">Please fill in your complete delivery address</p>
            </div>

            <form method="POST" action="<?= isset($isEdit) && $isEdit ? '/profile/addresses/edit/' . $address['address_id'] : '/profile/addresses/add' ?>" id="addressForm">
                <div class="form-group">
                    <label class="form-label">
                        Address Line 1 <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="address_line_1" 
                        class="form-input" 
                        placeholder="House No., Building, Street Name"
                        value="<?= isset($address['address_line_1']) ? htmlspecialchars($address['address_line_1']) : '' ?>"
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
                    ><?= isset($address['address_line_2']) ? htmlspecialchars($address['address_line_2']) : '' ?></textarea>
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
                            value="<?= isset($address['postal_code']) ? htmlspecialchars($address['postal_code']) : '' ?>"
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
        </main>
    </div>

    <footer>
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>

    <script>
        // Philippine Address Data
        const addressData = {
            "NCR": {
                "Metro Manila": {
                    "Manila": ["Binondo", "Ermita", "Intramuros", "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", "Sampaloc", "San Andres", "San Miguel", "San Nicolas", "Santa Ana", "Santa Cruz", "Santa Mesa", "Tondo"],
                    "Quezon City": ["Bagong Pag-asa", "Bahay Toro", "Balingasa", "Batasan Hills", "Commonwealth", "Culiat", "Fairview", "Kamuning", "Novaliches", "Project 4", "Project 6", "Tandang Sora", "White Plains"],
                    "Makati": ["Bel-Air", "Cembo", "Dasmarinas", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Magallanes", "Olympia", "Palanan", "Poblacion", "Salcedo Village", "San Lorenzo", "Urdaneta"],
                    "Pasig": ["Bagong Ilog", "Bagong Katipunan", "Bambang", "Caniogan", "Kapitolyo", "Manggahan", "Maybunga", "Oranbo", "Palatiw", "Pinagbuhatan", "Rosario", "Sagad", "San Antonio", "Santolan"],
                    "Las Piñas": ["Almanza Dos", "Almanza Uno", "BF International", "Daniel Fajardo", "Elias Aldana", "Ilaya", "Manuyo Dos", "Manuyo Uno", "Pamplona Dos", "Pamplona Tres", "Pamplona Uno", "Pilar", "Pulang Lupa Dos", "Pulang Lupa Uno", "Talon Dos", "Talon Kuatro", "Talon Singko", "Talon Tres", "Talon Uno", "Zapote"],
                    "Taguig": ["Bagumbayan", "Bambang", "Calzada", "Central Bicutan", "Central Signal Village", "Fort Bonifacio", "Hagonoy", "Ibayo-Tipas", "Katuparan", "Ligid-Tipas", "Lower Bicutan", "Maharlika Village", "Napindan", "New Lower Bicutan", "North Daang Hari", "North Signal Village", "Palingon", "Pinagsama", "San Miguel", "Santa Ana", "South Daang Hari", "South Signal Village", "Tanyag", "Tuktukan", "Upper Bicutan", "Ususan", "Wawa", "Western Bicutan"],
                    "Parañaque": ["Baclaran", "BF Homes", "Don Bosco", "Don Galo", "La Huerta", "Marcelo Green", "Merville", "Moonwalk", "San Antonio", "San Dionisio", "San Isidro", "San Martin de Porres", "Santo Niño", "Sun Valley", "Tambo", "Vitalez"]
                }
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
        };

        const regionSelect = document.getElementById('region');
        const provinceSelect = document.getElementById('province');
        const citySelect = document.getElementById('city');
        const barangaySelect = document.getElementById('barangay');

        // Store existing values for edit mode
        const existingProvince = '<?= isset($address["province"]) ? htmlspecialchars($address["province"]) : "" ?>';
        const existingCity = '<?= isset($address["city"]) ? htmlspecialchars($address["city"]) : "" ?>';
        const existingBarangay = '<?= isset($address["barangay"]) ? htmlspecialchars($address["barangay"]) : "" ?>';

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
            
            if (selectedRegion && selectedProvince && addressData[selectedRegion][selectedProvince]) {
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
                addressData[selectedRegion][selectedProvince][selectedCity]) {
                const barangays = addressData[selectedRegion][selectedProvince][selectedCity];
                populateSelect(barangaySelect, barangays);
            }
        });

        // Initialize selects for edit mode
        if (existingProvince || existingCity || existingBarangay) {
            // Trigger region change to populate provinces
            if (regionSelect.value) {
                const selectedRegion = regionSelect.value;
                if (addressData[selectedRegion]) {
                    const provinces = Object.keys(addressData[selectedRegion]);
                    populateSelect(provinceSelect, provinces, existingProvince);
                    
                    // Wait a bit then populate cities
                    setTimeout(() => {
                        if (existingProvince && addressData[selectedRegion][existingProvince]) {
                            const cities = Object.keys(addressData[selectedRegion][existingProvince]);
                            populateSelect(citySelect, cities, existingCity);
                            
                            // Wait a bit then populate barangays
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
    </script>
</body>
</html>