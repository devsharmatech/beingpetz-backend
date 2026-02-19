@extends('admin.layouts.master')

@section('title', 'View Role')

@section('content')
    <div class="container-fluid mt-4">


        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Role Details</h5>
                            <div>
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Role Name:</th>
                                        <td>
                                            <span class="badge bg-{{ $role->color }}" style="color: purple">
                                                <i class="{{ $role->icon }} me-1"></i>
                                                {{ $role->name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Display Name:</th>
                                        <td>{{ $role->display_name }}</td>
                                    </tr>

                                    <tr>
                                        <th>Description:</th>
                                        <td>{{ $role->description ?? 'N/A' }}</td>
                                    </tr>

                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">

                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if ($role->is_active == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Permissions Count:</th>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $role->permissions->count() }} permissions
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td>{{ $role->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At:</th>
                                        <td>{{ $role->updated_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <!-- Permissions Section -->
                        <div class="mt-4">
                            <h5 class="mb-3">Assigned Permissions</h5>
                            @if ($role->permissions->count() > 0)
                                <div class="row">
                                    @php
                                        $groupedPermissions = $role->permissions->groupBy('module');
                                    @endphp
                                    @foreach ($groupedPermissions as $module => $permissions)
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-semibold">{{ $module }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    @foreach ($permissions as $permission)
                                                        <div class="mb-2">
                                                            <i
                                                                class="{{ $permission->icon ?? 'fas fa-key' }} text-primary me-2"></i>
                                                            <strong>{{ $permission->display_name }}</strong>
                                                            @if ($permission->description)
                                                                <small
                                                                    class="text-muted d-block">{{ $permission->description }}</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No permissions assigned to this role.
                                </div>
                            @endif
                        </div>

                        <!-- Users Section -->
                        @if ($role->users->count() > 0)
                            <div class="mt-4">
                                <h5 class="mb-3">Assigned Users ({{ $role->users->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Joined At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($role->users as $user)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if ($user->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
