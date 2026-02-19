@extends('admin.layouts.master')

@section('title', 'Create Role')

@section('content')
    <div class="container-fluid mt-4">


        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Role Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Role Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Enter role name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                    </div>
                                </div>
                            </div>



                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="2" placeholder="Brief description of the role">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">

                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Role
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h5 class="mb-3">Assign Permissions</h5>
                                <p class="text-muted">Select permissions to assign to this role</p>

                                @if (isset($permissions) && count($permissions) > 0)
                                    <div class="row">
                                        @foreach ($permissions as $module => $modulePermissions)
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header bg-light">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0 fw-semibold">{{ $module }}</h6>
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    class="form-check-input group-checkbox"
                                                                    id="group-{{ Str::slug($module) }}"
                                                                    data-group="{{ Str::slug($module) }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach ($modulePermissions as $permission)
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox"
                                                                    class="form-check-input permission-checkbox"
                                                                    id="permission-{{ $permission->id }}"
                                                                    name="permissions[]" value="{{ $permission->id }}"
                                                                    data-group="{{ Str::slug($module) }}"
                                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="permission-{{ $permission->id }}">
                                                                    <strong>{{ $permission->display_name }}</strong>
                                                                    @if ($permission->description)
                                                                        <small
                                                                            class="text-muted d-block">{{ $permission->description }}</small>
                                                                    @endif
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        No permissions found. <a href="{{ route('admin.permissions.create') }}">Create
                                            permissions first</a>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
                    $('#display_name').val(name);
                }
            });

            // Group checkbox functionality
            $('.group-checkbox').change(function() {
                const group = $(this).data('group');
                const isChecked = $(this).prop('checked');
                $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
            });

            // Permission checkbox functionality
            $('.permission-checkbox').change(function() {
                const group = $(this).data('group');
                const totalPermissions = $(`.permission-checkbox[data-group="${group}"]`).length;
                const checkedPermissions = $(`.permission-checkbox[data-group="${group}"]:checked`).length;

                const groupCheckbox = $(`#group-${group}`);
                if (checkedPermissions === totalPermissions) {
                    groupCheckbox.prop('checked', true);
                    groupCheckbox.prop('indeterminate', false);
                } else if (checkedPermissions > 0) {
                    groupCheckbox.prop('checked', false);
                    groupCheckbox.prop('indeterminate', true);
                } else {
                    groupCheckbox.prop('checked', false);
                    groupCheckbox.prop('indeterminate', false);
                }
            });

            // Initialize group checkboxes
            $('.group-checkbox').each(function() {
                const group = $(this).data('group');
                const totalPermissions = $(`.permission-checkbox[data-group="${group}"]`).length;
                const checkedPermissions = $(`.permission-checkbox[data-group="${group}"]:checked`).length;

                if (checkedPermissions === totalPermissions) {
                    $(this).prop('checked', true);
                    $(this).prop('indeterminate', false);
                } else if (checkedPermissions > 0) {
                    $(this).prop('checked', false);
                    $(this).prop('indeterminate', true);
                }
            });
        });
    </script>
@endpush
