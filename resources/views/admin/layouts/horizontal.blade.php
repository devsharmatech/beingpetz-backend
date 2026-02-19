<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="index" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="20">
                    </span>
                </a>

                <a href="index" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="20">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 d-lg-none header-item waves-effect waves-light"
                data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="mdi mdi-menu"></i>
            </button>

            <div class="topbar-social-icon me-3 d-none d-md-block">
                <ul class="list-inline title-tooltip m-0">
                    <li class="list-inline-item">
                        <a href="email-inbox" data-toggle="tooltip" data-placement="top" title="Email">
                            <i class="mdi mdi-email-outline"></i>
                        </a>
                    </li>

                    <li class="list-inline-item">
                        <a href="calendar" data-toggle="tooltip" data-placement="top" title="Calendar">
                            <i class="mdi mdi-calendar"></i>
                        </a>
                    </li>

                    
                </ul>
            </div>
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


            <div class="dropdown d-none d-md-block ms-2">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <img class="me-2" src="{{ URL::asset('build/images/flags/us.jpg') }}" alt="Header Language"
                        height="16"> English <span class="mdi mdi-chevron-down"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="{{ URL::asset('build/images/flags/germany.jpg') }}" alt="user-image" class="me-1"
                            height="12"> <span class="align-middle"> German </span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="{{ URL::asset('build/images/flags/italy.jpg') }}" alt="user-image" class="me-1"
                            height="12"> <span class="align-middle"> Italian </span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="{{ URL::asset('build/images/flags/french.jpg') }}" alt="user-image" class="me-1"
                            height="12"> <span class="align-middle"> French </span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="{{ URL::asset('build/images/flags/spain.jpg') }}" alt="user-image" class="me-1"
                            height="12"> <span class="align-middle"> Spanish </span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <img src="{{ URL::asset('build/images/flags/russia.jpg') }}" alt="user-image" class="me-1"
                            height="12"> <span class="align-middle"> Russian </span>
                    </a>
                </div>
            </div>

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="mdi mdi-fullscreen"></i>
                </button>
            </div>



            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect"
                    id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="mdi mdi-bell-outline bx-tada"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0"> Notifications </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small"> View All</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="mdi mdi-cart text-white"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Your order is placed</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <img src="{{ URL::asset('build/images/users/avatar-3.jpg') }}"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">James Lemire</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">It will seem like simplified English.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 1 hours ago</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-success rounded-circle font-size-16">
                                        <i class="mdi mdi-check text-white"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Your item is shipped</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <img src="{{ URL::asset('build/images/users/avatar-4.jpg') }}"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Salena Layfield</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">As a skeptical Cambridge friend of mine occidental.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 1 hours ago</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top">
                        <a class="btn btn-sm btn-link font-size-14 w-100 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="{{ URL::asset('build/images/users/avatar-7.jpg') }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1">James</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="#"><i
                            class="mdi mdi-account-circle-outline font-size-16 align-middle me-1"></i> Profile</a>
                    <a class="dropdown-item" href="#"><i
                            class="mdi mdi-wallet-outline font-size-16 align-middle me-1"></i> My Wallet</a>
                    <a class="dropdown-item d-block" href="#"><span
                            class="badge badge-success float-end">11</span><i
                            class="mdi mdi-cog-outline font-size-16 align-middle me-1"></i> Settings</a>
                    <a class="dropdown-item" href="#"><i
                            class="mdi mdi-lock-open-outline font-size-16 align-middle me-1"></i> Lock screen</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="javascript:void();"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                            class="mdi mdi-power font-size-16 align-middle me-1 text-danger"></i> Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="mdi mdi-cog-outline font-size-20"></i>
                </button>
            </div>

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index">
                            <i class="dripicons-home me-2"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-uielement"
                            role="button">
                            <i class="dripicons-suitcase me-2"></i> UI Elements <div class="arrow-down"></div>
                        </a>

                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                            aria-labelledby="topnav-uielement">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div>
                                        <a href="ui-alerts" class="dropdown-item">Alerts</a>
                                        <a href="ui-buttons" class="dropdown-item">Buttons</a>
                                        <a href="ui-cards" class="dropdown-item">Cards</a>
                                        <a href="ui-carousel" class="dropdown-item">Carousel</a>
                                        <a href="ui-dropdowns" class="dropdown-item">Dropdowns</a>
                                        <a href="ui-grid" class="dropdown-item">Grid</a>
                                        <a href="ui-images" class="dropdown-item">Images</a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>

                                        <a href="ui-lightbox" class="dropdown-item">Lightbox</a>
                                        <a href="ui-modals" class="dropdown-item">Modals</a>
                                        <a href="ui-rangeslider" class="dropdown-item">Range Slider</a>
                                        <a href="ui-session-timeout" class="dropdown-item">Session Timeout</a>
                                        <a href="ui-progressbars" class="dropdown-item">Progress Bars</a>
                                        <a href="ui-sweet-alert" class="dropdown-item">Sweet-Alert</a>
                                        <a href="ui-tabs-accordions" class="dropdown-item">Accordions</a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <a href="ui-typography" class="dropdown-item">Typography</a>
                                        <a href="ui-video" class="dropdown-item">Video</a>
                                        <a href="ui-general" class="dropdown-item">General</a>
                                        <a href="ui-colors" class="dropdown-item">Colors</a>
                                        <a href="ui-rating" class="dropdown-item">Rating</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-apps"
                            role="button">
                            <i class="dripicons-archive me-2"></i> Apps <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-apps">

                            <a href="calendar" class="dropdown-item">Calendar</a>

                            <a href="chat" class="dropdown-item">Chat</a>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                    id="topnav-ecommerce" role="button">
                                    Ecommerce <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-ecommerce">
                                    <a href="ecommerce-products" class="dropdown-item">Products</a>
                                    <a href="ecommerce-product-detail" class="dropdown-item">Product Detail</a>
                                    <a href="ecommerce-orders" class="dropdown-item">Orders</a>
                                    <a href="ecommerce-customers" class="dropdown-item">Customers</a>
                                    <a href="ecommerce-cart" class="dropdown-item">Cart</a>
                                    <a href="ecommerce-checkout" class="dropdown-item">Checkout</a>
                                    <a href="ecommerce-shops" class="dropdown-item">Shops</a>
                                    <a href="ecommerce-add-product" class="dropdown-item">Add Product</a>
                                </div>
                            </div>


                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email"
                                    role="button">
                                    Email <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="email-inbox" class="dropdown-item">Inbox</a>
                                    <a href="email-read" class="dropdown-item">Email Read</a>
                                    <a href="email-compose" class="dropdown-item">Email Compose</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-form"
                            role="button">
                            <i class="dripicons-inbox me-2"></i> Components <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-form">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-form"
                                    role="button">
                                    Forms <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-form">
                                    <a href="form-elements" class="dropdown-item">Form Elements</a>
                                    <a href="form-validation" class="dropdown-item">Form Validation</a>
                                    <a href="form-advanced" class="dropdown-item">Form Advanced</a>
                                    <a href="form-editors" class="dropdown-item">Form Editors</a>
                                    <a href="form-uploads" class="dropdown-item">Form Upload</a>
                                    <a href="form-wizard" class="dropdown-item">Form Wizard</a>
                                    <a href="form-mask" class="dropdown-item">Form Mask</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-chart"
                                    role="button">
                                    Charts <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-chart">
                                    <a href="charts-apex" class="dropdown-item">Apex charts</a>
                                    <a href="charts-chartist" class="dropdown-item">Chartist</a>
                                    <a href="charts-chartjs" class="dropdown-item">Chartjs Chart</a>
                                    <a href="charts-flot" class="dropdown-item">Flot Chart</a>
                                    <a href="charts-knob" class="dropdown-item">Knob Chart</a>
                                    <a href="charts-sparkline" class="dropdown-item">Sparkline Chart</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-table"
                                    role="button">
                                    Tables <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-table">
                                    <a href="tables-basic" class="dropdown-item">Basic Tables</a>
                                    <a href="tables-datatable" class="dropdown-item">Data Tables</a>
                                    <a href="tables-responsive" class="dropdown-item">Responsive Table</a>
                                    <a href="tables-editable" class="dropdown-item">Editable Table</a>
                                </div>
                            </div>


                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-icons"
                                    role="button">
                                    Icons <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-icons">
                                    <a href="icons-materialdesign" class="dropdown-item">Material Design</a>
                                    <a href="icons-dripicons" class="dropdown-item">Dripicons</a>
                                    <a href="icons-fontawesome" class="dropdown-item">Font awesome</a>
                                    <a href="icons-themify" class="dropdown-item">Themify Icons</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-map"
                                    role="button">
                                    Maps <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-map">
                                    <a href="maps-google" class="dropdown-item">Google Maps</a>
                                    <a href="maps-vector" class="dropdown-item">Vector Maps</a>
                                </div>
                            </div>
                        </div>
                    </li>



                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-more"
                            role="button">
                            <i class="dripicons-copy me-2"></i> Extra Pages <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-more">

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-auth"
                                    role="button">
                                    Authentication <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-auth">
                                    <a href="auth-login" class="dropdown-item">Login</a>
                                    <a href="auth-register" class="dropdown-item">Register</a>
                                    <a href="auth-recoverpw" class="dropdown-item">Re-Password</a>
                                    <a href="auth-lock-screen" class="dropdown-item">Lock Screen</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                    id="topnav-utility" role="button">
                                    Pages <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-utility">
                                    <a href="pages-timeline" class="dropdown-item">Timeline</a>
                                    <a href="pages-invoice" class="dropdown-item">Invoice</a>
                                    <a href="pages-blank" class="dropdown-item">Blank Page</a>
                                    <a href="pages-404" class="dropdown-item">Error 404</a>
                                    <a href="pages-500" class="dropdown-item">Error 500</a>
                                    <a href="pages-pricing" class="dropdown-item">Pricing</a>
                                    <a href="pages-maintenance" class="dropdown-item">Maintenance</a>
                                    <a href="pages-comingsoon" class="dropdown-item">Coming Soon</a>
                                    <a href="pages-faq" class="dropdown-item">FAQs</a>
                                </div>
                            </div>
                        </div>
                    </li>



                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layout"
                            role="button">
                            <i class="dripicons-device-desktop me-2"></i> Layouts <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-layout">

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-verti"
                                    role="button">
                                    Vertical <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-verti">
                                    <a href="layouts-dark-sidebar" class="dropdown-item">Dark Sidebar</a>
                                    <a href="layouts-compact-sidebar" class="dropdown-item">Compact Sidebar</a>
                                    <a href="layouts-icon-sidebar" class="dropdown-item">Icon Sidebar</a>
                                    <a href="layouts-boxed" class="dropdown-item">Boxed Layout</a>
                                    <a href="layouts-preloader" class="dropdown-item">Preloader</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-hori"
                                    role="button">
                                    Horizontal <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-hori">
                                    <a href="layouts-horizontal" class="dropdown-item">Horizontal</a>
                                    <a href="layouts-hori-topbar-light" class="dropdown-item">Topbar light</a>
                                    <a href="layouts-hori-boxed-width" class="dropdown-item">Boxed width</a>
                                    <a href="layouts-hori-preloader" class="dropdown-item">Preloader</a>
                                    <a href="layouts-hori-colored-header" class="dropdown-item">Colored Header</a>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </nav>
    </div>
</div>
