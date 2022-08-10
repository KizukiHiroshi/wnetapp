<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'wnet2020') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" 
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
</head>
<style>
    form { margin:0px; }
    a { text-decoration: none; }
    .pagination { font-size:10pt; }
    .pagination li { display:inline-block; }
    tr th a:link { color: black; }
    tr th a:visited { color: black; }
    tr th a:hover { color: black; }
    tr th a:active { color: black; }
</style>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-primary shadow-sm p-1">
            <div class="container">
                <a class="navbar-brand text-white" href="{{ url('/') }}">
                <img src="{{ asset('images/wnet2020_logo.jpg')}}" width="30" height="30" class="d-inline-block" alt="">
                    {{ config('app.name', 'wnet2020') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto text-white">
                    @yield('title')
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <div class="text-white p-2">
                            @yield('devicename')
                        </div>
                        <!-- Authentication Links -->
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link text-white dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user() ? Auth::user()->name : 'onTest'}}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                    {{ __('ログアウト') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <main class="">
            <div class="row">
                @yield('menu')
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
<script>
    function delete_alert(e) {
        if(!window.confirm('本当に削除しますか？')) {
            window.alert('キャンセルされました'); 
            return false;
        }
        document.deleteform.submit();
    };
</script>