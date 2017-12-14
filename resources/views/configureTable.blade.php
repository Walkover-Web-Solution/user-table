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
                                    <div class="form-group col-xs-2">
                                        Name
                                    </div>
                                    <div class="form-group col-xs-2">
                                        Type
                                    </div>
                                    <div class="form-group col-xs-2">
                                        Display
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
                                        <div class="form-group col-xs-2">
                                            <input type="hidden" value="{{$value['column_name']}}" class="name">
                                            <label>{{$value['column_name']}}</label>
                                            @if(array_key_exists($value['column_name'], $sequence))
                                                {{ ($sequence[$value['column_name']]['is_unique'] == 1) ? '(Unique)' : '' }}
                                            @endif
                                            <!-- @if($value['is_unique'])
                                            <span>(Unique)</span>
                                            @endif -->
                                        </div>
                                        <div class="form-group col-xs-2">
                                            <select class="form-control type">
                                                <option value="">Select Field Type</option>
                                                @foreach($columnList as $row)
                                                <option value="{{ $row['id'] }}" {{ ($value['column_type']['column_name'] == $row['column_name']) ? 'selected' : '' }}>{{ $row['column_name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-2">
                                            <select class="form-control display">
                                                @if(array_key_exists($value['column_name'], $sequence))
                                                <option value="1" {{ $sequence[$value['column_name']]['display'] == 1 ? 'selected' : '' }}>Show</option>
                                                <option value="0" {{ $sequence[$value['column_name']]['display'] == 0 ? 'selected' : '' }}>Hide</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-1">
                                            @if(array_key_exists($value['column_name'], $sequence))
                                            <input type="text" value="{{ $sequence[$value['column_name']]['ordering'] }}" name="fieldOrder" class="form-control order order-input">
                                            @endif
                                        </div>
                                        <div class="form-group col-xs-3">
                                            <?php $options = json_decode($value['default_value'], true); ?>
                                            <textarea name="" placeholder="Default value" class="form-control value">{{ implode(",",$options['options'])}}</textarea>
                                        </div>
                                        <div class="form-group col-xs-2">
                                            @if(array_key_exists($value['column_name'], $sequence))
                                                <label><input type="radio" name="uniqe" class="unique" {{ ($sequence[$value['column_name']]['is_unique'] == 1) ? 'checked' : '' }}> Uniqe</label>
                                            @endif
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
                            </div>
                        </div>
                        <div class="panel-body">
                            <label class="col-md-6">Webhook Update Notification</label>
                            <label class="col-md-6">Auth Key</label>
                            <div class="form-group col-md-6">
                                <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="socketApi" value="{{$tableData['socket_api']}}">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" class="form-control name" value="{{$tableData['auth']}}" disabled="">
                            </div>
                            
                            <label class="col-md-6">Webhook New Entry Notification</label>
                            <div class="form-group col-md-6">
                                <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="newEntryApi" value="{{$tableData['new_entry_api']}}">
                            </div>
                            <div class="form-group col-md-12 text-center">
                                <button class="btn btn-md btn-success" id="updateTable" onclick="createTable()"><i class="glyphicon glyphicon-book"></i> Update</button>
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
<script>
$(document).ready(function(){
    $('body').on('click', '.remove-row', function() {
        $(this).closest('.row').text('');
    });
});
</script>
<script type="text/javascript">
    var tableData1 = [];
    var tableData2 = [];

    function createTable() {

        var idx = {};
        $('.order-input').each(function(){
            var val = $(this).val();
            if(val.length)
            {
                if(idx[val])
                {
                    idx[val]++;
                }
                else
                {
                  idx[val] = 1;
                }
            }
        });
        var gt_one = $.map(idx,function(e,i){return e>1 ? e: null});
        var isUnique = gt_one.length==0
        if(isUnique == false)
        {
            alert("This order already used before");
            return false;
        }

        $("#tableFieldRow .row").each(function (idx) {
            var name = $('.name', $(this)).val();
            if(name!=='') {
                var type = $('.type', $(this)).val();
                var display = $('.display', $(this)).val();
                var order = $('.order', $(this)).val();
                var unique = $('.unique', $(this)).prop("checked");
                var value = $('.value', $(this)).val();

                tableData1[idx] = {
                    'name': name,
                    'type': type,
                    'display': display,
                    'ordering': order,
                    'unique': unique,
                    'value': value
                };
            }
        });

        $('#tableStructure .row').each(function (idy) {
            var name = $('.name', $(this)).val();
            var type = $('.type', $(this)).val();
            var display = $('.display', $(this)).val();
            var order = $('.order', $(this)).val();
            var unique = $('.unique', $(this)).prop("checked");
            var value = $('.value', $(this)).val();

            tableData2[idy] = {'name': name, 'type': type, 'display': display, 'ordering': order, 'unique': unique, 'value': value};
        });

        var tableId = $("#tableId").text();
        var socketApi = $("#socketApi").val();
        var newEntryApi = $("#newEntryApi").val();

        $('#updateTable').attr("disabled", true);
        $('#updateTable').text("Please Wait...");

        console.log(tableId, API_BASE_URL);
        $.ajax({
            url: API_BASE_URL + '/configureTable',
            type: 'POST',
            data: {tableData: tableData1, tableOldData: tableData2, tableId: tableId, socketApi: socketApi , newEntryApi : newEntryApi},
            dataType: 'json',
            success: function (info) {
                $('#updateTable').attr("disabled", false);
                $('#updateTable').html('<i class="glyphicon glyphicon-book"></i> Update');
                if(info.error)
                {
                    alert(info.msg);
                    return false;
                }
                alert(info.msg);
                location.reload();
            }
        });
    }
</script>
@stop