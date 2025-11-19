<?php
// app/Views/profile/index.view.php
?>

    <?php if (!empty($statusMessage)): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType ?? 'error') ?>">
            <strong><?= ($statusType ?? 'error') === 'success' ? '✓' : '✗' ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>


        <main class="profile-content">
            <div class="content-header">
                <h1 class="content-title">Personal Information</h1>
                <p class="content-subtitle">Manage your personal information</p>
            </div>

            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">Profile Picture</div>
                    <div class="info-value">
                        <?php if (!empty($profile['profile_pic'])): ?>
                            <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile" class="profile-pic-preview">
                        <?php else: ?>
                            <div class="profile-pic-placeholder">
                                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <button class="btn-edit" onclick="openModal('pictureModal')">
                            <i class="fas fa-camera"></i>
                            Change Photo
                        </button>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Full Name</div>
                    <div class="info-value <?= empty($profile['full_name']) ? 'empty' : '' ?>">
                        <span><?= !empty($profile['full_name']) ? htmlspecialchars($profile['full_name']) : 'Not set' ?></span>
                        <button class="btn-edit" onclick="openModal('nameModal')">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value <?= empty($profile['phone_number']) ? 'empty' : '' ?>">
                        
                        <span>
                            <?php 
                                // Format phone number inline: 09XX XXX XXXX
                                if (!empty($profile['phone_number'])) {
                                    echo htmlspecialchars(preg_replace('/^(\d{4})(\d{3})(\d{4})$/', '$1 $2 $3', $profile['phone_number']));
                                } else {
                                    echo 'Not set';
                                }
                            ?>
                        </span>
                        
                        <button class="btn-edit" onclick="openModal('phoneModal')">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <span><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></span>
                        <span class="readonly-badge">Cannot be changed</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Username</div>
                    <div class="info-value">
                        <span><?= htmlspecialchars($user['username'] ?? 'User') ?></span>
                        <span class="readonly-badge">Cannot be changed</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Gender</div>
                    <div class="info-value <?= empty($profile['gender']) ? 'empty' : '' ?>">
                        <span><?= !empty($profile['gender']) ? htmlspecialchars($profile['gender']) : 'Not set' ?></span>
                        <button class="btn-edit" onclick="openModal('genderModal')">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Birth Date</div>
                    <div class="info-value <?= empty($profile['birth_date']) ? 'empty' : '' ?>">
                        <span><?= !empty($profile['birth_date']) ? date('F d, Y', strtotime($profile['birth_date'])) : 'Not set' ?></span>
                        <button class="btn-edit" onclick="openModal('birthModal')">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="pictureModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Change Profile Picture</h2>
                <button class="modal-close" onclick="closeModal('pictureModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update" enctype="multipart/form-data">
                <input type="hidden" name="update_field" value="picture">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Photo</label>
                        <div class="file-input-wrapper">
                            <label for="profile_pic" class="file-input-button">
                                <i class="fas fa-upload"></i>
                                <span>Choose Photo</span>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <p class="file-name" id="fileName">No file chosen</p>
                        <p class="form-help">JPG, PNG, GIF or WebP. Max 5MB. Recommended: 400x400px</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('pictureModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="nameModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Full Name</h2>
                <button class="modal-close" onclick="closeModal('nameModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="name">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" placeholder="Enter your full name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('nameModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="phoneModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Phone Number</h2>
                <button class="modal-close" onclick="closeModal('phoneModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update" id="phoneForm">
                <input type="hidden" name="update_field" value="phone">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-input" value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>" placeholder="0912 345 6789" maxlength="13">
                        <p class="form-error" id="phoneError">Please enter a valid mobile number</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('phoneModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="genderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Gender</h2>
                <button class="modal-close" onclick="closeModal('genderModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="gender">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            <option value="Prefer not to say" <?= ($profile['gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('genderModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="birthModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Birth Date</h2>
                <button class="modal-close" onclick="closeModal('birthModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="birthdate">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-input" value="<?= htmlspecialchars($profile['birth_date'] ?? '') ?>" max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('birthModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Phone number validation
        const phoneInput = document.getElementById('phone_number');
        const phoneError = document.getElementById('phoneError');
        const phoneForm = document.getElementById('phoneForm');

        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length === 1 && value === '9') {
                    value = '0' + value;
                    e.target.value = value;
                } else {
                    e.target.value = value;
                }
                
                if (value.length > 11) {
                    e.target.value = value.slice(0, 11);
                }
            });

            phoneInput.addEventListener('blur', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value === '') {
                    return;
                }
                
                if (value.length === 10 && value.startsWith('9')) {
                    value = '0' + value;
                }
                
                if (value.length === 11 && value.startsWith('09')) {
                    e.target.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
                }
                
                validatePhone();
            });

            phoneInput.addEventListener('focus', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });
        }

        function validatePhone() {
            if (!phoneInput) return true;
            
            const value = phoneInput.value.replace(/\D/g, '');
            
            if (value === '') {
                phoneInput.classList.remove('error');
                phoneError.style.display = 'none';
                return true;
            }
            
            if (value.length === 11 && value.startsWith('09')) {
                phoneInput.classList.remove('error');
                phoneError.style.display = 'none';
                return true;
            } else {
                phoneInput.classList.add('error');
                phoneError.style.display = 'block';
                return false;
            }
        }

        if (phoneForm) {
            phoneForm.addEventListener('submit', function(e) {
                // Remove spaces before submitting
                let value = phoneInput.value.replace(/\D/g, '');
                
                // Validate first
                if (value && (value.length !== 11 || !value.startsWith('09'))) {
                    e.preventDefault();
                    phoneInput.focus();
                    phoneInput.classList.add('error');
                    phoneError.style.display = 'block';
                    return false;
                }
                
                // Set the clean value without spaces for submission
                phoneInput.value = value;
            });
        }
        
        window.addEventListener('DOMContentLoaded', function() {
            if (phoneInput && phoneInput.value) {
                let value = phoneInput.value.replace(/\D/g, '');
                
                if (value.length === 10 && value.startsWith('9')) {
                    value = '0' + value;
                }
                
                if (value.length === 11 && value.startsWith('09')) {
                    phoneInput.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
                }
            }
        });

        function previewImage(input) {
            const fileName = document.getElementById('fileName');
            
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
            } else {
                fileName.textContent = 'No file chosen';
            }
        }
    </script>
</body>
</html>