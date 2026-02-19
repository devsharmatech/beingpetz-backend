<div class="post-details">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary">Basic Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="120"><strong>Post ID:</strong></td>
                    <td>#{{ $post->id }}</td>
                </tr>
                <tr>
                    <td><strong>Post Type:</strong></td>
                    <td>
                        <span
                            class="badge bg-{{ $post->post_type == 'normal' ? 'primary' : ($post->post_type == 'birthday' ? 'success' : ($post->post_type == 'repost' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($post->post_type ?? 'Normal') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        @if ($post->deleted == 1)
                            <span class="badge bg-danger">Deleted</span>
                        @else
                            <span class="badge bg-{{ $post->is_public ? 'success' : 'warning' }}">
                                {{ $post->is_public ? 'Published' : 'Draft' }}
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Created:</strong></td>
                    <td>{{ $post->created_at->format('M d, Y h:i A') }}</td>
                </tr>
                @if ($post->deleted_at)
                    <tr>
                        <td><strong>Deleted:</strong></td>
                        <td>{{ $post->deleted_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="col-md-6">
            <h6 class="text-primary">Pet & Parent Info</h6>
            <table class="table table-sm table-borderless">
                @if ($post->pet)
                    <tr>
                        <td width="120"><strong>Pet Name:</strong></td>
                        <td>{{ $post->pet->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pet Breed:</strong></td>
                        <td>{{ $post->pet->breed ?? 'N/A' }}</td>
                    </tr>
                @endif
                @if ($post->parent)
                    <tr>
                        <td><strong>Parent:</strong></td>
                        <td>{{ $post->parent->first_name }} {{ $post->parent->last_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $post->parent->email }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    @if ($post->caption)
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-primary">Caption</h6>
                <div class="border rounded p-3 bg-light">
                    {{ $post->caption }}
                </div>
            </div>
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-primary">Media & Engagement</h6>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border rounded p-2">
                        <h5 class="mb-1 text-primary">{{ $post->images ? count($post->images) : 0 }}</h5>
                        <small class="text-muted">Images</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-2">
                        <h5 class="mb-1 text-info">{{ $post->videos ? count($post->videos) : 0 }}</h5>
                        <small class="text-muted">Videos</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-2">
                        <h5 class="mb-1 text-success">{{ $post->likes ? count($post->likes) : 0 }}</h5>
                        <small class="text-muted">Likes</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-2">
                        <h5 class="mb-1 text-warning">{{ $post->comments ? count($post->comments) : 0 }}</h5>
                        <small class="text-muted">Comments</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
