<!-- resources/views/admin/posts/partials/gallery-content.blade.php -->
@php
    // Combine images and videos for display with their types
    $allMedia = [];
    if ($post->images) {
        foreach ($post->images as $image) {
            $allMedia[] = [
                'type' => 'image',
                'path' => $image->image_path,
                'id' => $image->id,
                'model' => 'image',
            ];
        }
    }
    if ($post->videos) {
        foreach ($post->videos as $video) {
            $allMedia[] = [
                'type' => 'video',
                'path' => $video->video_path,
                'id' => $video->id,
                'model' => 'video',
            ];
        }
    }
@endphp

@if (count($allMedia) > 0)
    <div class="row" id="media-gallery">
        @foreach ($allMedia as $media)
            <div class="col-md-4 col-sm-6 mb-4 gallery-item">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-2 position-relative">
                        @if ($media['type'] === 'video')
                            <video controls class="w-100 rounded" style="height: 200px; object-fit: cover;">
                                <source src="{{ asset($media['path']) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-play-circle me-1"></i>Video
                                </span>
                            </div>
                        @else
                            <img src="{{ asset($media['path']) }}" alt="Post media" class="w-100 rounded"
                                style="height: 200px; object-fit: cover; cursor: zoom-in;" onclick="openLightbox(this)">
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-info">
                                    <i class="fas fa-image me-1"></i>Image
                                </span>
                            </div>
                        @endif

                        <button class="btn btn-danger btn-sm delete-media-btn position-absolute top-0 end-0 m-1"
                            data-media-id="{{ $media['id'] }}" data-media-type="{{ $media['model'] }}"
                            title="Delete Media">
                            <i class="fas fa-trash fa-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <small>
                    Total Media: {{ count($allMedia) }}
                    ({{ $post->images ? count($post->images) : 0 }} images,
                    {{ $post->videos ? count($post->videos) : 0 }} videos)
                </small>
            </div>
        </div>
    </div>
@else
    <div class="empty-gallery text-center py-5">
        <i class="fas fa-images fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No Media Found</h5>
        <p class="text-muted">This post doesn't contain any images or videos.</p>
    </div>
@endif

<script>
    function openLightbox(element) {
        const lightbox = document.createElement('div');
        lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        cursor: zoom-out;
    `;

        const img = document.createElement('img');
        img.src = element.src;
        img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    `;

        lightbox.appendChild(img);
        lightbox.onclick = () => document.body.removeChild(lightbox);

        document.body.appendChild(lightbox);
    }
</script>
