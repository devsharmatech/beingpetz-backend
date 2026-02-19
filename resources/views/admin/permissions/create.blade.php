@extends('admin.layouts.master')

@section('title', 'Create Permission')

@section('content')
    <div class="container-fluid mt-4">


        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Permission Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.permissions.store') }}" method="POST" id="permissionForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Permission Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Enter permission name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">e.g., users.create, posts.view</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="display_name" class="form-label">Display Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('display_name') is-invalid @enderror"
                                            id="display_name" name="display_name" value="{{ old('display_name') }}"
                                            placeholder="Enter display name" required>
                                        @error('display_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">User-friendly name for display</small>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon</label>
                                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                            id="icon" name="icon" value="{{ old('icon', 'fas fa-key') }}"
                                            placeholder="fas fa-key">
                                        @error('icon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">FontAwesome icon class</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="route" class="form-label">Route</label>
                                        <input type="text" class="form-control @error('route') is-invalid @enderror"
                                            id="route" name="route" value="{{ old('route') }}"
                                            placeholder="admin.users.index">
                                        @error('route')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Corresponding route name</small>
                                    </div>
                                </div>


                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="2" placeholder="Brief description of the permission">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Permission
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Permission</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto generate slug from name
            $('#name').on('keyup', function() {
                const name = $(this).val();
                const slug = name.toLowerCase()
                    .replace(/[^\w\s]/gi, '')
                    .replace(/\s+/g, '-');
                $('#slug').val(slug);

                // Also set display name if empty
                if (!$('#display_name').val()) {
                    const displayName = name.replace('.', ' ').replace(/-/g, ' ').replace(/\b\w/g, l => l
                        .toUpperCase());
                    $('#display_name').val(displayName);
                }
            });

            // Custom module input
            $('#module').change(function() {
                if ($(this).val() === '') {
                    $('#newModule').show();
                } else {
                    $('#newModule').hide();
                }
            });

            // If new module entered, update select
            $('#newModule').on('keyup', function() {
                if ($(this).val()) {
                    $('#module').val($(this).val());
                }
            });
        });
    </script>
@endsection
