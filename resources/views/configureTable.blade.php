<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Configure Table </title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap core CSS -->

        <link href="{{ asset('css/reset.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    </head>

    <body>
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
                                            @if($value['is_unique'])
                                            <span>(Unique)</span>
                                            @endif
                                        </div>
                                        <div class="form-group col-xs-2">
                                            <select class="form-control type">
                                                <option value="">Select Field Type</option>
                                                <option value="1" {{ ($value['column_type']['column_name'] == 'text') ? 'selected' : '' }}>text</option>
                                                <option value="2" {{ ($value['column_type']['column_name'] == 'phone') ? 'selected' : '' }}>phone</option>
                                                <option value="3" {{ ($value['column_type']['column_name'] == 'any number') ? 'selected' : '' }}>any number</option>
                                                <option value="4" {{ ($value['column_type']['column_name'] == 'airthmatic number') ? 'selected' : '' }}>airthmatic number</option>
                                                <option value="5" {{ ($value['column_type']['column_name'] == 'email') ? 'selected' : '' }}>email</option>
                                                <option value="6" {{ ($value['column_type']['column_name'] == 'dropdown') ? 'selected' : '' }}>dropdown</option>
                                                <option value="7" {{ ($value['column_type']['column_name'] == 'radio button') ? 'selected' : '' }}>radio button</option>
                                                <option value="8" {{ ($value['column_type']['column_name'] == 'checkbox') ? 'selected' : '' }}>checkbox</option>
                                                <option value="9" {{ ($value['column_type']['column_name'] == 'date') ? 'selected' : '' }}>date</option>
                                                <option value="10" {{ ($value['column_type']['column_name'] == 'my teammates') ? 'selected' : '' }}>my teammates</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-2">
                                            <select class="form-control display">
                                                <option value="1">Show</option>
                                                <option value="0">Hide</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-1">
                                            <input type="text" value="{{ $i }}" name="fieldOrder" class="form-control order order-input">
                                        </div>
                                        <div class="form-group col-xs-3">
                                            <?php $options = json_decode($value['default_value'], true); ?>
                                            <textarea name="" placeholder="Default value" class="form-control value">{{ implode(",",$options['options'])}}</textarea>
                                        </div>
                                        <div class="form-group col-xs-2">
                                            <label><input type="radio" name="uniqe" class="unique"> Uniqe</label>
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
                            <label class="col-md-6">Socket API</label>
                            <label class="col-md-6">Auth Key</label>
                            <div class="form-group col-md-6">
                                <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="socketApi" value="{{$tableData['socket_api']}}">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" class="form-control name" value="{{$tableData['auth']}}" disabled="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!--<script src="js/functions.js"></script>-->
        <script src="{{ asset('js/functions.js') }}"></script>
    </body>

</html>
<script type="text/javascript">
                            var API_BASE_URL = '{{env('API_BASE_URL')}}';
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
            var type = $('.type', $(this)).val();
            var display = $('.display', $(this)).val();
            var order = $('.order', $(this)).val();
            var unique = $('.unique', $(this)).prop("checked");
            var value = $('.value', $(this)).val();

            tableData1[idx] = {'name': name, 'type': type, 'display': display, 'order': order, 'unique': unique, 'value': value};
        });

        $('#tableStructure .row').each(function (idy) {
            var name = $('.name', $(this)).val();
            var type = $('.type', $(this)).val();
            var display = $('.display', $(this)).val();
            var order = $('.order', $(this)).val();
            var unique = $('.unique', $(this)).prop("checked");
            var value = $('.value', $(this)).val();

            tableData2[idy] = {'name': name, 'type': type, 'display': display, 'order': order, 'unique': unique, 'value': value};
        });

        var tableId = $("#tableId").text();
        var socketApi = $("#socketApi").val();
        console.log(tableId, API_BASE_URL);
        $.ajax({
            url: API_BASE_URL + '/configureTable',
            type: 'POST',
            data: {tableData: tableData1, tableOldData: tableData2, tableId: tableId, socketApi: socketApi},
            dataType: 'json',
            success: function (info) {
                alert(info.msg);
                location.reload();
            }
        });
    }
</script>
