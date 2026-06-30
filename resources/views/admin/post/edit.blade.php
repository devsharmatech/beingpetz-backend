@extends('admin.layouts.master')
@section('title')
    Edit Post
@endsection

@section('css')
    <!-- Your custom CSS keeps same, place here or in master -->
@endsection
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
            <!-- Post Type Selection -->
            <div class="mb-4">
                <label for="type" class="form-label required">Post Type</label>
                <select class="form-control @error('type') is-invalid @enderror" id="type"
                    name="type" required>
                    <option value="">Select post type</option>
                    <option value="normal" {{ old('type', $post->post_type) == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="birthday" {{ old('type', $post->post_type) == 'birthday' ? 'selected' : '' }}>Birthday</option>
                    <option value="repost" {{ old('type', $post->post_type) == 'repost' ? 'selected' : '' }}>Repost</option>
                    <option value="sponsored" {{ old('type', $post->post_type) == 'sponsored' ? 'selected' : '' }}>Sponsored</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- 🎯 Sponsored Targeting Section -->
            <div id="sponsoredFields" style="display:none;">

                <div class="mb-4">
                    <label class="form-label">Target All Users</label>
                    <select name="target_all" class="form-control @error('target_all') is-invalid @enderror">
                        <option value="0" {{ old('target_all', $post->target_all ?? '0') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('target_all', $post->target_all ?? '0') == '1' ? 'selected' : '' }}>Yes (Show to everyone)</option>
                    </select>
                    @error('target_all')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Target Locations</label>
                    <input type="text" name="target_locations[]" 
                        class="form-control @error('target_locations') is-invalid @enderror" 
                        data-role="tagsinput" 
                        placeholder="e.g. Delhi, Noida" 
                        value="{{ old('target_locations') ? implode(',', old('target_locations')) : ($post->target_locations ? implode(',', $post->target_locations) : '') }}">
                    @error('target_locations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Target Pet Types</label>
                    <select name="target_pet_types[]" class="form-control @error('target_pet_types') is-invalid @enderror" multiple>
                        <option value="dog" {{ in_array('dog', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Dog</option>
                        <option value="cat" {{ in_array('cat', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Cat</option>
                        <option value="rabbit" {{ in_array('rabbit', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Rabbit</option>
                        <option value="bird" {{ in_array('bird', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Bird</option>
                        <option value="fish" {{ in_array('fish', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Fish</option>
                        <option value="hamster" {{ in_array('hamster', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Hamster</option>
                        <option value="guinea_pig" {{ in_array('guinea_pig', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Guinea Pig</option>
                        <option value="turtle" {{ in_array('turtle', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Turtle</option>
                        <option value="snake" {{ in_array('snake', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Snake</option>
                        <option value="lizard" {{ in_array('lizard', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Lizard</option>
                        <option value="horse" {{ in_array('horse', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Horse</option>
                        <option value="parrot" {{ in_array('parrot', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Parrot</option>
                        <option value="ferret" {{ in_array('ferret', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Ferret</option>
                        <option value="goat" {{ in_array('goat', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Goat</option>
                        <option value="cow" {{ in_array('cow', old('target_pet_types', $post->target_pet_types ?? [])) ? 'selected' : '' }}>Cow</option>
                    </select>
                    @error('target_pet_types')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Target Breeds</label>
                    <select name="target_breeds[]" id="breedsSelect" class="form-control @error('target_breeds') is-invalid @enderror" multiple>

                        <!-- 🐶 DOG BREEDS -->
                        <optgroup label="Dog">
                            <option value="labrador" {{ in_array('labrador', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Labrador</option>
                            <option value="german_shepherd" {{ in_array('german_shepherd', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>German Shepherd</option>
                            <option value="golden_retriever" {{ in_array('golden_retriever', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Golden Retriever</option>
                            <option value="pug" {{ in_array('pug', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Pug</option>
                            <option value="beagle" {{ in_array('beagle', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Beagle</option>
                            <option value="rottweiler" {{ in_array('rottweiler', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Rottweiler</option>
                            <option value="doberman" {{ in_array('doberman', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Doberman</option>
                            <option value="husky" {{ in_array('husky', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Siberian Husky</option>
                            <option value="shih_tzu" {{ in_array('shih_tzu', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Shih Tzu</option>
                            <option value="boxer" {{ in_array('boxer', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Boxer</option>
                            <option value="dachshund" {{ in_array('dachshund', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Dachshund</option>
                            <option value="great_dane" {{ in_array('great_dane', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Great Dane</option>
                            <option value="pomeranian" {{ in_array('pomeranian', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Pomeranian</option>
                            <option value="chihuahua" {{ in_array('chihuahua', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Chihuahua</option>
                            <option value="bulldog" {{ in_array('bulldog', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Bulldog</option>
                            <option value="pitbull" {{ in_array('pitbull', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Pitbull</option>
                            <option value="indian_spitz" {{ in_array('indian_spitz', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Indian Spitz</option>
                        </optgroup>

                        <!-- 🐱 CAT BREEDS -->
                        <optgroup label="Cat">
                            <option value="persian" {{ in_array('persian', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Persian</option>
                            <option value="siamese" {{ in_array('siamese', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Siamese</option>
                            <option value="maine_coon" {{ in_array('maine_coon', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Maine Coon</option>
                            <option value="ragdoll" {{ in_array('ragdoll', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Ragdoll</option>
                            <option value="british_shorthair" {{ in_array('british_shorthair', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>British Shorthair</option>
                            <option value="bengal" {{ in_array('bengal', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Bengal</option>
                            <option value="sphynx" {{ in_array('sphynx', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Sphynx</option>
                            <option value="abyssinian" {{ in_array('abyssinian', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Abyssinian</option>
                            <option value="scottish_fold" {{ in_array('scottish_fold', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Scottish Fold</option>
                            <option value="american_shorthair" {{ in_array('american_shorthair', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>American Shorthair</option>
                        </optgroup>

                        <!-- 🐰 RABBIT BREEDS -->
                        <optgroup label="Rabbit">
                            <option value="holland_lop" {{ in_array('holland_lop', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Holland Lop</option>
                            <option value="mini_rex" {{ in_array('mini_rex', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Mini Rex</option>
                            <option value="lionhead" {{ in_array('lionhead', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Lionhead</option>
                            <option value="flemish_giant" {{ in_array('flemish_giant', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Flemish Giant</option>
                            <option value="english_angora" {{ in_array('english_angora', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>English Angora</option>
                            <option value="netherland_dwarf" {{ in_array('netherland_dwarf', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Netherland Dwarf</option>
                            <option value="californian" {{ in_array('californian', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Californian Rabbit</option>
                            <option value="rex" {{ in_array('rex', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Rex Rabbit</option>
                        </optgroup>

                        <!-- 🐦 BIRD BREEDS -->
                        <optgroup label="Bird">
                            <option value="parakeet" {{ in_array('parakeet', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Parakeet</option>
                            <option value="cockatiel" {{ in_array('cockatiel', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Cockatiel</option>
                            <option value="lovebird" {{ in_array('lovebird', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Lovebird</option>
                            <option value="macaw" {{ in_array('macaw', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Macaw</option>
                            <option value="canary" {{ in_array('canary', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Canary</option>
                            <option value="budgerigar" {{ in_array('budgerigar', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Budgerigar</option>
                            <option value="african_grey" {{ in_array('african_grey', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>African Grey</option>
                            <option value="finch" {{ in_array('finch', old('target_breeds', $post->target_breeds ?? [])) ? 'selected' : '' }}>Finch</option>
                        </optgroup>

                    </select>
                    @error('target_breeds')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- User (parent) dropdown -->
            <div class="mb-4">
                <label for="user_id" class="form-label required">Parent (User)</label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="user_id"
                    name="user_id" >
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
                    name="pet_id" >
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

            <div class="mb-4">
                <label for="description" class="form-label required">Post Content</label>
                <textarea id="description" name="description" rows="5"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Write Post Content">{{ old('description', $post->content) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- 🎉 Post Enhancements -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Feeling</label>
                    <select name="feeling" class="form-control @error('feeling') is-invalid @enderror">
                        <option value="">Select Feeling</option>
                        <option value="happy" {{ old('feeling', $post->feeling ?? '') == 'happy' ? 'selected' : '' }}>Happy</option>
                        <option value="excited" {{ old('feeling', $post->feeling ?? '') == 'excited' ? 'selected' : '' }}>Excited</option>
                        <option value="cuddly" {{ old('feeling', $post->feeling ?? '') == 'cuddly' ? 'selected' : '' }}>Cuddly</option>
                        <option value="sleepy" {{ old('feeling', $post->feeling ?? '') == 'sleepy' ? 'selected' : '' }}>Sleepy</option>
                        <option value="silly" {{ old('feeling', $post->feeling ?? '') == 'silly' ? 'selected' : '' }}>Silly</option>
                        <option value="playful" {{ old('feeling', $post->feeling ?? '') == 'playful' ? 'selected' : '' }}>Playful</option>
                        <option value="relaxed" {{ old('feeling', $post->feeling ?? '') == 'relaxed' ? 'selected' : '' }}>Relaxed</option>
                    </select>
                    @error('feeling')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Activity</label>
                    <select name="activity" class="form-control @error('activity') is-invalid @enderror">
                        <option value="">Select Activity</option>
                        <option value="eating" {{ old('activity', $post->activity ?? '') == 'eating' ? 'selected' : '' }}>Eating</option>
                        <option value="training" {{ old('activity', $post->activity ?? '') == 'training' ? 'selected' : '' }}>Training</option>
                        <option value="adventure" {{ old('activity', $post->activity ?? '') == 'adventure' ? 'selected' : '' }}>Adventure</option>
                        <option value="birthday" {{ old('activity', $post->activity ?? '') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                    </select>
                    @error('activity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
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

            <!-- Existing Images Display -->
            @if ($post->images && $post->images->count() > 0)
                <div class="mb-4">
                    <label class="form-label">Current Images</label>
                    <div class="row">
                        @foreach ($post->images as $image)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ asset($image->image_path) }}" class="card-img-top" alt="Post Image" style="height: 150px; object-fit: cover;">
                                    <div class="card-body text-center p-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_img_{{ $image->id }}">
                                            <label class="form-check-label text-danger" for="remove_img_{{ $image->id }}">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Video Upload -->
            <div class="mb-4">
                <label for="video" class="form-label">Upload New Video</label>
                <input type="file" name="video" id="video" accept="video/*"
                    class="form-control @error('video') is-invalid @enderror">
                <div class="form-text">Supported formats: .mp4, .3gp, .mov, .avi (max 20MB)</div>
                @error('video')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Existing Video Display -->
            @if ($post->video)
                <div class="mb-4">
                    <label class="form-label">Current Video</label>
                    <div class="card">
                        <video width="100%" controls style="max-height: 400px;">
                            <source src="{{ asset($post->video->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove_video">
                                <label class="form-check-label text-danger" for="remove_video">
                                    Remove Video
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Button -->
            <div class="mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Post
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
         $('#type').change(function () {
    let type = $(this).val();

    if (type === 'sponsored') {
        // Hide user & pet
        $('#user_id').closest('.mb-4').hide();
        $('#pet_id').closest('.mb-4').hide();

        // Show sponsored fields
        $('#sponsoredFields').show();
    } else {
        // Show user & pet
        $('#user_id').closest('.mb-4').show();
        $('#pet_id').closest('.mb-4').show();

        // Hide sponsored fields
        $('#sponsoredFields').hide();
    }
});
         $('select[name="target_pet_types[]"]').select2({
    placeholder: "Select Pet Types",
    width: '100%'
});
        $('#breedsSelect').select2({
    placeholder: "Select or type breeds",
    tags: true,
    width: '100%'
});
            
        });
        $(document).ready(function() {
            // Toggle fields based on post type
// Trigger on load (important for edit case)
$('#type').trigger('change');
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
