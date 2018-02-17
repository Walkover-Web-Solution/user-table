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
                            </div>
                        </div>
                        <div class="panel-heading">Team Name :   <label>{{Session::get('teams')[$tableData['team_id']]}}</label></div>
                        <div class="panel-heading">Table Name :   <label>{{$tableData['table_name']}}</label></div>
                        <div class="panel-body">
                            <form class="">
                                <div class="row" id="column_"`+i+`>
                                    <div class="form-group col-xs-3">
                                        Email Address
                                    </div>
                                    <div class="form-group col-xs-9">
                                       Table Columns 
                                    </div>
                                </div>

                                <div id="tableAccess">
                                 @foreach($tableExistingData as $row)
                                    <div class="row">
                                        <div class="form-group col-xs-3">
                                            <input type="hidden" value="{{$row['id']}}" name="tableId" class="tableId">
                                            <input type="text" value="{{$row['team_id']}}" class="form-control email email-input lowercase" name="emailaddress" placeholder="enter email address">
                                        </div>
                                        
                                        <div class="form-group col-xs-9">
                                            @foreach($structure as $key => $value)
                                                <?php
                                                    $struct = json_decode($row['table_structure']);
                                                    $isChecked = in_array($value['id'],$struct);
                                                ?>
                                                <div class="column-name">
                                                <input <?php if($isChecked) echo 'checked'; ?>  class="column-name-ctrl" value="{{$value['id']}}" type="checkbox" id="column_{{$value['column_name']}}"><label>{{$value['column_name']}}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                       @endforeach
                                </div>
                                <div id="tableAccessRow">
                                    <div class="row">
                                        <div class="form-group col-xs-3">
                                            <input type="text" value="" class="form-control email email-input lowercase" name="emailaddress" placeholder="enter email address">
                                        </div>
                                        <div class="form-group col-xs-9">
                                            @foreach($structure as $key => $value)
                                                <div class="column-name">
                                                <input class="column-name-ctrl" value="{{$value['id']}}" type="checkbox" id="column_{{$value['column_name']}}"><label>{{$value['column_name']}}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="form-group">
                                <button class="btn btn-md btn-success" onclick="addMoreRow()"><i class="glyphicon glyphicon-plus"></i> Add New </button>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <form>
                                        <div class="form-group col-md-12 text-center">
                                            <button class="btn btn-lg btn-success" id="tableAccess" type="button" onclick="createAccessForTable()"><i class="glyphicon glyphicon-book"></i> Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!--<script src="js/functions.js"></script>
        <script src="{{ asset('js/functions.js') }}"></script>-->

<script type="text/javascript">
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
    var tableId = '<?php echo $tableData['id'];?>';
    var rowIndex = 1;
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

    function createAccessForTable() {
        $("#tableAccess .row").each(function (idx) {
            var email = $('.email', $(this)).val();
            var tableAccessId = $('.tableId', $(this)).val();
             var columns = [];
            if(email!=='') {
                //column-name-ctrl
                //console.log($('.column-name-ctrl', $(this)));
                 
                $('.column-name-ctrl', $(this)).each((index,item)=>{
                  //  console.log(item);
                    var isChecked =  $(item).prop("checked");
                    if(isChecked)
                        columns.push( $(item).val());

                });   
                tableData1.push ({
                    'id':tableAccessId,
                    'email': email,
                    'columns':columns
                });
            }
        });

        $("#tableAccessRow .row").each(function (idx) {
            var email = $('.email', $(this)).val();
             var columns = [];
            if(email!=='') {
                //column-name-ctrl
                //console.log($('.column-name-ctrl', $(this)));
                 
                $('.column-name-ctrl', $(this)).each((index,item)=>{
                  //  console.log(item);
                    var isChecked =  $(item).prop("checked");
                    if(isChecked)
                        columns.push( $(item).val());

                });   
                tableData2.push ({
                    'id': -1,
                    'email': email,
                    'columns':columns
                });
            }
        });

        $('#updateTable').attr("disabled", true);
        $('#updateTable').text("Please Wait...");

        console.log(tableId, API_BASE_URL);
        $.ajax({
            url: API_BASE_URL + '/tableaccessmanage',
            type: 'POST',
            data: {tableExistingData: tableData1, tableNewData: tableData2, tableId: tableId},
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

function addMoreRow(check) {
    var formGrp = `<div class="row" id="column_` + rowIndex + `">
        <div class="form-group col-xs-3">
            <input type="text" value="" class="form-control email email-input lowercase" name="emailaddress" placeholder="enter email address">
        </div><div class="form-group col-xs-9">`;
        var rowStr = "";
            @foreach($structure as $key => $value)
               rowStr += `<div class="column-name"><input value="{{$value['column_name']}}" type="checkbox" id="column_{{$value['column_name']}}"><label>{{$value['column_name']}}</label></div>`;
            @endforeach
    formGrp +=  rowStr;        
    formGrp += `</div></div>`;
    formGrp += '';
    rowIndex++;
    return $('#tableAccessRow').append(formGrp);
}
</script>
@stop