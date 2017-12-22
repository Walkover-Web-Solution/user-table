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
        <link href="{{ url('css/app.css') }}" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
        <link rel="stylesheet" href="{{url('css/reset.css')}}"> <!-- CSS reset -->
        <link rel="stylesheet" href="{{url('css/style.css')}}"> <!-- Resource style -->
        <link rel="stylesheet" href="{{url('css/style2.css')}}"> <!-- Resource style -->  
        <script src="{{url('js/modernizr.js')}}"></script> <!-- Modernizr -->
        <link type="text/css" href="{{url('css/jquery-ui.css')}}" rel="Stylesheet" />
        <script type="text/javascript" src="{{url('js/jquery3.2.1.min.js')}}"></script>
        <script type="text/javascript" src="{{url('js/jquery-ui.min.js')}}"></script>
        <link rel="icon" type="image/png" href="{{url('favicon.png')}}" />
    </head>
    <body>
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
