@extends('layouts.app-header')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <div class="card card-new cp" onclick="location.href='{{ route('createTable') }}'">
                    <div>
                        <div class="center-block text-center">
                             <i class="">+</i>
                             <h1>New Table</h1>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @foreach($teamTables as $teamId=>$tables)
        <div class="row">
            
            <div id="heads-up" class="card-header">{{$teamsArr[$teamId]}}</div>
            <div>
            @foreach($tables as $key=>$val)
                <div class="col-xs-3">
                    <div class="card">
                    <a href="tables/{{$val['id']}}" class="card_link" target="_blank"></a>
                        <div class="text-center">
                            <h1 style="margin-top:0px" class="card-title"><a href="tables/{{$val['id']}}" target="_blank"> {{$val['table_name']}}</a></h1>

                            <div class="center-block btn-grp text-center">
                                <button class="btn btn-primary" onclick="location.href='configure/{{$val['id']}}'">Configure</button>
                                <button id="srcbtn" data-keyboard="true" data-target="#src_modal" data-toggle="modal" class="btn btn-default" title="{{ isset($source_arr[$val['id']]) ? implode(',',$source_arr[$val['id']]) : "Your content goes here" }}">{{isset($source_arr[$val['id']] )? count($source_arr[$val['id']]) : 0}} sources</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    @endforeach
    </div>

    <div id="src_modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header login-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="modal-title">source details</h4>
                </div>
                <form>
                    <div class="modal-body">
                    </div>

                    <div class="modal-footer" style="overflow: hidden">
                        <input type="hidden" id="eId"/>
                        <input type="hidden" id="tokenKey"/>
                        <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                        <button type="button" class="btn btn-success" data-dismiss="modal" target="_blank" onclick="window.open('https://viasocket.com/')">
                            Add another source...
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop