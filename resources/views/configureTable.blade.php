@extends('layouts.app-header')

@section('content')
        <div class="container mt20">
            <div class="row">
                <!--  new field form -->
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-2 active">
                                   <a href="{{env('APP_URL')}}/configure/{{$tableData['id']}}">Configure</a>
                                </div>
                                <div class="col-xs-2">
                                   <a href="{{env('APP_URL')}}/tableaccess/{{$tableData['id']}}">Table Access</a>
                                </div>
                                <div class="col-xs-2">
                                   <a href="{{env('APP_URL')}}/listFilters/{{$tableData['id']}}">Filters</a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-heading">Team Name :   <label>{{Session::get('teams')[$tableData['team_id']]}}</label></div>
                        
                        <div class="panel-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <form>
                                        <div class="form-group col-md-6">
                                            <label for="socketApi">Webhook Update Notification</label>
                                            <input type="text" placeholder="Enter API" class="form-control name" id="socketApi" name="socketApi" value="{{$tableData['socket_api']}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="key">Auth Key</label>
                                            <input type="text" class="form-control name" id="key" name="key" value="{{$tableData['auth']}}" disabled="">
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label for="newEntryApi">Webhook New Entry Notification</label>
                                            <input type="text" placeholder="Enter API" class="form-control" id="newEntryApi" name="newEntryApi" value="{{$tableData['new_entry_api']}}" >
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="newEntryApi">SMS API key</label>
                                            <input type="text" placeholder="Enter SMS API key" class="form-control" id="smsApiKey" name="smsApiKey" value="{{$tableData['sms_api_key']}}" >
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="newEntryApi">Email API key</label>
                                            <input type="text" placeholder="Enter Email API key" class="form-control" id="EmailApiKey" name="EmailApiKey" value="{{$tableData['email_api_key']}}" >
                                        </div>
                                        <div class="col-md-6">
                                            &nbsp;
                                        </div>
                                        <div class="form-group col-md-12 text-center">
                                            <button class="btn btn-lg btn-success" id="updateTable" onclick="createTable()"><i class="glyphicon glyphicon-book"></i> Update</button>
                                        </div>
                                    </form>
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
    var tableId = '<?php echo $tableData['id'];?>';
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

        var socketApi = $("#socketApi").val();
        var newEntryApi = $("#newEntryApi").val();
        var smsApiKey = $("#smsApiKey").val();
        var EmailApiKey = $("#EmailApiKey").val();

        $('#updateTable').attr("disabled", true);
        $('#updateTable').text("Please Wait...");

        console.log(tableId, API_BASE_URL);
        $.ajax({
            url: API_BASE_URL + '/updateTableStructure',
            type: 'POST',
            data: {tableId: tableId, socketApi: socketApi , newEntryApi : newEntryApi, smsApiKey : smsApiKey, EmailApiKey : EmailApiKey},
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