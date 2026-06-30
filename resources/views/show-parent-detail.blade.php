<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pet->name }}'s Parent Information</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" style="background-color:blue !important;" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8337B2;
            --primary-light: #9c5bc5;
            --primary-dark: #6a2d8f;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo {
            height: 58px;
            margin-bottom: 15px;
        }
        
        .pet-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .user-profile {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .info-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .section-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        
        .contact-badge {
            background-color: var(--primary-light);
            color: white;
        }
        
        #map {
            height: 200px;
            width: 100%;
            border-radius: 10px;
        }
        
        .alert-message {
            background-color: #ffc41d;
            border-left: 4px solid #ffc41d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color:#000 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <!-- Beingpetz Logo -->
            <img src="{{ asset('web-logo.png') }}" alt="Beingpetz Logo" class="logo" onerror="this.src='https://via.placeholder.com/150x50?text=Beingpetz'">
            
            <!-- Lost pet message -->
            <div class="alert-message text-start mx-auto" style="max-width: 600px;">
                <h4><i class="fas fa-exclamation-circle me-2"></i> It seems {{ $pet->name }} is lost</h4>
                <p class="mb-0">Please help {{ $pet->gender == 'male' ? 'him' : 'her' }} re-unite with {{ $pet->gender == 'male' ? 'his' : 'her' }} parents</p>
            </div>
            
            <h1>{{ $pet->name }}'s Information</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <img src="{{ asset($pet->avatar) }}" alt="{{ $pet->name }}" class="pet-avatar mb-3" onerror="this.src='https://via.placeholder.com/150'">
                <h2>{{ $pet->name }}</h2>
                <p class="text-muted">{{ $pet->breed }}</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="section-title">About {{ $pet->name }}</h3>
                        <div class="row mb-3">
                            <div class="col-4 info-label">Gender:</div>
                            <div class="col-8">{{ ucfirst($pet->gender) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 info-label">Age:</div>
                            <div class="col-8">
                                @php
                                    $age = \Carbon\Carbon::parse($pet->dob)->age;
                                    echo $age . ' year' . ($age != 1 ? 's' : '') . ' old';
                                @endphp
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 info-label">Breed:</div>
                            <div class="col-8">{{ $pet->breed }}</div>
                        </div>
                        @if($pet->bio)
                        <div class="mt-4">
                            <h5 class="info-label">Bio</h5>
                            <p>{{ $pet->bio }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                             @if($parentInfo->isNameShow)
                            <img src="{{ asset($parentInfo->profile) }}" alt="{{ $parentInfo->first_name }}" class="user-profile me-3" onerror="this.src='https://via.placeholder.com/100'">
                             @else
                                    
                            <img src="https://placehold.co/100?text=Locked" alt="Locked" class="user-profile me-3" onerror="this.src='https://via.placeholder.com/100'">
                                 
                                 @endif
                            <div>
                                 @if($parentInfo->isNameShow)
                                    <h3 class="mb-0">{{ $parentInfo->first_name }} {{ $parentInfo->last_name }}</h3>
                                 @else
                                    <h3 class="mb-0">Name is locked.</h3>
                                 
                                 @endif
                                <p class="text-muted mb-0">{{ $pet->name }}'s Parent</p>
                            </div>
                        </div>
                        
                        <h4 class="section-title">Contact Information</h4>
                        <div class="row mb-3">
                            <div class="col-4 info-label">Email:</div>
                             @if($parentInfo->isPhoneShow)
                             <div class="col-8">{{ $parentInfo->email ?? 'Email is not available!' }}</div>
                            @else
                             <div class="col-8">Email is locked.</div>
                            
                            @endif
                        </div>
                        @if($parentInfo->isPhoneShow && $parentInfo->phone)
                        <div class="row mb-3">
                            <div class="col-4 info-label">Phone/Whatsapp:</div>
                            <div class="col-8">{{ $parentInfo->phone ?? 'Phone number is not available!' }}</div>
                        </div>
                        @else
                        <div class="row mb-3">
                            <div class="col-4 info-label">Phone/Whatsapp:</div>
                            <div class="col-8">Phone number is locked.</div>
                        </div>
                        
                        @endif
                         @if($parentInfo->isPhoneShow)
                        @if($parentInfo->latitude && $parentInfo->longitude)
                        <div class="mt-4">
                            <h5 class="info-label mb-3">Location</h5>
                            <div id="map"></div>
                            <small class="text-muted">Approximate location based on parent's coordinates</small>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
       @if($parentInfo->isPhoneShow)
    <div class="text-center mt-4 mb-5 contact-buttons">
        <!-- WhatsApp button with distinctive green color -->
         <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $parentInfo->phone ?? '') }}" class="btn btn-lg px-4 me-3 whatsapp-btn" target="_blank">
            <i class="fab fa-whatsapp me-2"></i> WhatsApp
        </a>
        
        <!-- Call button with phone blue color -->
        <a href="tel:{{ $parentInfo->phone ?? ''}}" class="btn btn-lg px-4 me-3 call-btn">
            <i class="fas fa-phone-alt me-2"></i> Call
        </a>
        
        <!-- Email button with professional red color -->
        <a href="mailto:{{ $parentInfo->email }}" class="btn btn-lg px-4 email-btn">
            <i class="fas fa-envelope me-2"></i> Email
        </a>
    </div>

   
@endif

<style>
    /* Base button styling */
    .contact-buttons .btn {
        font-weight: 600;
        border-radius: 8px;
        padding: 12px 24px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    /* Button hover effect */
    .contact-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Button active effect */
    .contact-buttons .btn:active {
        transform: translateY(1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* WhatsApp button specific styling */
    .whatsapp-btn {
        background-color: #25D366;
        color: white;
    }
    
    .whatsapp-btn:hover {
        background-color: #128C7E;
    }
    
    /* Call button specific styling */
    .call-btn {
        background-color: #007BFF;
        color: white;
    }
    
    .call-btn:hover {
        background-color: #0056b3;
    }
    
    /* Email button specific styling */
    .email-btn {
        background-color: #D44638;
        color: white;
    }
    
    .email-btn:hover {
        background-color: #B23121;
    }
    
    /* Optional: Add a subtle shine effect on buttons */
    .contact-buttons .btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 40px;
        height: 200%;
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(30deg);
        transition: all 0.3s;
    }
    
    .contact-buttons .btn:hover::after {
        left: 120%;
    }
</style>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @if($parentInfo->latitude && $parentInfo->longitude)
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAonK15hotzDslX4ePjIbmizRii-7Ng4QE&callback=initMap" async defer></script>
    <script>
        function initMap() {
            // Parent's location coordinates
            const parentLocation = { 
                lat: {{ $parentInfo->latitude }}, 
                lng: {{ $parentInfo->longitude }} 
            };
            
            // Create the map centered at parent's location
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: parentLocation,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                styles: [
                    {
                        "featureType": "poi",
                        "stylers": [{ "visibility": "off" }]
                    }
                ]
            });
            
            // Add a marker at parent's location
            new google.maps.Marker({
                position: parentLocation,
                map: map,
                title: "{{ $parentInfo->first_name }}'s Location",
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/purple-dot.png"
                }
            });
            
            // Add a circle to show approximate area (for privacy)
            new google.maps.Circle({
                strokeColor: "#8337B2",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#8337B2",
                fillOpacity: 0.2,
                map: map,
                center: parentLocation,
                radius: 300 // 300 meters radius
            });
        }
    </script>
    @endif
</body>
</html>