<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" 
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-primary shadow-sm p-1">
            <div class="container">
                <a class="navbar-brand text-white " href="{{ url('/') }}">
                    <img src="{{ asset('images/wnet2020_logo.jpg')}}" width="30" height="30" class="d-inline-block" alt="">
                    {{ config('app.name', 'wnet2020') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        <!-- @if (Route::has('login')) -->
                            <!-- <li class="nav-item"> -->
                                <!-- <a class="nav-link text-white" href="{{ route('login') }}">{{ __('ログイン') }}</a> -->
                            <!-- </li> -->
                        <!-- @endif -->

                        <!-- @if (Route::has('register')) -->
                            <!-- <li class="nav-item"> -->
                                <!-- <a class="nav-link text-white" href="{{ route('register') }}">{{ __('新規登録') }}</a> -->
                            <!-- </li> -->
                        <!-- @endif -->
                    </ul>
                </div>
            </div>
        </nav>
        <main class="py-4">
            @yield('content')
        </main>
        <div style="text-align: center">
            @include ('layouts/components/wnet2020_logo', ['size' => 300])
        </div>
        <div style="text-align: right">
            <a class="btn btn-link" href="/device/delete">
                {{ __('デバイス登録削除') }}
            </a>
        </div>
    </div>
</body>
</html>
