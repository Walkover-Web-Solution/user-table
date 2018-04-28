@extends('layouts.app')
    <!-- <a href="{{env('APP_URL')}}/graph/{{$tableId}}">Table Graph</a> -->
@section('content')
    <div class="tablist">
        <ul id="tablist">
            <li><a href="{{route('tables')}}"><i class="glyphicon glyphicon-chevron-left"></i> Back to Tables</a></li>
            <li role="presentation">
            <a href="{{env('APP_URL')}}/tables/{{$tableId}}">Table</a>
        </li>
        @foreach($arrTabCount as $tabDetail)
        @foreach($tabDetail as $tabName => $tabCount)

        <li role="presentation">
            <!--<a href="{{ collect(request()->segments())->last() }}/{{$tabName}}">{{$tabName}}-->
            <a href="{{env('APP_URL')}}/graph/{{$tableId}}/filter/{{$tabName}}">{{$tabName}} ({{$tabCount}})
            </a>
        </li>
        @endforeach
        @endforeach
            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-right user_dropdown">
                <!-- Authentication Links -->
                @guest
                    <li><a href="{{env('SOCKET_LOGIN_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Login</a></li>
                    <li><a href="{{env('SOCKET_SIGNUP_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Register</a></li>
                @else
                    <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                        <!-- {{ Auth::user()->name }}  -->
                        {{$userTableName}} <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </a>

                    <ul class="dropdown-menu">
                    <li><a href="{{route('tables')}}">Dashboard</a></li>
                    <!-- <li><a href="{{env('APP_URL')}}/graph/{{$tableId}}">Table Graph</a></li>                 -->
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

                @endguest
            </ul>
            <form class="search-form pull-right" action="" name="queryForm"
                  onsubmit="searchKeyword(event, query.value)">
                <label for="searchInput"><i class="fa fa-search"></i></label>
                <input type="text" name="query" class="form-control" placeholder="Search for..."
                       aria-label="Search for..." id="searchInput">
            </form>
        </ul>
    </div>

    <div class="nav-and-table  from-right nav-table-custom-response">
        <div id="user-board" class="user-dashboard user-custom-dashboard">
            <!-- Tab panes -->
            <div class="scroll-x">
                <div class="scroll-y graph-page-container" id="response">
                    <div class="top-chart-container">
                        <div class="ajax-loader-container">
                            <div class="loading-icon">
                                <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        <form class="form-inline graph-form">

                            <div class="form-group" style="display:none;">
                                <label for="email"  class="control-caption">Column</label>
                                <select class="form-control" id="column2">
                                    @foreach( $other_columns as $other_column)
                                        <option value="{{$other_column}}">{{$other_column}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email" class="control-caption">Date Column </label>
                                <select class="form-control" id="column1">
                                    @foreach( $date_columns as $date_column)
                                        <option value="{{$date_column}}">{{$date_column}}</option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group">
                                <label for="email"  class="control-caption">Date Range</label>
                                <input class="form-control" id="barDate" name="date" placeholder="MM/DD/YYY" type="text" value="{{$rangeStart}}"/>
                                To
                                <input class="form-control" id="barDate1" name="date1" placeholder="MM/DD/YYY" type="text" value="{{$rangeEnd}}"/>
                            </div>
                            <button type="button" class="btn btn-primary" id="btnLoadGraph">Load Graph</button>
                        </form>
                        <div class="charts-container">
                            <div class="chart-first">
                                <canvas id="myChart" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                    <form class="form-inline graph-form">
                        <div class="form-group">
                            <label for="email" class="control-caption">Date Column </label>
                            <select class="form-control" id="column3">
                                @foreach( $date_columns as $date_column)
                                    <option value="{{$date_column}}">{{$date_column}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email"  class="control-caption">Date Range</label>
                            <input class="form-control" id="pieDate" name="pieDate" placeholder="MM/DD/YYY" type="text"  value="{{$rangeStart}}"/>
                            To
                            <input class="form-control" id="pieDate1" name="pieDate1" placeholder="MM/DD/YYY" type="text"  value="{{$rangeEnd}}"/>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnLoadGraph1">Load Graph</button>
                    </form>

                    <div class="charts-container">
                        <div class="pie-chart-container row">
                            @foreach( $other_columns as $other_column)
                                <div class="pie-chart col-md-2 col-lg-2 col-sm-4 col-xs-6">
                                    <div class="column-caption">Column : {{$other_column}}</div>
                                    <canvas id="id_{{$other_column}}" width="200" height="200"></canvas>
                                </div>
                            @endforeach
                            <div class="clearfix"></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@stop
@section('pagescript')
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{asset('js/templates.js')}}"></script>
    <script src="{{asset('js/functions.js')}}"></script>
    <script src="{{asset('js/Chart.bundle.js')}}"></script>
    <script src="{{asset('js/bootstrap-datepicker.min.js')}}"></script>
    <link href="{{ asset('css/bootstrap-datepicker3.css') }}" rel="stylesheet">

    <script type="text/javascript">
        var API_BASE_URL = "{{env('API_BASE_URL')}}";
        var activeTab = "{{$activeTab}}";
        var tableId = "{{$tableId}}";
    </script>
    <!-- inline scripts -->
    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        function CreatePieChart(element, labels,values,colors,bcolors) {
            var ctx = document.getElementById(element).getContext('2d');
            data = {
                "labels": labels,
                "datasets": [{
                    "label": "",
                    "data": values,
                    "backgroundColor": colors
                }]
            };
            var myPieChart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    legend: {display: false}
                }
            });
        }
        function CreateBarChart(element, labels, values,colors,bColors) {
            if(window.mybarChart == undefined)
            {
                var ctx = document.getElementById(element).getContext('2d');
                window.mybarChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '',
                            data: values,
                            backgroundColor: colors,
                            borderColor:bColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }else{
                var newData = {
                    labels: labels,
                    datasets: [{
                        label: '',
                        data: values,
                        backgroundColor: colors,
                        borderColor:bColors,
                        borderWidth: 1
                    }]
                };
                window.mybarChart.data.datasets[0].data = values;
                window.mybarChart.data.labels = labels;
                window.mybarChart.update();
            }
        }
        //Get Graph functions.
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
        function random_rgba() {
            var o = Math.round, r = Math.random, s = 255;
            var opacity = 0.5;// r().toFixed(1);

            return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + opacity + ')';
        }
        function getGraphData(dateColumn, secondColumn,startDate,endDate) {
            var tableName = "{{$tableId}}";
            var tabName = "{{$activeTab}}";
            var dataUrl = "{{env('APP_URL')}}/graphdata?tabName=" + tabName + "&startDate="+ startDate + "&endDate=" + endDate + "&tableName=" + tableName + "&dateColumn=" + dateColumn + "&secondColumn=" + dateColumn;
            $.get(dataUrl, function (response) {
                var data = JSON.parse(response);
                var Total_data = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    Total_data += item.Total;
                }
                var thurshold = Total_data / 25;
                //console.log(thurshold);
                var dates = new Array();
                var values = new Array();
                var colors = new Array();
                var bcolors = new Array();
                var others_count = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                   // if(item.Total > thurshold) {
                        dates.push(item.LabelColumn);
                        values.push(item.Total);
                        colors.push(random_rgba());
                        bcolors.push("rgba(100,100,100,1)");
                   // }else{
                   //     others_count += item.Total;
                  //  }
                }
                //dates.push("Others");
                //values.push(others_count);
                //colors.push(random_rgba());
               // bcolors.push("rgba(100,100,100,1)");
                //console.log(colors);
                CreateBarChart("myChart", dates,values,colors,bcolors);
                $(".top-chart-container .ajax-loader-container").hide();
            });
        }
        function getPieGraphData(dateColumn, secondColumn, element) {

            var startDate = $("#pieDate").val();
            var endDate = $("#pieDate1").val();

            var tableName = "{{$tableId}}";
            var tabName = "{{$activeTab}}";
            var dataUrl = "{{env('APP_URL')}}/graphdata?tabName="+ tabName + "&startDate="+ startDate+ "&endDate=" + endDate + "&tableName=" + tableName + "&dateColumn=" + dateColumn + "&secondColumn=" + secondColumn;
            $.get(dataUrl, function (response) {
                var data = JSON.parse(response);
                var Total_data = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    Total_data += item.Total;
                }
                var thurshold = Total_data / 25;

                var dates = new Array();
                var values = new Array();
                var colors = new Array();
                var bcolors = new Array();

                var others_count = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    if(item.Total > thurshold) {
                        dates.push(item.LabelColumn);
                        values.push(item.Total);
                        colors.push(random_rgba());
                        bcolors.push("rgba(100,100,100,1)");
                    }else{
                        others_count += item.Total;
                    }
                }
                
                dates.push("Others");
                values.push(others_count);
                colors.push(random_rgba());
                bcolors.push("rgba(100,100,100,1)");
                
                
                //console.log(colors);
                if( dates.length > 1){
                     $("#" + element ).parent().show();
                     CreatePieChart(element, dates,values,colors,bcolors);
                }
                else  
                    $("#" + element ).parent().hide();
            });
        }
        function loadGraph() {
            $(".top-chart-container .ajax-loader-container").show();
            var column1 = $("#column1").val();
            var barDate = $("#barDate").val();
            var barDate1 = $("#barDate1").val();
            console.log("StartDate : " , barDate);
            console.log(barDate1);
            getGraphData(column1, column1,barDate,barDate1);
        }
        $(document).ready(function ($) {

            $("#btnLoadGraph").click(function($){
                loadGraph();
            });
            //Load the Initial Graph Data//
            var c1length = $('#column1 > option').length;
            var c2length = $('#column2 > option').length;
            if (c1length > 0 && c2length > 0){
                $('#column1 option:first-child').attr("selected", "selected");
                $('#column2 option:first-child').attr("selected", "selected");
                loadGraph();
            } else{
                alert("Cannot Load Graph");
            }

            var date_input = $('input[name="date"]');
            var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
            var options = {
                format: 'mm/dd/yyyy',
                container: container,
                todayHighlight: true,
                autoclose: true,
            };
            date_input.datepicker(options);
            var date_input1 = $('input[name="date1"]');
            date_input1.datepicker(options);

            var date_input3 = $('input[name="pieDate"]');
            date_input3.datepicker(options);
            var date_input4 = $('input[name="pieDate1"]');
            date_input4.datepicker(options);

             $("#btnLoadGraph1").click(function($){
                 createAllPieCharts();
            });
        });
       
        function createAllPieCharts(){
            var column3 = $("#column3").val();
            @foreach($other_columns as $other_column)
            getPieGraphData(column3, "{{$other_column}}", "id_{{$other_column}}");
            @endforeach
        }
        createAllPieCharts();

    </script>
@stop
@section('models')

    <!-- Modal -->
    <div id="edit_user" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <!-- <div class="modal-header login-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="modal-title">Edit User</h4>
                </div> -->
                <form id="editUserDetails">
                    <div class="modal-body" id="edit_users_body">
                    </div>

                    <div class="modal-footer">
                        <input type="hidden" id="eId"/>
                        <input type="hidden" id="tokenKey"/>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" data-dismiss="modal" onclick="editUserData()">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Modal -->
<script>

$(document).ready(function(){
    $('.dropdowncolumn').hover(function() {
  $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
}, function() {
  $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
});
});     

</script>
@stop