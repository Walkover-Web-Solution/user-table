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
            <div class="card">
            <div>{{$teamsArr[$teamId]}}</div>
            @foreach($tables as $key=>$val)
                <div class="col-xs-3">
                    <div class="card">
                    <a href="tables/{{$val['id']}}" class="card_link" target="_blank"></a>
                        <div class="text-center">
                            <h1 class="title"><a href="tables/{{$val['id']}}" target="_blank"> {{$val['table_name']}}</a></h1>

                            <div class="center-block btn-grp text-center">
                                <button class="btn btn-primary btn-md" onclick="location.href='configure/{{$val['id']}}'">Configure</button>
                                <button class="btn btn-default btn-md" title="{{ isset($source_arr[$val['id']]) ? implode(',',$source_arr[$val['id']]) : "Your content goes here" }}">{{isset($source_arr[$val['id']] )? count($source_arr[$val['id']]) : 0}} sources</button>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    @endforeach
    </div>
@stop