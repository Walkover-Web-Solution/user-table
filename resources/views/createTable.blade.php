<!DOCTYPE html>
<html lang="en">

<head>
    <title>userTABLE Mockup</title>
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
                    <div class="panel-heading">Create New Table IN 
                    <?php 
                    $teamArr = Session::get('teams');
                    ?>
                    {{ Form::select('teamName', [$teamArr]) }}
                    </div>
                    <div class="panel-heading">Enter Table Name : <input type="text" id="tableName" name="tableName"></div>
                    <div class="panel-body">

                        <form class="">
                            <div class="row" id="column_"`+i+`>
            <div class="form-group col-xs-3">
                Name
            </div>
            <div class="form-group col-xs-3">
                Type
            </div>
            <div class="form-group col-xs-3">
                Unique
            </div>
            <div class="form-group col-xs-3">
               Default value
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
                            <button class="btn btn-md btn-success" onclick="createTable()"><i class="glyphicon glyphicon-plus"></i> Create</button>
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
</body>

</html>
<script type="text/javascript">
    var tableData= [];

    function createTable(){
       $("#tableField .row").each(function(idx) {           
           var name = $('.name', $(this)).val();
           var type = $('.type', $(this)).val();
           var unique = $('.unique', $(this)).prop("checked");
           var value = $('.value', $(this)).val();
           console.log(name,type,unique,value);
           
           tableData[idx].name = name;
           tableData[idx].type = type;           
           tableData[idx].unique = unique;           
           tableData[idx].value = value;           
       });
       var tableName = $("#tableName").val();
       var teamId = $('select[name=teamName]').val();

       console.log(tableData);
       $.ajax({
                    url: 'createTable',
                    type: 'POST',
                    data: {tableData:tableData,tableName:tableName,teamId:teamId},
                    dataType: 'json',
                    success: function(info){
                        alert(info.msg);
                        window.location.href = "tables";                    }

                });
    }
</script>