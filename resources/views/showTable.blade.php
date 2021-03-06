<head>
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
</head>


@extends('layouts.app-header')

@section('content')

<div class="container">
    @foreach($teamTables as $teamId=>$tables)
<div class="row">
    <div class="col-sm-12">
    <div id="heads-up">{{$teamsArr[$teamId]}}</div>
    </div>
</div>

<div class="row">
 @foreach($tables as $key=>$val)
    <div class="col-sm-3">
    
        <div class="table-block">
            
            <div class="row">
                <div class="col-sm-12">
                    <a href="/tables/{{$val['id']}}" target="_blank" class="table-name-link">
                        <div class="block-first">
                                <h1 id="heads-up" class="user-table-name">{{ $val['table_name'] }}</h1>
                                <p>{{ $tableCounts[$val['table_id']] }}</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <ul class="nav navbar-nav option-nav">
                            <li>
                                <a onclick="location.href='listFilters/{{$val['id']}}'">
                                    <span>
                                        <i class="fa fa-magic" aria-hidden="true"></i>&nbsp;</span>
                                </a>
                            </li>
                            <li>
                                <a href="">
                                    <span>
                                        <i class="fa fa-users" aria-hidden="true"></i>5</span>
                                </a>
                            </li>
                            <li>
                                <a onclick="location.href='configure/{{$val['id']}}'">
                                    <span>
                                        <i class="fa fa-database" aria-hidden="true"></i>Source</span>
                                </a>
                            </li>
                    </ul>
                </div>
            </div>
           
        </div>
             
    </div>
    @endforeach 
</div>


    @endforeach 
    @if(!empty($readOnlyTables))
    <div>
        <div id="heads-up">Guest Access</div>
        <div>
            @foreach($readOnlyTables as $table)
            <div class="col-xs-3">
                <div class="card">
                    <a href="tables/{{$table['id']}}" target="_blank"></a>
                    <div class="text-center">
                        <div class="tab_name">
                            <a href="tables/{{$table['id']}}" target="_blank"> {{$table['table_name']}}</a>
                    </div>
                </div>
            </div>
            </div>
            @endforeach @endif

        </div>
        
    </div>
   
    @stop

    @section('pagescript')
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(".btn-sources").click(function () {
                var id = $(this).attr("dataid");
                var cls = ".sources-" + id;
                $("#table-sources").html($(cls).html());
            });
        });
        $(document).ready(function () {
        $('#createTable').click(function () {
            $('#create_Table').modal('show');
        });
        $('#CreateTableSubmit').click(function () {
            if ($.trim($('#table_category').val()) == '') {
                    //alert('Create New Table IN can not select.');
                    $.toast({
                        text: 'Create New Table IN can not select.',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });
                    $('#table_category').addClass('has-error');
                return false;
            } else if ($.trim($('#table_name').val()) == '') {
                    //alert('Table Name can not be left blank.');
                    $.toast({
                        text: 'Table Name can not be left blank.',
                        showHideTransition: 'fade',
                        icon: 'error'
                    });
                    $('#table_name').addClass('has-error');
                return false;
            } else {
                    var tableData = [];
                    tableData[0] = {
                        'name': 'Created At',
                        'type': '9',
                        'display': '0',
                        'ordering': '1',
                        'unique': '0',
                        'value': ''
                    };
                    tableData[1] = {
                        'name': 'Updated At',
                        'type': '9',
                        'display': '0',
                        'ordering': '2',
                        'unique': '0',
                        'value': ''
                    };
                var tableName = $('#table_name').val();
                var teamId = $('#table_category').val();
                $.ajaxSetup({
                    header: $('meta[name="_token"]').attr('content')
                })
                $.ajax({
                    url: 'createTable',
                    type: 'POST',
                        data: {
                            tableName: tableName,
                            teamId: teamId,
                            tableData: tableData
                        },
                    dataType: 'json',
                    success: function (info) {
                        $('#createTable').attr("disabled", false);
                            $('#createTable').html(
                                '<i class="glyphicon glyphicon-plus"></i> Create');
                            if (info.error) {
                            //alert(info.msg);
                            $.toast({
                                text: info.msg,
                                showHideTransition: 'slide',
                                icon: 'error'
                            });
                            return false;
                        }
                        //alert(info.msg);
                        $.toast({
                            text: info.msg,
                            showHideTransition: 'slide',
                            icon: 'succes'
                        });
                        window.location.href = "tables";
                    }

                });
            }
        });
    });
    </script>
    @stop
    @section('models')
    <div id="src_modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header login-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title">source details</h3>
                </div>
                <form>
                    <div class="modal-body">
                        <h4>You are currently receiving data from sources -</h4>
                        <div id="table-sources"></div>
                    </div>

                    <div class="modal-footer" style="overflow: hidden">
                        <input type="hidden" id="eId"/>
                        <input type="hidden" id="tokenKey"/>
                        <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                        <button type="button" class="btn btn-success" data-dismiss="modal" target="_blank" onclick="window.open('https://viasocket.com/')">
                            Add more source
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="create_Table" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div style="background:rgb(237,239,240)" class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid rgba(0,0,0,0.2);">
                    <img style="width:21px;height:21px;vertical-align:middle" src="http://localhost:8080/img/docs.svg" alt="docs">
                    <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" class="modal-title">Create Table</span>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body" style="padding: 0px;">
                    <div class="col-xs-12">
                        <div class="panel-body panel-table">
                            <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    Team
                                    <?php
                                    $teamArr = Session::get('teams');
                                    ?>
                                    {{ Form::select('teamName', [$teamArr], null, ['class' => 'form-control', 'id' => 'table_category']) }}
                                </div>
                                </div>
                                </div>
                                <div class="row">
                                <div class="col-sm-6">
                                <div class="form-group">
                                        Table Name
                                        <input type="text" placeholder="Table Name" name="table_name" id="table_name"
                                            class="form-control order order-input" />
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" style="width:75px;height:40px" class="btn btn-success btn-submit" id="CreateTableSubmit">
                        Create
                    </button>
                </div>

               
            </div>
        </div>
    </div>
    <!-- End Modal -->

    @stop