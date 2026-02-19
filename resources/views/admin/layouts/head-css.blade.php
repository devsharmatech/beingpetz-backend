@yield('css')
@stack('styles')
<!-- Bootstrap Css -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@5.9.55/css/materialdesignicons.min.css">
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('build/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

<!--<link href="{{ URL::asset('build/icons/materialdesignicons-webfont.ttf') }}" id="app-style" rel="stylesheet" type="text/css" />-->

<!--<link href="{{ URL::asset('build/icons/materialdesignicons-webfont.woff') }}" id="app-style" rel="stylesheet" type="text/css" />-->
