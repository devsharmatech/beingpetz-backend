@extends('admin.layouts.master')
@section('title')
    Edit Post
@endsection

@section('css')
    <!-- Your custom CSS keeps same, place here or in master -->
@endsection

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1 class="page-title mb-0">Edit Post</h1>
                        <a href="{{ route('admin.post.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i> Back to Posts
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

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form id="PostForm" action="{{ route('admin.post.update', $post->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-12">

                                    <!-- User (parent) dropdown -->
                                    <div class="mb-4">
                                        <label for="user_id" class="form-label required">Parent (User)</label>
                                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id"
                                            name="user_id" required>
                                            <option value="">Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ old('user_id', $post->parent_id) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Dependent Pet dropdown -->
                                    <div class="mb-4">
                                        <label for="pet_id" class="form-label required">Pet</label>
                                        <select class="form-control @error('pet_id') is-invalid @enderror" id="pet_id"
                                            name="pet_id" required>
                                            <option value="">Select Pet</option>
                                            @if (isset($pets) && $pets->count() > 0)
                                                @foreach ($pets as $pet)
                                                    <option value="{{ $pet->id }}"
                                                        {{ old('pet_id', $post->pet_id) == $pet->id ? 'selected' : '' }}>
                                                        {{ $pet->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('pet_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Post Type Selection -->
                                    <div class="mb-4">
                                        <label for="type" class="form-label required">Post Type</label>
                                        <select class="form-control @error('type') is-invalid @enderror" id="type"
                                            name="type" required>
                                            <option value="">Select post type</option>
                                            <option value="normal"
                                                {{ old('type', $post->post_type) == 'normal' ? 'selected' : '' }}>Normal
                                            </option>
                                            <option value="birthday"
                                                {{ old('type', $post->post_type) == 'birthday' ? 'selected' : '' }}>
                                                Birthday
                                            </option>
                                            <option value="repost"
                                                {{ old('type', $post->post_type) == 'repost' ? 'selected' : '' }}>Repost
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label required">Post Content</label>
                                        <textarea id="description" name="description" rows="5"
                                            class="form-control  @error('description') is-invalid @enderror">{{ old('description', $post->content) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>



                                    <!-- Multiple Images Upload -->
                                    <div class="mb-4">
                                        <label for="images" class="form-label">Add New Images</label>
                                        <input type="file" name="images[]" id="images" accept="image/*" multiple
                                            class="form-control @error('images') is-invalid @enderror">
                                        <div class="form-text">You can select multiple images (up to 2MB each). Supported
                                            formats: JPG, JPEG, PNG, GIF</div>
                                        <div id="imagePreviewContainer" class="row mt-2"></div>
                                        @error('images')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('images.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>



                                    <!-- Video Upload -->
                                    <div class="mb-4">
                                        <label for="video" class="form-label">Upload New Video</label>
                                        <input type="file" name="video" id="video" accept="video/*"
                                            class="form-control @error('video') is-invalid @enderror">
                                        <div class="form-text">Supported formats: .mp4, .3gp, .mov, .avi (max 20MB)</div>
                                        <div id="videoPreviewContainer" class="mt-2"></div>
                                        @error('video')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            <div class="action-buttons">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="reset" class="btn btn-outline-secondary" id="resetBtn">
                                        <i class="fas fa-redo me-1"></i> Reset Changes
                                    </button>
                                    <div class="d-flex gap-2">
                                        {{-- <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                            <i class="fas fa-eye me-1"></i> Preview
                                        </button> --}}
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Post
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Summernote
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Initialize pets dropdown when page loads
            function initializePetDropdown() {
                let userId = $('#user_id').val();
                let currentPetId = "{{ $post->pet_id }}";

                if (userId) {
                    $.ajax({
                        url: "{{ url('admin/get-pets-by-user') }}/" + userId,
                        type: "GET",
                        success: function(data) {
                            $('#pet_id').empty().append('<option value="">Select Pet</option>');
                            if (data.length > 0) {
                                $.each(data, function(i, pet) {
                                    $('#pet_id').append('<option value="' + pet.id + '">' + pet
                                        .name + '</option>');
                                });

                                // Select current post's pet
                                if (currentPetId) {
                                    $('#pet_id').val(currentPetId);
                                }
                            } else {
                                $('#pet_id').append('<option value="">No pets found</option>');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Could not fetch pets!', 'error');
                            $('#pet_id').empty().append('<option value="">Select Pet</option>');
                        }
                    });
                }
            }

            // Call on page load
            initializePetDropdown();

            // Dependent pet dropdown on user change
            $('#user_id').change(function() {
                let userId = $(this).val();
                $('#pet_id').empty().append('<option value="">Loading...</option>');

                if (userId) {
                    $.ajax({
                        url: "{{ url('admin/get-pets-by-user') }}/" + userId,
                        type: "GET",
                        success: function(data) {
                            $('#pet_id').empty().append('<option value="">Select Pet</option>');
                            if (data.length > 0) {
                                $.each(data, function(i, pet) {
                                    $('#pet_id').append('<option value="' + pet.id +
                                        '">' + pet.name + '</option>');
                                });
                            } else {
                                $('#pet_id').append('<option value="">No pets found</option>');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Could not fetch pets!', 'error');
                            $('#pet_id').empty().append('<option value="">Select Pet</option>');
                        }
                    });
                } else {
                    $('#pet_id').empty().append('<option value="">Select Pet</option>');
                }
            });

            // Remove existing image
            $(document).on('click', '.remove-existing-image', function() {
                let imageToRemove = $(this).data('image');
                let existingImages = JSON.parse($('#existingImages').val() || '[]');

                // Remove the image from the array
                existingImages = existingImages.filter(img => img !== imageToRemove);

                // Update the hidden input
                $('#existingImages').val(JSON.stringify(existingImages));

                // Remove the image element from DOM
                $(this).closest('.existing-image-item').remove();

                Swal.fire('Success', 'Image removed. It will be deleted when you update the post.',
                    'success');
            });

            // Remove existing video
            $('#removeVideoBtn').click(function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This video will be removed from the post!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Clear the existing video value
                        $('#existingVideo').val('');

                        // Hide the video element
                        $(this).closest('.mb-4').hide();

                        Swal.fire('Removed!', 'Video has been removed.', 'success');
                    }
                });
            });

            // Multiple images preview with validation
            $("#images").change(function() {
                $("#imagePreviewContainer").empty();
                let files = this.files;
                let valid = true;

                if (files && files.length) {
                    // Check number of files
                    if (files.length > 10) {
                        Swal.fire('Error', 'Maximum 10 images allowed!', 'error');
                        this.value = '';
                        return;
                    }

                    $.each(files, function(index, file) {
                        // Check file size
                        if (file.size > 2 * 1024 * 1024) {
                            Swal.fire('Error', 'File ' + file.name + ' is too large. Max 2MB.',
                                'error');
                            valid = false;
                            return false;
                        }

                        // Check file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            Swal.fire('Error', 'File ' + file.name +
                                ' is not a valid image format!', 'error');
                            valid = false;
                            return false;
                        }

                        let reader = new FileReader();
                        reader.onload = function(e) {
                            let img = $('<img>').attr('src', e.target.result).addClass(
                                'img-thumbnail').css({
                                maxWidth: '120px',
                                margin: '8px',
                                borderRadius: '8px'
                            });
                            $("#imagePreviewContainer").append(img);
                        };
                        reader.readAsDataURL(file);
                    });

                    if (!valid) {
                        this.value = '';
                        $("#imagePreviewContainer").empty();
                    }
                }
            });

            // Video preview with validation
            $("#video").change(function() {
                let input = this;
                $("#videoPreviewContainer").empty();

                if (input.files && input.files[0]) {
                    let file = input.files[0];

                    // Check file size
                    if (file.size > 20 * 1024 * 1024) {
                        Swal.fire('Error', 'Video file too large (max 20MB)', 'error');
                        input.value = '';
                        return;
                    }

                    // Check file type
                    const validTypes = ['video/mp4', 'video/3gp', 'video/quicktime', 'video/x-msvideo'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire('Error', 'Invalid video format! Supported: MP4, 3GP, MOV, AVI', 'error');
                        input.value = '';
                        return;
                    }

                    let reader = new FileReader();
                    reader.onload = function(e) {
                        let video = $('<video controls width="200" class="img-thumbnail">').attr('src',
                            e.target.result);
                        $("#videoPreviewContainer").append(video);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Form validation before submit
            // $('#PostForm').on('submit', function(e) {
            //     const existingImages = $('#existingImages').val();
            //     const newImages = $('#images')[0].files;
            //     const existingVideo = $('#existingVideo').val();
            //     const newVideo = $('#video')[0].files;

            //     // Check if there's at least some content (either images OR video OR both)
            //     const hasImages = (existingImages && JSON.parse(existingImages).length > 0) ||
            //         (newImages && newImages.length > 0);
            //     const hasVideo = existingVideo || (newVideo && newVideo.length > 0);

            //     if (!hasImages && !hasVideo) {
            //         Swal.fire('Error', 'Please add at least one image or video!', 'error');
            //         e.preventDefault();
            //         return false;
            //     }

            //     return true;
            // });

            // Reset form
            $('#resetBtn').on('click', function() {
                // Reset to original post values
                $('.summernote').summernote('code', `{!! addslashes($post->description) !!}`);

                // Clear new file inputs
                $('#images').val('');
                $('#video').val('');

                // Clear previews
                $("#imagePreviewContainer").empty();
                $("#videoPreviewContainer").empty();

                // Restore existing images and video
                $('#existingImages').val(JSON.stringify(@json($post->images ?? [])));
                $('#existingVideo').val("{{ $post->video ?? '' }}");

                // Show all existing images again
                $('.existing-image-item').show();
                $('.mb-4').show();

                // Reset dropdowns
                $('#user_id').val("{{ $post->user_id }}");
                initializePetDropdown();

                Swal.fire('Reset', 'Form has been reset to original values.', 'info');
            });

            // Preview button handler
            $('#previewBtn').on('click', function() {
                Swal.fire('Info', 'Preview functionality to be implemented!', 'info');
            });
        });
    </script>
@endsection
