@extends('admin.layouts.master')
@section('title')
    Post Add
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
                        <h1 class="page-title mb-0">Create New Post</h1>
                        <a href="{{ route('admin.post.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i> Back to Post
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

                        <form id="PostForm" action="{{ route('admin.post.save_post') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
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
                                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                            @if (old('user_id'))
                                                <!-- Populated via AJAX or old data -->
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
                                            <option value="normal" {{ old('type') == 'normal' ? 'selected' : '' }}>Normal
                                            </option>
                                            <option value="birthday" {{ old('type') == 'birthday' ? 'selected' : '' }}>
                                                Birthday</option>
                                            <option value="repost" {{ old('type') == 'repost' ? 'selected' : '' }}>Repost
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>



                                    <div class="mb-4">
                                        <label for="description" class="form-label required">Post Content</label>
                                        <textarea id="description" name="description" rows="5"
                                            class="form-control  @error('description') is-invalid @enderror" placeholder="Write Post Content">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Multiple Images -->
                                    <div class="mb-4">
                                        <label for="images" class="form-label">Post Images</label>
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
                                        <label for="video" class="form-label">Video File</label>
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
                                        <i class="fas fa-redo me-1"></i> Reset
                                    </button>
                                    <div class="d-flex gap-2">
                                        {{-- <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                            <i class="fas fa-eye me-1"></i> Preview
                                        </button> --}}
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Post
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

            // Dependent pet dropdown
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

                                // Select old value if exists
                                let oldPetId = "{{ old('pet_id') }}";
                                if (oldPetId) {
                                    $('#pet_id').val(oldPetId);
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
                } else {
                    $('#pet_id').empty().append('<option value="">Select Pet</option>');
                }
            });

            // Trigger user change on page load if old user_id exists
            @if (old('user_id'))
                $('#user_id').trigger('change');
            @endif





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
            $('#PostForm').on('submit', function(e) {
                const images = $('#images')[0].files;
                const video = $('#video')[0].files;

                // Basic validation - you can add more as needed
                if (!images || images.length === 0) {
                    Swal.fire('Error', 'Please select at least one image!', 'error');
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Reset form
            $('#resetBtn').on('click', function() {
                $('.summernote').summernote('code', '');
                $("#imagePreviewContainer").empty();
                $("#videoPreviewContainer").empty();
                // Reset file inputs
                $('#images').val('');
                $('#video').val('');
            });

            // Preview button handler (you can implement modal preview)
            $('#previewBtn').on('click', function() {
                Swal.fire('Info', 'Preview functionality to be implemented!', 'info');
            });
        });
    </script>
@endsection
