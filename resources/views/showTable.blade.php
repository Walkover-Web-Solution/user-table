@extends('layouts.app-header')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <div class="card" onclick="location.href='{{ route('createTable') }}'">
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

<<<<<<< HEAD
                            <div class="center-block btn-grp text-center">
                                <button class="btn btn-primary" onclick="location.href='configure/{{$val['id']}}'">Configure</button>
                                <button id="srcbtn" dataid="{{$val['id']}}" data-keyboard="true" data-target="#src_modal" data-toggle="modal" class="btn btn-default btn-sources" title="{{ isset($source_arr[$val['id']]) ? implode(',',$source_arr[$val['id']]) : "Your content goes here" }}">{{isset($source_arr[$val['id']] )? count($source_arr[$val['id']]) : 0}} sources</button>
                            </div>
                          
                            <div class="sources-container sources-{{$val['id']}}">
                                @foreach($source_arr[$val['id']] as $key => $sources)
                                {{$sources}}<br/>
                                @endforeach
=======
                            <div class="btn-grp text-center">
                                <button class="btn btn-primary btn-sm" onclick="location.href='configure/{{$val['id']}}'">Configure</button>
                                <button id="srcbtn" data-keyboard="true" data-target="#src_modal" data-toggle="modal" class="btn btn-default btn-sm" title="{{ isset($source_arr[$val['id']]) ? implode(',',$source_arr[$val['id']]) : "Your content goes here" }}">{{isset($source_arr[$val['id']] )? count($source_arr[$val['id']]) : 0}} sources</button>
>>>>>>> test
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    @endforeach
    </div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $(".btn-sources").click(function(){
       var id = $(this).attr("dataid");
       var cls = ".sources-" + id;
       $("#table-sources").html($(cls).html());
    });
});
</script>
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
<<<<<<< HEAD
                    <strong>The table connected with following sources:</strong>
                    <div id="table-sources"></div>
=======
                    <h4>You are currently receiving data from sources -</h4>
                    <ul>
                        <li>dummy source #1</li>
                        <li>dummy source #2</li>
                        <li>dummy source #3</li>
                    </ul>
>>>>>>> test
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

@stop