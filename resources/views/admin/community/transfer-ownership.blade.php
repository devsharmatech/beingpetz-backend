{{-- resources/views/admin/community/transfer-ownership.blade.php --}}

@extends('admin.layouts.master')
@section('title')
    Transfer Ownership - {{ $community->name }}
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-sm-0">Transfer Ownership - {{ $community->name }}</h4>
                    <a href="{{ route('admin.community.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Communities
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.community.transfer', $community->id) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Current Admin</label>
                                <input type="text" class="form-control"
                                    value="{{ $community->creator->first_name }} {{ $community->creator->last_name }}"
                                    readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Moderators</label>
                                @if ($community->moderators->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($community->moderators as $moderator)
                                            <span class="badge bg-warning text-dark">
                                                {{ $moderator->user->first_name }} {{ $moderator->user->last_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No moderators assigned</p>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="transfer_type" class="form-label">Transfer Type</label>
                                <select class="form-select" id="transfer_type" name="transfer_type" required>
                                    <option value="">Select Transfer Type</option>
                                    <option value="admin">Transfer Community Ownership</option>
                                    <option value="moderator">Add as Moderator</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="new_owner_id" class="form-label">Select User</label>
                                <select class="form-select select2" id="new_owner_id" name="new_owner_id" required>
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Transfer Ownership:</strong> Will make the selected user the new community admin.
                                <br>
                                <strong>Add as Moderator:</strong> Will add the selected user as a moderator to the
                                community.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Transfer</button>
                                <a href="{{ route('admin.community.index') }}" class="btn btn-secondary">Cancel</a>
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
            $('.select2').select2();
        });
    </script>
@endsection
