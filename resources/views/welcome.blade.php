<!DOCTYPE html>
<html lang="en">

<head>
    <title>userTABLE</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="favico.png" />

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-W6JKH5J');</script>
    <!-- End Google Tag Manager -->


    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}"  crossorigin="anonymous">

    <link href="{{ asset('css/normalize.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="{{ asset('css/user-table.css') }}" rel="stylesheet">

     <script src="{{ asset('js/jquery.min.js') }}"></script> 

    <script src="{{ asset('js/bootstrap.min.js') }}" crossorigin="anonymous"></script>

</head>

<body>

    <header class="purple-bg">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>                        
        </button>
                    <a class="navbar-brand" href="#" id="logo">
                        <span><img src="./img/logo.png" alt="user table logo" /></span>
                        <span class="extrabold">user</span>
                        <span class="light">TABLE</span>
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="myNavbar">
                    <!-- <a href="https://viasocket.typeform.com/to/SBBljH" target="_blank" class="typeform-share link signup-btn lg-btn btn btn-default navbar-btn pull-right">Sign up</a>
                    <a id="lg-btn" href="login.php" class="lg-btn btn btn-default navbar-btn pull-right">Log in</a>
 -->
                    @if (Route::has('login'))
                    <div class="top-right links">
                        @auth
                        <a href="{{ url('/tables') }}" class="lg-btn btn btn-default navbar-btn pull-right">Home</a>
                        @else
                        <a href="{{env('SOCKET_LOGIN_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin"  class="lg-btn btn btn-default navbar-btn pull-right">Login</a>
                        <!--<a href="https://viasocket.com/login?token_required=true&redirect_uri=http://contact-crm-test.herokuapp.com/socketlogin">Login</a>-->
                        <a href="{{env('SOCKET_SIGNUP_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin" class="typeform-share link signup-btn lg-btn btn btn-default navbar-btn pull-right">Register</a>
                        <!--<a href="https://viasocket.com/signup?token_required=true&redirect_uri=http://contact-crm-test.herokuapp.com/socketlogin">Register</a>-->
                        @endauth
                    </div>
                    @endif

                </div>
            </div>
        </nav>
        <section class="container text-center">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="fs46 extralight wht_txt">Do you <span class="extrabold">have Clients?</<span></h1> 
                    <h2 class="light h2 intro"><span class="wht_txt fs30">We have </span><span class="extrabold wht_txt fs40">user</span><span class="fs40">TABLE</span></h2>
                    <figure><img src="./img/table.png" alt="usertable" /></figure>
                    <p class="fs28 wht_txt">SEE. ACT. AUTOMATE.</p>
                </div>
            </div>
        </section>
    </header>

    <section class="gray-bg" id="features">
        <div class="container text-center">
            <div class="row">
                <div class="col-xs-12">
                    <h3>WHAT’S ON THE TABLE</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <figure><img src="./img/data.png" alt="segregated data"></figure>
                    <h4>ALL DATA, SEGREGATED</h4>
                    <p><strong>Collect all data here </strong>from different sources and manage.</p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <figure><img src="./img/analyse.png" alt="analyse data"></figure>
                    <h4>ANALYSE</h4>
                    <p>Create different filters for <strong>different purpose</strong> and know what is going on with your client?</p>
                </div>
                <div class="col-md-4 col-sm-12">
                    <figure><img src="./img/automate.png" alt="automate data"></figure>
                    <h4>AUTOMATE</h4>
                    <p>Automate SMS, Email on Different filter, Eg, Purchase above 1M, <strong>send some rewards</strong>.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="" id="glimpse-sec">
        <div class="container text-center">
            <div class="row">
                <div class="col-xs-12">
                    <h3>In A Glimpse</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <figure>
                        <img src="./img/glimpse.png" class="img-responsive" alt="Image">
                    </figure>
                </div>
            </div>
        </div>
    </section>


    <section class="dark-bg" id="sub-footer">
        <div class="container text-center">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="wht_txt">SEE IN ACTION</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center" id="demo-div">
                    <div>
                        <span>Demo</span>
                        <img src="./img/line.png" alt="line" />
                    </div>
                    <div>
                        <p class="fs20 prpl_txt">or</p>
                    </div>
                    <div>
                        <span>Start</span>
                        <img src="./img/line.png" alt="line" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <!-- <p class="fs18 wht_txt no-margin"></p> -->
                    <a href="https://viasocket.typeform.com/to/SBBljH" class="typeform-share link btn btn-lg skype-btn">Talk to an Expert</a>
                    <!-- <a class="" href="">pushpendraagrawal</a> -->
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="jumbotron">
                        <div class="fs50 wht_txt extrabold">Setup $100 + $50/mo</div>
                        <p class="fs22 prpl_txt">Unlimited User | Unlimited Admin</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <p class="love">Built with <span style="color: red">&hearts;</span> by <a class="und" href="https://www.walkover.in" target="_Blank">walkover.in</a> for internal use</p>
                    <p class="hidden-md hidden-lg">&nbsp;</p>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <p>
                        <span>© 2017 USER TABLE</span> . <a href="#">Home</a> . <a href="#">Terms of use</a> . <a href="#">Privacy policy</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>


<!-- ProductHunt modal -->

<div class="modal fade bs-example-modal-lg" tabindex="-1" id="huntModal" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <div class="img_wrap">
      <img src="img/huntcat.jpg" alt="userTable producthunt" />
    </div>
    <div class="content_wrap">
        <h1>Meow! Product Hunters</h1>
        <p>Signup today for a starter, Get 2 months for FREE. <br />Exclusive offer for the Product Hunt Community.</p>
        <a class="btn btn-lg btn-hunt typeform-share link" target="_blank" href="https://viasocket.typeform.com/to/SBBljH">Sign up Today <i class="glyphicon glyphicon-heart like"></i>  Get 2 months FREE</a>
      </div>
    </div>
  </div>
</div>


<!-- typeform -->
 <script> (function() { var qs,js,q,s,d=document, gi=d.getElementById, ce=d.createElement, gt=d.getElementsByTagName, id="typef_orm_share", b="https://embed.typeform.com/"; if(!gi.call(d,id)){ js=ce.call(d,"script"); js.id=id; js.src=b+"embed.js"; q=gt.call(d,"script")[0]; q.parentNode.insertBefore(js,q) } })() </script>


<script>
    // debugger;
    var searchContain = window.location.search;
    if (searchContain) {
        searchContain = searchContain.split("=")
    }
    if (searchContain.length > 1 && searchContain[1] === 'producthunt') {
        $('#huntModal').modal('show') 
    }
</script>

</body>

</html>