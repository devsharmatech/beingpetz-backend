@extends('admin.layouts.master')
@section('title')
    Edit Community
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #4f46e5;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid #f3f4f6;
        padding: 1.5rem;
    }
    .page-title {
        font-weight: 700;
        color: #111827;
        font-size: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    .image-upload-wrapper {
        border: 2px dashed #d1d5db;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        background-color: #f9fafb;
        transition: all 0.2s;
        cursor: pointer;
    }
    .image-upload-wrapper:hover {
        border-color: var(--primary-color);
        background-color: #eff6ff;
    }
    .required:after {
        content: " *";
        color: #ef4444;
    }
    .community-image-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .cover-image-preview {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
    }
    .btn-primary:hover {
        background-color: var(--secondary-color);
    }
    .btn-light {
        background-color: #f3f4f6;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1 class="page-title mb-0">Edit Community: {{ $community->name }}</h1>
                        <a href="{{ route('admin.community.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i> Back to Communities
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="communityForm" action="{{ route('admin.community.update', $community->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-8">
                                    <!-- Community Name -->
                                    <div class="mb-4">
                                        <label for="name" class="form-label required">Community Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $community->name) }}"
                                            required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Community Description -->
                                    <div class="mb-4">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea id="description" name="description" rows="5" placeholder="Write Community Description"
                                            class="form-control summernote @error('description') is-invalid @enderror">{!! old('description', $community->description) !!}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Community Type -->
                                    <div class="mb-4">
                                        <label for="type" class="form-label required">Community Type</label>
                                        <select class="form-control @error('type') is-invalid @enderror" id="type"
                                            name="type" required>
                                            <option value="">Select Community Type</option>
                                            <option value="public"
                                                {{ old('type', $community->type) == 'public' ? 'selected' : '' }}>Public
                                            </option>
                                            <option value="private"
                                                {{ old('type', $community->type) == 'private' ? 'selected' : '' }}>Private
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Members Selection -->
                                    <div class="mb-4">
                                        <label for="members" class="form-label">Select Members</label>
                                        <select class="form-control select2-multiple @error('members') is-invalid @enderror"
                                            id="members" name="members[]" multiple>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ in_array($user->id, old('members', $selectedMembers)) ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('members')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('members.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="moderators" class="form-label">Moderators</label>
                                        <select class="form-select select2" id="moderators" name="moderators[]" multiple>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ in_array($user->id, $selectedModerators) ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <!-- Community Info -->
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Community Info</h6>
                                            <p class="mb-1"><strong>Created By:</strong>
                                                @if ($community->creator)
                                                    {{ $community->creator->first_name }}
                                                    {{ $community->creator->last_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                            <p class="mb-1"><strong>Created At:</strong>
                                                {{ $community->created_at->format('M d, Y h:i A') }}
                                            </p>
                                            <p class="mb-1"><strong>Total Members:</strong>
                                                {{ $community->members->count() }}
                                            </p>
                                            <p class="mb-0"><strong>Moderators:</strong>
                                                @if ($community->moderators->count() > 0)
                                                    {{ $community->moderators->count() }} moderator(s)
                                                    <div class="mt-1">
                                                        @foreach ($community->moderators as $moderator)
                                                            <small class="badge bg-warning text-dark me-1">
                                                                <i class="fas fa-user-shield me-1"></i>
                                                                {{ $moderator->user->first_name ?? 'N/A' }}
                                                            </small>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No moderators</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <!-- Profile Image -->
                                    <div class="mb-4">
                                        <label for="profile" class="form-label">Profile Image</label>

                                        <!-- Current Profile Image -->
                                        @if ($community->profile)
                                            <div class="mb-2">
                                                <p class="mb-1">Current Profile Image:</p>
                                                <img src="{{ asset($community->profile) }}" alt="Current Profile"
                                                    class="community-image-preview current-image">
                                            </div>
                                        @endif

                                        <input type="file" name="profile" id="profile" accept="image/*"
                                            class="form-control @error('profile') is-invalid @enderror">
                                        <div class="form-text">Recommended size: 300x300px</div>
                                        <div id="profilePreviewContainer" class="mt-2">
                                            <img id="profilePreview" class="community-image-preview d-none" src="#"
                                                alt="Profile Preview">
                                        </div>
                                        @error('profile')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Cover Image -->
                                    <div class="mb-4">
                                        <label for="cover_image" class="form-label">Cover Image</label>

                                        <!-- Current Cover Image -->
                                        @if ($community->cover_image)
                                            <div class="mb-2">
                                                <p class="mb-1">Current Cover Image:</p>
                                                <img src="{{ asset($community->cover_image) }}" alt="Current Cover"
                                                    class="cover-image-preview current-image">
                                            </div>
                                        @endif

                                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                                            class="form-control @error('cover_image') is-invalid @enderror">
                                        <div class="form-text">Recommended size: 1200x400px</div>
                                        <div id="coverPreviewContainer" class="mt-2">
                                            <img id="coverPreview" class="cover-image-preview d-none" src="#"
                                                alt="Cover Preview">
                                        </div>
                                        @error('cover_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Location Coordinates -->
                                    <div class="mb-4">
                                        <label class="form-label">Location Coordinates</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="number" step="any"
                                                    class="form-control @error('latitude') is-invalid @enderror"
                                                    id="latitude" name="latitude"
                                                    value="{{ old('latitude', $community->latitude) }}"
                                                    placeholder="Latitude">
                                                @error('latitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6">
                                                <input type="number" step="any"
                                                    class="form-control @error('longitude') is-invalid @enderror"
                                                    id="longitude" name="longitude"
                                                    value="{{ old('longitude', $community->longitude) }}"
                                                    placeholder="Longitude">
                                                @error('longitude')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-text">Optional: Enter latitude and longitude</div>
                                    </div>


                                </div>
                            </div>

                            <div class="action-buttons">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="reset" class="btn btn-outline-secondary" id="resetBtn">
                                        <i class="fas fa-redo me-1"></i> Reset Changes
                                    </button>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.community.index') }}" class="btn btn-light">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Community
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2, .select2-multiple').select2({
                placeholder: "Select options",
                allowClear: true
            });

            // Summernote
            $('.summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });


            // Profile image preview
            $('#profile').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profilePreview').attr('src', e.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#profilePreview').addClass('d-none');
                }
            });

            // Cover image preview
            $('#cover_image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#coverPreview').attr('src', e.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#coverPreview').addClass('d-none');
                }
            });

            // Image validation
            function validateImage(file, maxSizeMB) {
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire('Error', 'File must be an image (JPEG, JPG, PNG, GIF)', 'error');
                    return false;
                }
                if (file.size > maxSizeMB * 1024 * 1024) {
                    Swal.fire('Error', `File size must be less than ${maxSizeMB}MB`, 'error');
                    return false;
                }
                return true;
            }

            // Profile image validation
            $('#profile').change(function() {
                const file = this.files[0];
                if (file && !validateImage(file, 2)) {
                    this.value = '';
                    $('#profilePreview').addClass('d-none');
                }
            });

            // Cover image validation
            $('#cover_image').change(function() {
                const file = this.files[0];
                if (file && !validateImage(file, 2)) {
                    this.value = '';
                    $('#coverPreview').addClass('d-none');
                }
            });

            // Form validation
            $('#communityForm').on('submit', function(e) {
                let valid = true;

                // Check required fields
                const name = $('#name').val();
                const type = $('#type').val();

                if (!name.trim()) {
                    Swal.fire('Error', 'Community name is required', 'error');
                    valid = false;
                } else if (!type) {
                    Swal.fire('Error', 'Community type is required', 'error');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                }
            });

            // Reset form to original values
            $('#resetBtn').on('click', function() {
                // Reset form fields to their original values
                $('#name').val("{{ $community->name }}");
                $('#type').val("{{ $community->type }}");
                $('#latitude').val("{{ $community->latitude }}");
                $('#longitude').val("{{ $community->longitude }}");

                // Reset Select2
                const selectedMembers = @json($selectedMembers);
                $('.select2-multiple').val(selectedMembers).trigger('change');

                // Reset file inputs and previews
                $('#profile').val('');
                $('#cover_image').val('');
                $('#profilePreview').addClass('d-none');
                $('#coverPreview').addClass('d-none');

                // Reset Summernote
                $('.summernote').summernote('code', "{!! $community->description !!}");
            });
        });
    </script>
@endsection
