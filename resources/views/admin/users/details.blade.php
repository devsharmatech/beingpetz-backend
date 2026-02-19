@extends('admin.layouts.master')
@section('title', 'User Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Details</h2>
            <div>
                <a href="{{ route('admin.users.deleted') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Deleted Users
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-circle"></i> User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">User ID</th>
                                        <td><strong>#{{ $user->id }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Profile Status</th>
                                        <td>
                                            @if ($user->isComplete)
                                                <span class="badge bg-success">Complete</span>
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Account Created</th>
                                        <td>{{ $user->created_at ? $user->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Login</th>
                                        <td>
                                            @if ($user->last_login)
                                                {{ \Carbon\Carbon::parse($user->last_login)->format('M d, Y H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    ({{ \Carbon\Carbon::parse($user->last_login)->diffForHumans() }})
                                                </small>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($user->deleted_at)
                                        <tr class="table-danger">
                                            <th>Deleted At</th>
                                            <td class="text-danger">
                                                <strong>
                                                    {{ $user->deleted_at->format('M d, Y H:i') }}
                                                    <br>
                                                    <small>
                                                        ({{ $user->deleted_at->diffForHumans() }})
                                                    </small>
                                                </strong>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status Card -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">Account Status</h6>
                    </div>
                    <div class="card-body text-center">
                        @if ($user->deleted_at)
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill display-4"></i>
                                <h5 class="mt-2">Account Deleted</h5>
                                <p class="mb-0">This user account has been deleted from the system.</p>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill display-4"></i>
                                <h5 class="mt-2">Account Active</h5>
                                <p class="mb-0">This user account is currently active.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="card-title mb-0">Quick Stats</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Days Since Registration</span>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $user->created_at ? $user->created_at->diffInDays(now()) : 'N/A' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Last Activity</span>
                                <span class="badge bg-info rounded-pill">
                                    @if ($user->last_login)
                                        {{ $user->last_login->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </span>
                            </div>
                            @if ($user->deleted_at)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Days Since Deletion</span>
                                    <span class="badge bg-danger rounded-pill">
                                        {{ $user->deleted_at->diffInDays(now()) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            border-bottom: none;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
@endsection
