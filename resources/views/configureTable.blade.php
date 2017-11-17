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
    <?php
//    print_r($tableData);
//    die;
//    echo "<br>";
//    echo "<br>";
//    echo "<br>";
//    echo $tableData[0]['table_name'];
    $tableStructureArr = json_decode($tableData[0]['table_structure'],TRUE); 
//    print_r($tableStruarr);
//    foreach($tableStruarr as $key => $value){
//        echo $key;
//        echo "<br>";
//        print_r($value);
//        echo "<br>";
//        echo "<br>";
//    }
//    die;
    ?>

    <div class="container">
        <div class="row">

            <!--  new field form -->
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Configure 
                    </div>
                    <div class="panel-heading">Table Name :   <label>{{$tableData[0]['table_name']}}</label></div>
                    <div class="panel-body">

                        <form class="">
                            <div class="row" id="column_"`+i+`>
            <div class="form-group col-xs-3">
                Name
            </div>
            <div class="form-group col-xs-3">
                Type
            </div>
<!--            <div class="form-group col-xs-3">
                Unique
            </div>-->
            <div class="form-group col-xs-3">
               Default value
            </div>
        </div>
                            <div id="tableStructure">
                                <span style="display: none" id="tableId">{{$tableData[0]['id']}}</span>
                                @foreach($tableStructureArr as $key => $value)
                                <div class="row" id="column_"`+i+`>
                                    <div class="form-group col-xs-3">
                                        <label>{{$key}}</label>
                                        @if($value['unique'] == 'true')
                                            <span>(Unique)</span>
                                        @endif
                                    </div>
                                    <div class="form-group col-xs-3">
                                        <label>{{$value['type']}}</label>
                                    </div>
<!--                                    <div class="form-group col-xs-3">
                                        <label></label>
                                    </div>-->
                                    <div class="form-group col-xs-3">
                                       <label>{{$value['value']}}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div id="tableFieldRow">
                            </div>

                            <!-- <div class="form-group">
                                <button class="btn btn-md btn-success">Save</button>
                                <button class="btn btn-md btn-danger">Cancel</button>
                            </div> -->
                        </form>
                        
                        <div class="form-group">
                            <button class="btn btn-md btn-success" onclick="addMoreRow()"><i class="glyphicon glyphicon-plus"></i> Add New Field</button>
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

    <!--<script src="js/functions.js"></script>-->
    <script src="{{ asset('js/functions.js') }}"></script>
</body>

</html>
<script type="text/javascript">
    var tableData1= [];

    function createTable(){
       $("#tableFieldRow .row").each(function(idx) {           
           var name = $('.name', $(this)).val();
           var type = $('.type', $(this)).val();
           var unique = $('.unique', $(this)).prop("checked");
           var value = $('.value', $(this)).val();
           console.log(name,type,unique,value);
           
           tableData1[idx].name = name;
           tableData1[idx].type = type;           
           tableData1[idx].unique = unique;           
           tableData1[idx].value = value;           
       });
       var tableId = $("#tableId").text();
       console.log(tableId);
       console.log(tableData1,tableId );
       $.ajax({
                    url: '/configureTable',
                    type: 'POST',
                    data: {tableData:tableData1,tableId:tableId},
                    dataType: 'json',
                    success: function(info){
                        alert(info.msg);
                            location.reload();
                    }

                });
    }
    
</script>