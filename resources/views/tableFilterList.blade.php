@extends('layouts.app-header')

@section('content')
        <div class="container">
            <div class="row">
                <!--  new field form -->
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-2">
                                   <a href="{{env('APP_URL')}}/tableaccess/{{$tableData['id']}}">Table Access</a>
                                </div>
                                <div class="col-xs-2">
                                   <a href="{{env('APP_URL')}}/configure/{{$tableData['id']}}">Configure</a>
                                </div>
                                <div class="col-xs-2 active">
                                   <a href="{{env('APP_URL')}}/listFilters/{{$tableData['id']}}">Filters</a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-heading">Team Name :   <label>{{Session::get('teams')[$tableData['team_id']]}}</label></div>
                        <div class="panel-heading">Table Name :   <label>{{$tableData['table_name']}}</label></div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-xs-4">Name</div>
                                <div class="form-group col-xs-4">Count</div>
                                <div class="form-group col-xs-4">Delete</div>
                            </div>
                            @foreach($tabData as $key => $tab)
                            <div class="row" id="filter-{{$tab['id']}}">
                                <div class="form-group col-xs-4">
                                    {{$tab['tab_name']}}
                                </div>
                                <div class="form-group col-xs-4">
                                    {{$tabCount[$key][$tab['tab_name']]}}
                                </div>
                                <div class="form-group col-xs-4">
                                    <a href="javascript:void(0)" class="remove-row" onclick="deleteFilter({{$tab['id']}})"><i class="glyphicon glyphicon-trash"></i></a>
                                </div>
                            </div>
                             @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
@stop

@section('pagescript')
<script type="text/javascript">
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
    var tableId = '<?php echo $tableData['id'];?>';
    var rowIndex = 1;
</script>
<script>
    function deleteFilter(id){
        $.ajax({
            url: API_BASE_URL + '/deleteFilter',
            type: 'POST',
            data: {tableId: tableId, filterId: id},
            dataType: 'json',
            success: function (info) {
                $('#filter-'+id).closest('.row').text('');
                if(info.error)
                {
                    alert(info.msg);
                    return false;
                }
                alert(info.message);
            }
        });
    }
</script>
@stop