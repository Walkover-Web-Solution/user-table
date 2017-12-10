@extends('layouts.app-header')

@section('content')
<div class="container">
    <div class="row">
        <!--  new field form -->
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Configure</div>
                <div class="panel-heading">Table Name :   <label>{{$tableData['table_name']}}</label></div>
                <div class="panel-body">
                    <form class="">
                        <div class="row" id="column_"`+i+`>
                            <div class="form-group col-xs-3">
                                Name
                            </div>
                            <div class="form-group col-xs-3">
                                Type
                            </div>
                            <div class="form-group col-xs-1">
                                Sequence
                            </div>
                            <div class="form-group col-xs-3">
                                Default value
                            </div>
                            <div class="form-group col-xs-2">
                                Unique
                            </div>
                        </div>
                        <div id="tableStructure">
                            <span style="display: none" id="tableId">{{$tableData['id']}}</span>
                            @php ($i = 1)
                            @foreach($structure as $key => $value)
                            <div class="row" id="column_"`+i+`>
                                <div class="form-group col-xs-3">
                                    <label>{{$value['column_name']}}</label>
                                    @if($value['is_unique'])
                                    <span>(Unique)</span>
                                    @endif
                                </div>
                                <div class="form-group col-xs-3">
                                    <label>{{$value['column_type']['column_name']}}</label>
                                </div>
                                <div class="form-group col-xs-1">
                                    <label>{{ $i }}</label>
                                </div>
                                <div class="form-group col-xs-2">
                                </div>
                                <div class="form-group col-xs-3">
                                    <label>
                                        <?php $options = json_decode($value['default_value'], true); ?>
                                        {{ implode(",",$options['options'])}}
                                    </label>
                                </div>
                            </div>
                            @php ($i++)
                            @endforeach
                        </div>
                        <div id="tableFieldRow">
                        </div>

                    </form>

                    <div class="form-group">
                        <button class="btn btn-md btn-success" onclick="addMoreRow()"><i class="glyphicon glyphicon-plus"></i> Add New Field</button>
                        <button class="btn btn-md btn-success" onclick="createTable()"><i class="glyphicon glyphicon-book"></i> Update</button> 
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Socket API</label>
                            <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="socketApi" value="{{$tableData['socket_api']}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Auth Key</label>
                            <input type="text" class="form-control name" value="{{$tableData['auth']}}" disabled="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!--<script src="js/functions.js"></script>-->
<script src="{{ asset('js/functions.js') }}"></script>

<script type="text/javascript">
                                var API_BASE_URL = '{{env('API_BASE_URL')}}';
</script>
<script type="text/javascript">
    var tableData1 = [];

    function createTable() {
        $("#tableFieldRow .row").each(function (idx) {
            var name = $('.name', $(this)).val();
            var type = $('.type', $(this)).val();
            var unique = $('.unique', $(this)).prop("checked");
            var value = $('.value', $(this)).val();

            tableData1[idx] = {'name': name, 'type': type, 'unique': unique, 'value': value};
        });
        var tableId = $("#tableId").text();
        var socketApi = $("#socketApi").val();
        console.log(tableId, API_BASE_URL);
        $.ajax({
            url: API_BASE_URL + '/configureTable',
            type: 'POST',
            data: {tableData: tableData1, tableId: tableId, socketApi: socketApi},
            dataType: 'json',
            success: function (info) {
                alert(info.msg);
                location.reload();
            }

        });
    }

</script>
@stop