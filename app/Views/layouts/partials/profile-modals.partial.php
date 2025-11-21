<?php
// app/Views/layouts/partials/profile-modals.partial.php
?>

<!-- Picture Modal -->
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

<!-- Name Modal -->
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
                    <input type="text" name="full_name" class="form-input" 
                           value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" 
                           placeholder="Enter your full name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('nameModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Phone Modal -->
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
                    <input type="tel" id="phone_number" name="phone_number" class="form-input" 
                           value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>" 
                           placeholder="0912 345 6789" maxlength="13">
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

<!-- Gender Modal -->
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

<!-- Birth Date Modal -->
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
                    <input type="date" name="birth_date" class="form-input" 
                           value="<?= htmlspecialchars($profile['birth_date'] ?? '') ?>" 
                           max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('birthModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>