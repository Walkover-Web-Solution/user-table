@extends('layouts.app-header')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-xs-3">
            <div class="card" onclick="location.href ='{{ route('createTable') }}'">
                <div>
                    <div class="text-center">
                        <i id="iii">+</i><br>
                        <span id="new_table">New Table</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @foreach($teamTables as $teamId=>$tables)
    <div class="row">

        <div id="heads-up">{{$teamsArr[$teamId]}}</div>
        <div>
            @foreach($tables as $key=>$val)
            <div class="col-xs-3">
                <div class="card">
                    <a href="tables/{{$val['id']}}" target="_blank"></a>
                    <div class="text-center">
                        <div class="tab_name"><a href="tables/{{$val['id']}}" target="_blank"> {{$val['table_name']}}</a></div>

                        <div class="center-block btn-grp text-center">
                            <button class="btn btn-primary" onclick="location.href='configure/{{$val['id']}}'">Configure</button>
                            <button id="srcbtn" dataid="{{$val['id']}}" data-keyboard="true" data-target="#src_modal" data-toggle="modal" class="btn btn-default btn-sources" title="{{ isset($source_arr[$val['id']]) ? implode(',',$source_arr[$val['id']]) : "Your content goes here" }}">{{isset($source_arr[$val['id']] )? count($source_arr[$val['id']]) : 0}} sources</button>
                        </div>

                        <div class="sources-container sources-{{$val['id']}}">
                            <ul>
                                @if(isset($source_arr[$val['id']]))
                                @foreach($source_arr[$val['id']] as $key => $sources)
                                <li>{{$sources}}</li>
                                @endforeach
                                @endif    
                            </ul>    
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @if(!empty($readOnlyTables))
    <div class="row">
        <div id="heads-up">Guest Access</div>
        <div>
            @foreach($readOnlyTables as $table)
            <div class="col-xs-3">
                <div class="card">
                    <a href="tables/{{$table['id']}}" target="_blank"></a>
                    <div class="text-center">
                        <div class="tab_name"><a href="tables/{{$table['id']}}" target="_blank"> {{$table['table_name']}}</a></div>
                    </div>
                </div>
            </div>
            @endforeach

            @endif
        </div>
    </div>
    @stop

    @section('pagescript')
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $(".btn-sources").click(function(){
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
                alert('Create New Table IN can not select.');
                return false;
            } else if ($.trim($('#table_name').val()) == '') {
                alert('Table Name can not be left blank.');
                return false;
            } else {
                var tableName = $('#table_name').val();
                var teamId = $('#table_category').val();
                $.ajaxSetup({
                    header: $('meta[name="_token"]').attr('content')
                })
                $.ajax({
                    url: 'createTable',
                    type: 'POST',
                    data: {tableName: tableName, teamId: teamId},
                    dataType: 'json',
                    success: function (info) {
                        $('#createTable').attr("disabled", false);
                        $('#createTable').html('<i class="glyphicon glyphicon-plus"></i> Create');
                        if (info.error)
                        {
                            alert(info.msg);
                            return false;
                        }
                        alert(info.msg);
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
                <div class="modal-header">
                    <img style="width:21px;height:21px;vertical-align:middle" src="http://localhost:8080/img/docs.svg" alt="docs">
                    <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" class="modal-title">Create Table</span>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body" style="padding: 0px;">
                    <div class="col-xs-12">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group">
                                    Create New Table IN
                                    <?php
                                    $teamArr = Session::get('teams');
                                    ?>
                                    {{ Form::select('teamName', [$teamArr], null, ['class' => 'form-control', 'id' => 'table_category']) }}
                                </div>
                                <div class="form-group">
                                    Table Name : <input type="text" placeholder="Table Name" name="table_name" id="table_name" class="form-control order order-input" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                    <button type="button" style="width:75px;height:40px" class="btn btn-success" data-dismiss="modal" id="CreateTableSubmit">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    @stop