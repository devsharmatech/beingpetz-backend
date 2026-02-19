@extends('admin.layouts.master')

@section('title', 'User/Vendor Details')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title">User/Vendor Details</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.uservendors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                @if ($user->profile)
                                    <img src="{{ asset('storage/' . $user->profile) }}" alt="Profile"
                                        class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                                @else
                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width: 150px; height: 150px;">
                                        <span class="text-white display-4">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Full Name:</th>
                                        <td>{{ $user->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $user->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role:</th>
                                        <td>
                                            <span class="badge badge-{{ $user->role == 'vendor' ? 'warning' : 'info' }}"
                                                style="color: gray">
                                                {{ $user->roleRelation->display_name ?? ucfirst($user->role) }}
                                            </span>
                                            @if ($user->roleRelation)
                                                <small class="text-muted">({{ $user->roleRelation->name }})</small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Role ID:</th>
                                        <td>{{ $user->role_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td>
                                            {{ $user->locality ? $user->locality . ', ' : '' }}
                                            {{ $user->city ? $user->city . ', ' : '' }}
                                            {{ $user->state ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge badge-{{ $user->isComplete ? 'success' : 'secondary' }}"
                                                style="color: green">
                                                {{ $user->isComplete ? 'Profile Complete' : 'Profile Incomplete' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Login:</th>
                                        <td>{{ $user->last_login ? $user->last_login->format('Y-m-d H:i:s') : 'Never' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td>{{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Permissions</h4>
                                    </div>
                                    <div class="card-body">
                                        <!-- Role Permissions -->
                                        <div class="mb-4">
                                            <h5>Role Default Permissions:</h5>
                                            @if (!empty($rolePermissions))
                                                <div class="row">
                                                    @foreach ($rolePermissions as $permission)
                                                        <div class="col-md-3 mb-2">
                                                            <span class="badge bg-primary">{{ $permission }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">No default permissions set for this role.</p>
                                            @endif
                                        </div>



                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.uservendors.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .badge {
            font-size: 0.85em;
            padding: 6px 10px;
            border-radius: 12px;
        }
    </style>
@endsection
