<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Contact UI') }}</title>

        <!-- Styles -->
         <link rel="icon" type="image/png" href="{{url('favicon.png')}}" />
        <link href="{{ asset('css/normalize.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('css/app.css') }}" rel="stylesheet" />
        <link href="{{ url('css/font-awesome.min.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="{{url('css/style2.css')}}" type="text/css" /> <!-- Resource style -->
        <link type="text/css" href="{{url('css/jquery-ui.css')}}" rel="Stylesheet" />    
        <script src="{{url('js/modernizr.js')}}"></script> <!-- Modernizr -->
        <!-- <script type="text/javascript" src="{{url('js/jquery-1.4.4.js')}}"></script> -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{{url('js/jquery-ui.min.js')}}"></script>
        <script src="{{ url('js/bootstrap.min.js') }}"></script>
          <!-- <link rel="icon" type="image/png" href="{{url('img/logo.png')}}"> -->
    </head>
    <body>
            <nav class="navbar navbar-default">
            <div class="container">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{route('tables')}}" id="logo">
                        <span><img src="{{ asset('img/logo.png') }}" alt="user table logo" /></span>
                        <span class="extrabold">user</span>
                        <span class="light">TABLE</span>
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{{ isset(Auth::user()->first_name) ? Auth::user()->first_name : Auth::user()->email }}} <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ route('profile') }}">
                                Profile
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>

                    </ul>
                </li>

                </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
            </nav>
            <div id="main-wrp">
                <div class="body">
                    <!--
                    <nav class="navbar navbar-default navbar-static-top">
                        <div class="container">
                            <div class="navbar-header">

                                 Collapsed Hamburger
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                                    <span class="sr-only">Toggle Navigation</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>

                                Branding Image
                                <a class="navbar-brand" href="{{ url('/') }}">
                                    {{ config('app.name', 'Laravel') }}
                                </a>
                            </div>

                            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                                 Left Side Of Navbar
                                <ul class="nav navbar-nav">
                                    &nbsp;
                                </ul>


                            </div>
                        </div>
                    </nav> -->

                    @yield('content')
                </div>
            </div>
        @yield('pagescript')
        @yield('models')

    </body>
</html>