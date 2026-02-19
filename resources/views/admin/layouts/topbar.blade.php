<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">

            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="#" target="_blank" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('images/logo.webp') }}" alt="" height="30" width="40">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('images/logo.webp') }}" alt="" height="70" width="150">
                    </span>
                </a>

                <a href="#" target="_blank" class="logo logo-light">
                    {{-- <span class="logo-sm">
                        <h6 style="margin-top: 10px;">Being Petz</h6>
                    </span>
                    <span class="logo-lg">
                        <h1 style="margin-top: 10px;">Being Petz</h1>
                    </span> --}}

                    <span class="logo-sm">
                        <img src="{{ asset('images/logo.webp') }}" alt="" height="30" width="40">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('images/logo.webp') }}" alt="" height="70" width="150">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="mdi mdi-menu"></i>
            </button>


        </div>

        <!-- Search input -->
        <div class="search-wrap" id="search-wrap">
            <div class="search-bar">
                <input class="search-input form-control" placeholder="Search" />
                <a href="#" class="close-search toggle-search" data-target="#search-wrap">
                    <i class="mdi mdi-close-circle"></i>
                </a>
            </div>
        </div>

        <div class="d-flex">
            <div class="dropdown d-none d-lg-inline-block">
                <button type="button" class="btn header-item toggle-search noti-icon waves-effect"
                    data-target="#search-wrap">
                    <i class="mdi mdi-magnify"></i>
                </button>
            </div>


            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="mdi mdi-fullscreen"></i>
                </button>
            </div>


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    <img src="{{ Auth::user()->profile ? asset(Auth::user()->profile) : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=500&q=80' }}"
                        alt="Header Avatar" class="rounded-circle header-profile-user">

                    <span class="d-none d-xl-inline-block ms-1">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </span>

                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="mdi mdi-account-circle-outline font-size-16 align-middle me-1"></i>
                        Profile
                    </a>


                    {{-- <a class="dropdown-item d-block" href=""><span
                            class="badge badge-success float-end">11</span><i
                            class="mdi mdi-cog-outline font-size-16 align-middle me-1"></i> Settings</a> --}}
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-power font-size-16 align-middle me-1 text-danger"></i> Logout
                    </a>

                    <form id="logout-form" action="{{ route('admin.logout') }}" method="GET" style="display: none;">
                        @csrf
                    </form>

                </div>
            </div>

            {{-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="mdi mdi-cog-outline font-size-20"></i>
                </button>
            </div> --}}

        </div>
    </div>
</header>
