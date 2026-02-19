@extends('admin.layouts.master')
@section('title', $title)


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 38px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #6f42c1;
        border-color: #5a2d9c;
        color: white;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-check-label {
        font-weight: 500;
    }

    .btn-lg {
        padding: 10px 30px;
        font-size: 16px;
    }
</style>

@section('content')
    <div class="container-fluid mt-4">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">{{ $title }}
                                </h4>
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="btn btn-primary btn-round ms-auto">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Notifications
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form
                                action="{{ isset($notification) ? route('admin.notifications.update', $notification->id) : route('admin.notifications.store') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @if (isset($notification))
                                    @method('PUT')
                                @endif

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-label">Notification Title *</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                id="title" name="title"
                                                value="{{ old('title', $notification->title ?? '') }}"
                                                placeholder="Enter notification title" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type" class="form-label">Notification Type *</label>
                                            <select class="form-control @error('type') is-invalid @enderror" id="type"
                                                name="type" required>
                                                <option value="">Select Type</option>
                                                <option value="info"
                                                    {{ old('type', $notification->type ?? '') == 'info' ? 'selected' : '' }}>
                                                    Info</option>
                                                <option value="alert"
                                                    {{ old('type', $notification->type ?? '') == 'alert' ? 'selected' : '' }}>
                                                    Alert</option>
                                                <option value="promo"
                                                    {{ old('type', $notification->type ?? '') == 'promo' ? 'selected' : '' }}>
                                                    Promo</option>
                                                <option value="update"
                                                    {{ old('type', $notification->type ?? '') == 'update' ? 'selected' : '' }}>
                                                    Update</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="4"
                                        placeholder="Enter notification message" required>{{ old('message', $notification->message ?? '') }}</textarea>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <span id="char-count">0</span> characters
                                        </small>
                                    </div>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-3">
                                    <label for="image" class="form-label">Notification Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                        id="image" name="image" accept="image/*">
                                    <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if (isset($notification) && $notification->image)
                                        <div class="mt-3">
                                            <p class="mb-2">Current Image:</p>
                                            <img src="{{ asset('storage/' . $notification->image) }}" alt="Current Image"
                                                class="img-thumbnail" width="150">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_image"
                                                    name="remove_image" value="1">
                                                <label class="form-check-label text-danger" for="remove_image">
                                                    Remove current image
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group mt-4">
                                    <label class="form-label">Target Audience</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="audience_type" id="all_users"
                                            value="all"
                                            {{ !isset($notification) || !$notification->audience ? 'checked' : '' }}>
                                        <label class="form-check-label" for="all_users">
                                            All Users
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="audience_type"
                                            id="custom_audience" value="custom"
                                            {{ isset($notification) && $notification->audience ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_audience">
                                            Custom Audience (Specific Locations)
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mt-4" id="locations-section"
                                    style="{{ isset($notification) && $notification->audience ? '' : 'display: none;' }}">
                                    <label for="locations" class="form-label">Select Locations</label>
                                    <select class="form-control select2-multiple" id="locations" name="locations[]"
                                        multiple>
                                        @php
                                            $locations = [
                                                'New York',
                                                'Los Angeles',
                                                'Chicago',
                                                'Houston',
                                                'Phoenix',
                                                'Philadelphia',
                                                'San Antonio',
                                                'San Diego',
                                                'Dallas',
                                                'San Jose',
                                                'Miami',
                                                'Seattle',
                                                'Denver',
                                                'Boston',
                                                'Atlanta',
                                            ];
                                        @endphp
                                        @foreach ($locations as $location)
                                            <option value="{{ $location }}"
                                                {{ isset($notification) && $notification->audience && in_array($location, $notification->audience['locations'] ?? []) ? 'selected' : '' }}>
                                                {{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select one or multiple locations</small>
                                </div>

                                <div class="form-group mt-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable_schedule"
                                            name="enable_schedule" value="1"
                                            {{ old('enable_schedule', isset($notification) && $notification->schedule_time ? 'checked' : '') }}>
                                        <label class="form-check-label" for="enable_schedule">
                                            Schedule Notification
                                        </label>
                                    </div>

                                    <div id="schedule-section mt-4"
                                        style="{{ old('enable_schedule', isset($notification) && $notification->schedule_time) ? '' : 'display: none;' }}">
                                        <label for="schedule_time" class="form-label">Schedule Date & Time</label>
                                        <input type="datetime-local"
                                            class="form-control @error('schedule_time') is-invalid @enderror"
                                            id="schedule_time" name="schedule_time"
                                            value="{{ old('schedule_time', isset($notification) && $notification->schedule_time ? \Carbon\Carbon::parse($notification->schedule_time)->format('Y-m-d\TH:i') : '') }}"
                                            min="{{ now()->format('Y-m-d\TH:i') }}">
                                        <small class="text-muted">Schedule when this notification should be sent</small>
                                        @error('schedule_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status12" name="status"
                                            value="1"
                                            {{ !isset($notification) || $notification->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            Enable Notification
                                        </label>
                                    </div>
                                    <small class="text-muted">Disabled notifications won't be sent to users</small>
                                </div>

                                <div class="form-group text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-2"></i>
                                        {{ isset($notification) ? 'Update Notification' : 'Create Notification' }}
                                    </button>
                                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#locations').select2({
                placeholder: "Select locations",
                allowClear: true,
                width: '100%'
            });

            // Show/hide locations section based on audience type
            $('input[name="audience_type"]').change(function() {
                if ($(this).val() === 'custom') {
                    $('#locations-section').slideDown();
                } else {
                    $('#locations-section').slideUp();
                }
            });

            // Character counter for message
            function updateCharCount() {
                const length = $('#message').val().length;
                $('#char-count').text(length + ' characters');
            }

            $('#message').on('input', updateCharCount);
            updateCharCount(); // Initialize on page load

            // Schedule toggle functionality
            $('#enable_schedule').change(function() {
                if ($(this).is(':checked')) {
                    $('#schedule-section').slideDown();
                    // Set default schedule time to 1 hour from now if empty
                    if (!$('#schedule_time').val()) {
                        const now = new Date();
                        now.setHours(now.getHours() + 1);
                        $('#schedule_time').val(now.toISOString().slice(0, 16));
                    }
                } else {
                    $('#schedule-section').slideUp();
                    $('#schedule_time').val('');
                }
            });

            // Image preview
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    // Check file size (2MB limit)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        $(this).val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image-preview').remove();
                        $('#image').after(
                            '<div id="image-preview" class="mt-3">' +
                            '<p class="mb-2">Image Preview:</p>' +
                            '<img src="' + e.target.result +
                            '" class="img-thumbnail" width="200" alt="Preview">' +
                            '</div>'
                        );
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection
