@extends('admin.layouts.master')
@section('title', 'Notification Details')

@section('content')
    <div class="container-fluid mt-4">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">
                                    Notification Details
                                </h4>
                                <div class="ms-auto">

                                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-primary btn-round">
                                        <i class="fas fa-arrow-left me-2"></i>Back to List
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Basic Information -->
                                    <div class="card mb-4">
                                        <div class="card-header text-white">
                                            <h5 class="mb-0">Basic Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-sm-4 fw-bold">ID:</div>
                                                <div class="col-sm-8">#{{ $notification->id }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 fw-bold">Title:</div>
                                                <div class="col-sm-8">{{ $notification->title }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 fw-bold">Message:</div>
                                                <div class="col-sm-8">{{ $notification->message }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 fw-bold">Type:</div>
                                                <div class="col-sm-8">
                                                    <span
                                                        class="badge bg-{{ $notification->type === 'alert' ? 'warning' : ($notification->type === 'promo' ? 'success' : 'info') }}">
                                                        {{ ucfirst($notification->type) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4 fw-bold">Status:</div>
                                                <div class="col-sm-8">
                                                    <span
                                                        class="badge bg-{{ $notification->status ? 'success' : 'secondary' }}">
                                                        {{ $notification->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Audience Information -->
                                    <div class="card mb-4">
                                        <div class="card-header text-white">
                                            <h5 class="mb-0">Audience Information</h5>
                                        </div>
                                        <div class="card-body">
                                            @if ($notification->audience && isset($notification->audience['locations']))
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Target Locations:</div>
                                                    <div class="col-sm-8">
                                                        @foreach ($notification->audience['locations'] as $location)
                                                            <span
                                                                class="badge bg-primary me-1 mb-1">{{ $location }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-12">
                                                        <span class="badge bg-success">All Users</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Image Preview -->
                                    <div class="card mb-4">
                                        <div class="card-header text-white">
                                            <h5 class="mb-0">Notification Image</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            @if ($notification->image)
                                                <img src="{{ asset('storage/' . $notification->image) }}"
                                                    class="img-fluid rounded" alt="Notification Image"
                                                    style="max-height: 300px;">
                                                <p class="text-muted mt-2 mb-0">Image Preview</p>
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">No Image Available</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Meta Information -->
                                    <div class="card">
                                        <div class="card-header text-white">
                                            <h5 class="mb-0">Meta Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-sm-5 fw-bold">Created:</div>
                                                <div class="col-sm-7">
                                                    {{ $notification->created_at->format('M d, Y h:i A') }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 fw-bold">Last Updated:</div>
                                                <div class="col-sm-7">
                                                    {{ $notification->updated_at->format('M d, Y h:i A') }}</div>
                                            </div>
                                            @if ($notification->sender)
                                                <div class="row">
                                                    <div class="col-sm-5 fw-bold">Sent By:</div>
                                                    <div class="col-sm-7">{{ $notification->sender->name }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-4">
                                <div class="col-12 text-left">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.notifications.edit', $notification) }}"
                                            class="btn btn-warning">
                                            <i class="fas fa-edit me-2"></i>Edit Notification
                                        </a>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal">
                                            <i class="fas fa-trash me-2"></i>Delete Notification
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Delete Notification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this notification? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will permanently delete the notification.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .fw-bold {
            color: #495057;
        }

        .badge {
            font-size: 0.85em;
        }
    </style>
@endsection
