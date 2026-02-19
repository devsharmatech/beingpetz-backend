@extends('admin.layouts.master')
@section('title')
    Profile
@endsection


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet"
        type="text/css" />
    <style>
        .property-type-img {
            max-width: 80px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .swal2-toast {
            font-size: 12px !important;
            padding: 6px 10px !important;
            min-width: auto !important;
            width: 220px !important;
            line-height: 1.3em !important;
        }

        .swal2-toast .swal2-icon {
            width: 24px !important;
            height: 24px !important;
            margin-right: 6px !important;
        }

        .swal2-toast .swal2-title {
            font-size: 13px !important;
        }
    </style>



@section('content')
    <style>
        :root {
            --primary-color: rgb(131, 55, 178);
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
        }

        .small-toast {
            font-size: 12px;
            /* ya jitna chhota chahiye */
        }


        body {
            background-color: #f5f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .profile-card {
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
            background: white;
        }

        .profile-sidebar {
            background: linear-gradient(to bottom, rgb(131, 55, 178), #fcfdfe);
            color: white;
            padding: 30px;
            text-align: center;
            height: 100%;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            margin: 0 auto 20px;
            display: block;
        }

        .profile-avatar-edit {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .avatar-edit-icon {
            position: absolute;
            bottom: 20px;
            right: 5px;
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
        }

        .profile-details {
            padding: 30px;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-role {
            color: var(--secondary-color);
            margin-bottom: 25px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 125, 221, 0.25);
        }

        .profile-section {
            background-color: white;
            border-radius: 10px;
            padding: 21px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #2d6ac1;
            border-color: #2d6ac1;
        }

        .btn-outline-secondary {
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
        }

        .sidebar-stats {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item {
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .password-toggle {
            cursor: pointer;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Tab Styles */
        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            /* margin-bottom: 25px; */
        }

        .nav-tabs .nav-item {
            margin-bottom: -2px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--secondary-color);
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 8px 8px 0 0;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color);
            background-color: rgba(82, 92, 229, 0.05);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: white;
            border-bottom: 3px solid var(--primary-color);
            font-weight: 600;
        }

        .tab-content {
            padding: 0;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 992px) {
            .profile-sidebar {
                padding: 20px;
            }

            .profile-avatar {
                width: 120px;
                height: 120px;
            }

            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .profile-sidebar {
                text-align: center;
                padding: 30px 20px;
            }

            .profile-details {
                padding: 25px 20px;
            }

            .nav-tabs {
                flex-direction: column;
                border-bottom: none;
            }

            .nav-tabs .nav-item {
                margin-bottom: 5px;
                width: 100%;
            }

            .nav-tabs .nav-link {
                border-radius: 6px;
                text-align: center;
                border: 1px solid var(--border-color);
            }

            .nav-tabs .nav-link.active {
                border-bottom: 3px solid var(--primary-color);
            }
        }
    </style>

    <div class="container-fluid profile-container py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Profile Update</h2>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div id="messageContainer"></div>

        <div class="row">
            <div class="col-12">
                <div class="card profile-card">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Left Sidebar with Image -->
                            <div class="col-lg-4 col-md-5 profile-sidebar">
                                <div class="profile-avatar-edit mb-3">
                                    <img src="{{ $user->profile ? asset($user->profile) : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=500&q=80' }}"
                                        alt="Avatar" class="profile-avatar" id="avatarPreview">
                                    <div class="avatar-edit-icon" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>

                                <h3 class="profile-name">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                <p class="profile-role"><span class="text-black">Role - {{ $user->role }}</span> </p>

                                <div class="sidebar-stats">
                                    <div class="stat-item">
                                        <div class="text-black">{{ $user->locality }} </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side with Tabs and Form -->
                            <div class="col-lg-8 col-md-7 profile-details">
                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#profile" type="button" role="tab" aria-controls="profile"
                                            aria-selected="true">
                                            <i class="fas fa-user me-2"></i>Profile Information
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab"
                                            data-bs-target="#password" type="button" role="tab"
                                            aria-controls="password" aria-selected="false">
                                            <i class="fas fa-lock me-2"></i>Change Password
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="profileTabsContent">
                                    <!-- Profile Tab -->
                                    <div class="tab-pane fade show active" id="profile" role="tabpanel"
                                        aria-labelledby="profile-tab">
                                        <form id="profileForm">
                                            @csrf
                                            <!-- Personal Information Section -->
                                            <div class="profile-section">

                                                {{-- <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="name" class="form-label">Full Name </label>
                                                        <input type="text" class="form-control" id="name"
                                                            name="name" value="{{ $user->first_name }}" required>
                                                        <div class="invalid-feedback" id="nameError"></div>
                                                    </div>
                                                </div> --}}

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="first_name" class="form-label">First Name </label>
                                                        <input type="text" class="form-control" id="first_name"
                                                            name="first_name" value="{{ $user->first_name }}" required>
                                                        <div class="invalid-feedback" id="nameError"></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="last_name" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="last_name"
                                                            name="last_name" value="{{ $user->last_name }}">
                                                        <div class="invalid-feedback" id="phoneError"></div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="email" class="form-label">Email Address </label>
                                                        <input type="email" class="form-control" id="email" readonly
                                                            name="email" value="{{ $user->email }}">
                                                        <div class="invalid-feedback" id="emailError"></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="phone" class="form-label">Phone Number</label>
                                                        <input type="tel" class="form-control" id="phone"
                                                            name="phone" value="{{ $user->phone }}">
                                                        <div class="invalid-feedback" id="phoneError"></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="role" class="form-label">City</label>
                                                        <input type="text" class="form-control" id="city"
                                                            name="city" value="{{ $user->city }}">
                                                        <div class="invalid-feedback" id="emailError"></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="state" class="form-label">State</label>
                                                        <input type="text" class="form-control" id="state"
                                                            name="state" value="{{ $user->state }}">
                                                        <div class="invalid-feedback" id="phoneError"></div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="locality" class="form-label">Locality</label>
                                                    <textarea class="form-control" id="locality" name="locality" rows="3">{{ $user->locality }}</textarea>
                                                    <div class="invalid-feedback" id="bioError"></div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    id="cancelBtn">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                    Update Profile
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Password Tab -->
                                    <div class="tab-pane fade" id="password" role="tabpanel"
                                        aria-labelledby="password-tab">
                                        <form id="passwordForm">
                                            @csrf
                                            <!-- Password Change Section -->
                                            <div class="profile-section">

                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="current_password" class="form-label">Current Password
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control"
                                                                id="current_password" name="current_password" required>
                                                            <span class="input-group-text password-toggle">
                                                                <i class="fas fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                        <div class="invalid-feedback" id="currentPasswordError"></div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="new_password" class="form-label">New Password </label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control" id="new_password"
                                                                name="new_password" required>
                                                            <span class="input-group-text password-toggle">
                                                                <i class="fas fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                        <div class="invalid-feedback" id="newPasswordError"></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="new_password_confirmation" class="form-label">Confirm
                                                            New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control"
                                                                id="new_password_confirmation"
                                                                name="new_password_confirmation" required>
                                                            <span class="input-group-text password-toggle">
                                                                <i class="fas fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                        <div class="invalid-feedback" id="confirmPasswordError"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    id="passwordCancelBtn">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="passwordSubmitBtn">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                    Change Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarModalLabel">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Choose an image</label>
                        <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*">
                        <div class="invalid-feedback" id="avatarError"></div>
                    </div>
                    <div class="text-center">
                        <img src="{{ $user->profile ? asset($user->profile) : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=500&q=80' }}"
                            alt="Avatar Preview" class="img-fluid rounded" id="modalAvatarPreview"
                            style="max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAvatarBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tabs
            const triggerTabList = [].slice.call(document.querySelectorAll('#profileTabs button'));
            triggerTabList.forEach(function(triggerEl) {
                new bootstrap.Tab(triggerEl);
            });

            // Elements
            const profileForm = document.getElementById('profileForm');
            const passwordForm = document.getElementById('passwordForm');
            const messageContainer = document.getElementById('messageContainer');
            const submitBtn = document.getElementById('submitBtn');
            const passwordSubmitBtn = document.getElementById('passwordSubmitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const passwordCancelBtn = document.getElementById('passwordCancelBtn');
            const avatarUpload = document.getElementById('avatar');
            const modalAvatarPreview = document.getElementById('modalAvatarPreview');
            const mainAvatarPreview = document.getElementById('avatarPreview');
            const saveAvatarBtn = document.getElementById('saveAvatarBtn');
            const avatarModal = document.getElementById('avatarModal');
            const modalInstance = new bootstrap.Modal(avatarModal);

            // Password visibility toggle
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    const input = this.parentElement.querySelector('input');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            });

            // Avatar preview in modal
            avatarUpload.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file type and size
                    const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (!validImageTypes.includes(file.type)) {
                        showMessage('Please select a valid image file (JPEG, PNG, GIF).', 'danger');
                        this.value = '';
                        return;
                    }

                    if (file.size > maxSize) {
                        showMessage('Image size should be less than 2MB.', 'danger');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        modalAvatarPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Save avatar button
            saveAvatarBtn.addEventListener('click', function() {
                const file = avatarUpload.files[0];
                if (!file) {
                    showMessage('Please select an image first.', 'danger');
                    return;
                }

                const formData = new FormData();
                formData.append('avatar', file);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading state
                saveAvatarBtn.disabled = true;
                saveAvatarBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';

                fetch('{{ route('admin.profile.update-avatar') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mainAvatarPreview.src = data.avatar_url;
                            modalInstance.hide();
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Profile picture updated successfully!',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'small-toast'
                                }
                            });

                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            showMessage(data.message || 'Error updating profile picture.', 'danger');
                        }
                    })
                    .catch(error => {
                        showMessage('An error occurred. Please try again.', 'danger');
                    })
                    .finally(() => {
                        saveAvatarBtn.disabled = false;
                        saveAvatarBtn.innerHTML = 'Save Changes';
                    });
            });

            // Profile Form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                clearErrors();

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.querySelector('.spinner-border').classList.remove('d-none');

                // Create FormData object
                const formData = new FormData(this);

                // Send AJAX request
                fetch('{{ route('admin.profile.update') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Profile updated successfully!',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'small-toast'
                                }
                            });

                            // Refresh page after 2 sec
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            // Display validation errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(field => {
                                    const errorElement = document.getElementById(field +
                                        'Error');
                                    if (errorElement) {
                                        const input = document.getElementById(field);
                                        if (input) {
                                            input.classList.add('is-invalid');
                                        }
                                        errorElement.textContent = data.errors[field][0];
                                    }
                                });
                                Swal.fire({
                                    toast: true,
                                    icon: 'error',
                                    title: 'Please correct the errors in the form.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'small-toast'
                                    }
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    icon: 'error',
                                    title: data.message || 'Error updating profile.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'small-toast'
                                    }
                                });
                            }
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: 'An error occurred. Please try again.',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            customClass: {
                                popup: 'small-toast'
                            }
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.querySelector('.spinner-border').classList.add('d-none');
                    });
            });

            // Password Form submission
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                clearErrors();

                // Validate password confirmation
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('new_password_confirmation').value;

                if (newPassword !== confirmPassword) {
                    document.getElementById('new_password').classList.add('is-invalid');
                    document.getElementById('new_password_confirmation').classList.add('is-invalid');
                    document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';

                    Swal.fire({
                        toast: true,
                        icon: 'error',
                        title: 'Passwords do not match.',
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        customClass: {
                            popup: 'small-toast'
                        }
                    });
                    return;
                }

                // Show loading state
                passwordSubmitBtn.disabled = true;
                passwordSubmitBtn.querySelector('.spinner-border').classList.remove('d-none');

                // Create FormData object
                const formData = new FormData(this);

                // Send AJAX request
                fetch('{{ route('admin.profile.change-password') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Password updated successfully!',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'small-toast'
                                }
                            });

                            // Clear form and reset validation
                            passwordForm.reset();
                            clearErrors();
                        } else {
                            // Display validation errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(field => {
                                    const errorElement = document.getElementById(field +
                                        'Error');
                                    if (errorElement) {
                                        const input = document.getElementById(field);
                                        if (input) {
                                            input.classList.add('is-invalid');
                                        }
                                        errorElement.textContent = data.errors[field][0];
                                    }
                                });
                                Swal.fire({
                                    toast: true,
                                    icon: 'error',
                                    title: 'Please correct the errors in the form.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'small-toast'
                                    }
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    icon: 'error',
                                    title: data.message || 'Error changing password.',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    customClass: {
                                        popup: 'small-toast'
                                    }
                                });
                            }
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: 'An error occurred. Please try again.',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            customClass: {
                                popup: 'small-toast'
                            }
                        });
                    })
                    .finally(() => {
                        passwordSubmitBtn.disabled = false;
                        passwordSubmitBtn.querySelector('.spinner-border').classList.add('d-none');
                    });
            });

            // Cancel buttons
            cancelBtn.addEventListener('click', function() {
                window.location.reload();
            });

            passwordCancelBtn.addEventListener('click', function() {
                passwordForm.reset();
                clearErrors();
            });

            // Helper function to show messages
            function showMessage(message, type) {
                messageContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    const alert = messageContainer.querySelector('.alert');
                    if (alert) {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }
                }, 5000);
            }

            // Helper function to clear validation errors
            function clearErrors() {
                const errorElements = document.querySelectorAll('.invalid-feedback');
                errorElements.forEach(element => {
                    element.textContent = '';
                });

                const invalidInputs = document.querySelectorAll('.is-invalid');
                invalidInputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });
            }
        });
    </script>
@endsection
