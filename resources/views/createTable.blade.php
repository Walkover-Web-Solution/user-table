@extends('layouts.app-header')

@section('content')
<div class="container">
        <div class="row">

            <!--  new field form -->
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Create New Table IN
                    <?php
                    $teamArr = Session::get('teams');
                    ?>
                    {{ Form::select('teamName', [$teamArr]) }}
                    </div>
                    <div class="panel-heading">Enter Table Name : <input type="text" class="form-control" id="tableName" name="tableName" /></div>
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
                            <div id="tableField">

                            </div>

                            <!-- <div class="form-group">
                                <button class="btn btn-md btn-success">Save</button>
                                <button class="btn btn-md btn-danger">Cancel</button>
                            </div> -->
                        </form>

                        <div class="form-group">
                            <button class="btn btn-md btn-success" onclick="addRow()"><i class="glyphicon glyphicon-plus"></i> Add New Field</button>
                            <button class="btn btn-md btn-success" id="createTable" onclick="createTable()"><i class="glyphicon glyphicon-plus"></i> Create</button>
                        </div>
                    </div>
                    <hr>
                    <div class="panel-body">
                        <label class="col-md-12">Webhook Update Notification</label>
                        <div class="form-group col-md-5">
                            <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="socketApi" value="">
                        </div>
                    </div>

                </div>
            </div>
            <!--  new field form -->

            <!--  advanced option form -->
<!--            <div class="col-md-4" id="right_panel">
                <div class="panel panel-default">
                    <div class="panel-heading title"></div>
                    <div class="panel-body">
                        <form class="">
                            <div id="additional_option">

                            </div>
                        </form>
                    </div>
                </div>
            </div>-->
            <!--  advanced option form -->


        </div>
    </div>

<script src="js/functions.js"></script>

<script type="text/javascript">
        var API_BASE_URL = '{{env('API_BASE_URL')}}';
</script>
<script type="text/javascript">
    var tableData= [];

    function createTable(){
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
            alert("Please remove repeat sequence from order.");
            return false;
        }

       $("#tableField .row").each(function(idx) {
           var name = $('.name', $(this)).val();
           var display = $('.display', $(this)).val();
           var type = $('.type', $(this)).val();
           var order = $('.order', $(this)).val();
           var unique = $('.unique', $(this)).prop("checked");
           var value = $('.value', $(this)).val();
           console.log(name,type,order,unique,value);

           tableData[idx] = {'name':name,'type':type,'display':display,'order':order,'unique':unique,'value':value};
//           tableData[idx].type = type;
//           tableData[idx].unique = unique;
//           tableData[idx].value = value;
       });
       var tableName = $("#tableName").val();
       var teamId = $('select[name=teamName]').val();
       var socketApi = $("#socketApi").val();

       $('#createTable').attr("disabled", true);
       $('#createTable').text("Please Wait...");

       console.log(tableData);
       $.ajax({
            url: 'createTable',
            type: 'POST',
            data: {tableData:tableData,tableName:tableName,teamId:teamId, socketApi:socketApi},
            dataType: 'json',
            success: function(info){
                $('#createTable').attr("disabled", false);
                $('#createTable').html('<i class="glyphicon glyphicon-plus"></i> Create');
                if(info.error)
                {
                    alert(info.msg);
                    return false;
                }
                alert(info.msg);
                window.location.href = "tables";
            }

        });
    }
</script>

@stop
