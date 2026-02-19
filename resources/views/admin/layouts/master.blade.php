<?php

?>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>Being Petz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="#">
    <link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- include head css -->
    @include('admin.layouts.head-css')
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- topbar -->
        @include('admin.layouts.topbar')

        <!-- sidebar components -->
        @include('admin.layouts.sidebar')

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                @yield('content')
            </div>
            <!-- end page content-->

            <!-- footer -->
            @include('admin.layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- customizer -->
    {{-- @include('admin.layouts.right-sidebar') --}}

    <!-- vendor-scripts -->
    @include('admin.layouts.vendor-scripts')

</body>

</html>
