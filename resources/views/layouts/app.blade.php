<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-523353K');</script>
        <!-- End Google Tag Manager -->
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
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-523353K"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
<div class="loading-box">
    <div class="loader-icon"></div>
</div>
            <div id="main-wrp">
                <div class="body">
                    @yield('content')
                </div>
            </div>
        @yield('pagescript')
        @yield('models')

    </body>
</html>
