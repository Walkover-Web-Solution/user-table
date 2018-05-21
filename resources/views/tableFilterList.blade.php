@extends('layouts.app-header')

@section('content')
<link rel="stylesheet" href="{{ asset('css/toast.css')}}"><!--ToastCSS-->
        <div class="container mt20">
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
                            @if(!empty($tabData))
                            <table id="table-filter" class="table basic table-bordred">
                                <thead><tr id="0"><th>Name</th><th>Sequence</th><th>Count</th><th>Delete</th></tr></thead>
                                @foreach($tabData as $key => $tab)
                                <tr id="{{$tab['id']}}">
                                    <td>{{$tab['tab_name']}}</td>
                                    <td>{{($tab['sequence'] != 0) ? $tab['sequence'] : ($key+1)}}</td>
                                    <td>{{$tabCount[$key][$tab['tab_name']]}}</td>
                                    <td><a href="javascript:void(0)" class="remove-row" onclick="deleteFilter({{$tab['id']}})"><i class="glyphicon glyphicon-trash"></i></a></td>
                                </tr>
                                @endforeach
                            </table>
                            <div class="modal-footer">
                                <button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="updateFilterSequence()">
                                    Save
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
@stop

@section('pagescript')
<script type="text/javascript" src="{{  asset('js/toast.js')}}"></script> <!--Toast JS-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/plugins/jquery.tablednd.js"></script>
<script type="text/javascript">
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
    var tableId = '<?php echo $tableData['id'];?>';
    var rowIndex = 1;
</script>
<script>
    $("#table-filter").tableDnD();
    function updateFilterSequence(){
        var tablearray = [];
        $("#table-filter tr").each(function() {
            if(this.id != 0)
            {
                tablearray.push(this.id);
            }
        });
        if(tablearray.length > 1)
        {
            $.ajax({
                url: API_BASE_URL + '/updatelistFilters',
                type: 'POST',
                data: {'tablearray' : tablearray},
                dataType: 'json',
                success: function (info) {
                    if(info.status == 'error')
                    {
                        alert(info.error);
                        return false;
                    }
                    $.toast({
                        heading: 'Success',
                        text: info.message,
                        showHideTransition: 'slide',
                        icon: 'success',
                        afterHidden: function() {
                            location.reload();
                        }
                    });
                }
            });
        }
    }
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