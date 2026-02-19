<?php
$settings = \App\Models\Setting::first();
$site_title = $settings->site_name ?? 'Lawform - Admin Panel';
$favicon = isset($settings->favicon) ? asset('storage/' . $settings->favicon) : asset('build/images/favicon.ico');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | {{ $site_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ $favicon }}">

    <!-- include head css -->
    @include('admin.layouts.head-css')
</head>

<body class="authentication-bg bg-primary">

    @yield('content')

    <!-- vendor-scripts -->
    @include('admin.layouts.vendor-scripts')

</body>

</html>
