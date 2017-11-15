<!DOCTYPE html>
<html lang="en">

<head>
    <title>userTABLE Mockup</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap core CSS -->
    <!--<link rel="stylesheet" href="css/bootstrap.min.css" />-->

<!--    <link href="./css/reset.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">-->

    <link href="{{ asset('css/reset.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
<!--    <script src="js/jquery.min.js"></script>

    <script src="js/bootstrap.min.js"></script>-->

</head>

<body>

    <div class="container">
        <div class="col-xs-4">
            <div class="card card-new cp" onclick="location.href='/createTable'">
                <div>
                    <div class="center-block text-center">
                         <i class="glyphicon glyphicon-plus"></i>
                    </div>

                    <h1>New Table</h1>
                </div>
            </div>
        </div>
        @foreach($allTables as $key=>$val)
        <div class="col-xs-4">
            <div class="card">
                <div>
                    <h1>{{$val['table_name']}}</h1>
                    <div class="center-block text-center">
                        <button class="btn btn-primary btn-sm" onclick="location.href='/tables/{{$val['id']}}'">Configure</button>
                        <button class="btn btn-default btn-sm">2 sources</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    
    </div>

</body>

</html>

