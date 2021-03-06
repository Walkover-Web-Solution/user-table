<?php use Carbon\Carbon; ?>
<style>
     table th, table td{
        overflow:hidden;
        /* max-width:320px; */
        text-overflow: ellipsis;
        /* width: 100%; */
    }
    .dropdowncolumn { position: absolute; top: 20px;}
   #userThead .dropdown-menu { position:realtive; top:16px;} /*top:30px;*/
    .dropdowncolumn span.caret { display: inline-block;}
    .dropdowncolumn .dropdown-menu{top:16px !important;}
    .default_value_div {display: none;}
    .fix-header .open>.dropdown-menu{
        margin-top:25px;
    }
</style>
<div class="" id="table_data" style="padding-bottom:200px;">
<table class="table-custom table-bordred table-custom-res" id="userTableData">
    @if(count($structure) < 3 && !$isGuestAccess)
        <thead id="userThead">
            <tr><th><span></span></th><th><span><button class="btn btn-primary addcolumn fixedBtn">Add some column first</button></span></th></tr>
        </thead>
    @elseif(!empty($structure) && !$isGuestAccess && empty($allTabs))
        <thead id="userThead">
            <tr>
                <th><div class="dropdowncolumn"><span class="dropdown-toggle"></span></div></th>
                <th hidden><div class="dropdowncolumn"><span class="dropdown-toggle"></span></div></th>
                @foreach($structure as $key => $val)
                @if (!empty($filtercolumns) && !in_array($key, $filtercolumns))
                    @continue
                @endif
                @if($val['display'] != 0)
                
                @php
                    $sortOrder = "ASC";
                    $sortIcon = "";
                @endphp
                
                @if(isset($sortArray['sort-key']) && $sortArray['sort-key']==$key ) 
                    @if($sortArray['sort-order']=='ASC') 
                        @php
                            $sortOrder = "DESC";
                            $sortIcon = '<i class="glyphicon glyphicon-sort-by-attributes"></i>';
                        @endphp
                    @else
                        @php
                            $sortIcon = '<i class="glyphicon glyphicon-sort-by-attributes-alt"></i>';
                        @endphp
                    @endif 
                @endif
                <th class="sort-table" data-sort-key="{{ $key }}" data-sort-order="{{ $sortOrder }}" data-target-id="{{ $tableId }}">
                    <div class="dropdowncolumn">
                        <span class="dropdown-toggle" data-toggle="dropdown"><span class="gluphicon glyphicon-email"></span>{{ $sortIcon }} {{ $key}}
                        </span>
                    <ul class="dropdown-menu">
                        <li><a href="Javascript:;" class="hidecolumn">Hide</a></li>
                        <li><a href="Javascript:;" onClick="editcolumn('{{$key}}');">Edit</a></li>
                        <li><a href="Javascript:;" class="addcolumnleft">Add to left</a></li>
                        <li><a href="Javascript:;" class="addcolumnright">Add to right</a></li>
                    </ul>
                    </div>
                </th>
                @endif
                @endforeach
            </tr>
        </thead>
    @endif
    @foreach($allTabs as $key=>$val)
    @if($key==0)
    <thead id="userThead">
    <tr>
        <!-- <th><span class="fixed-header"></span></th> -->
         @if(!$isGuestAccess)
         <th>
             <div class="dropdowncolumn"><span class="dropdown-toggle"><input type="checkbox" id="selectall" /></span></div></th>
        @endif
        @foreach($val as $k => $colName)
            @if($k == 'is_deleted')
                @continue;
            @endif
            @if (!empty($filtercolumns) && !in_array($k, $filtercolumns))
                @continue
            @endif
        @if($k!='id')
        @if(!$isGuestAccess)
        
            

        @php
            $sortOrder = "ASC";
            $sortIcon = "";
        @endphp
        
        @if(isset($sortArray['sort-key']) && $sortArray['sort-key']==$k ) 
            @if($sortArray['sort-order']=='ASC') 
                @php
                    $sortOrder = "DESC";
                    $sortIcon = '<i class="glyphicon glyphicon-sort-by-attributes"></i>';
                @endphp
            @else
                @php
                    $sortIcon = '<i class="glyphicon glyphicon-sort-by-attributes-alt"></i>';
                @endphp
            @endif 
        @endif
        <th style="position:relative;"  class="sort-table" data-sort-key="{{ $k }}" data-sort-order="{{ $sortOrder }}" data-target-id="{{ $tableId }}">
            <!--<th style="position:relative;"  class="sort-table" data-sort-key="{{ $k }}" data-sort-order="@if(isset($sortArray[$k]) && $sortArray[$k]=='ASC') {{ 'ASC' }} @else {{ 'DESC' }} @endif" data-target-id="{{ $tableId }}">-->
            <div class="dropdowncolumn">
                <span class="dropdown-toggle" data-toggle="dropdown">{!! $sortIcon !!} {{$k}}
                    {{-- <span class="caret"></span> --}}
                </span>
            {{-- <ul class="dropdown-menu">
                <li><a href="Javascript:;" class="hidecolumn">Hide</a></li>
                <li><a href="Javascript:;" onClick="editcolumn('{{$k}}');">Edit</a></li>
                <li><a href="Javascript:;" class="addcolumnleft">Add to left</a></li>
                <li><a href="Javascript:;" class="addcolumnright">Add to right</a></li>
            </ul> --}}
            </div>
        </th>
        @else
        <th>
        <div class="dropdowncolumn"><span class="dropdown-toggle" data-toggle="dropdown">{{$k}}</span></div>
        </th>
        @endif
        @else
        <th hidden><div class="dropdowncolumn"><span>{{$k}}</span></div></th>
        @endif
        @endforeach
    </tr>
    </thead>
    <tbody id="all_users">    
         @if(!$isGuestAccess)
            <tr id="tr_{{$val['id']}}" onclick="getUserDetails(event,'{{$val['id']}}','{{$tableId}}')" data-toggle="modal" data-target="#edit_user"  class="test">
                <td class="delete-row">
                    <input value="{{$val['id']}}" class="row-delete" type="checkbox" onclick="event.stopPropagation();enableDelete();"/>
                </td>
        @else
            <tr id="tr_{{$val['id']}}">    
        @endif
        @foreach($val as $k => $colValue)
            @if($k == 'is_deleted')
                @continue
            @endif
            @if (!empty($filtercolumns) && !in_array($k, $filtercolumns))
                @continue
            @endif
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true);?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
         @if(!$isGuestAccess)
            <select id="{{$k}}:_:{{$val['id']}}" name="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                <option value="">select</option>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        @else
            {{$colValue}}
        @endif    
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
          @if(!$isGuestAccess)
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif>
                @if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif
                </option>
                @endforeach
            </select>
         @else
            {{$colValue}}
        @endif 
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
        <?php $options = json_decode($structure[$k]['value'], true);
                $colValueArr = is_null(json_decode($colValue))?array():json_decode($colValue);
        ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
         @if(!$isGuestAccess)
            <select id="{{$k}}:_:{{$val['id']}}" class="multi_select{{$val['id']}}" size="5" multiple="multiple" tabindex="1" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'multiselect')">
                <option value="">select</option>
            @foreach($options['options'] as $info)
                <option value="{{$info}}" @if(in_array($info,$colValueArr)) selected="selected" @endif>{{$info}}</option>
                {{$info}}<br>
            @endforeach
            </select>
        @else
            {{$colValue}}
        @endif 
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '9')
        <?php if ($colValue) {
            $colValue = (int)$colValue;
            $carbonDate = Carbon::createFromTimestamp($colValue);
            $carbonDate->setTimezone('UTC');
            $date = $carbonDate->diffForHumans();
            $dateActual = $carbonDate->toDateTimeString();
        } else {
            $date = '';
            $dateActual = '';
        } ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><div class="col_value" data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</div></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}"><div class="col_value">{{$colValue}}</div></td>
        @else
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><div class="col_value">{{$colValue}}</div></td>
        @endif
        @endforeach
    </tr>

    @endif
    @if($key!=0)    
      @if(!$isGuestAccess)
        <tr id="tr_{{$val['id']}}" onclick="getUserDetails(event,'{{$val['id']}}','{{$tableId}}')" data-toggle="modal" data-target="#edit_user">
            <td class="delete-row">
                <input value="{{$val['id']}}" class="row-delete" type="checkbox"  onclick="event.stopPropagation();enableDelete();"/>
            </td>
      @else
        <tr id="tr_{{$val['id']}}">
      @endif  
        @foreach($val as $k => $colValue)
            @if($k == 'is_deleted')
                @continue
            @endif
            @if (!empty($filtercolumns) && !in_array($k, $filtercolumns))
                @continue
            @endif
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
        @if(!$isGuestAccess)
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                <option value="">select</option>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        @else
            {{$colValue}}
        @endif 
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
        @if(!$isGuestAccess)
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif>
                @if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif</option>
                @endforeach
            </select>
        @else
            {{$colValue}}
        @endif 
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
                <?php $options = json_decode($structure[$k]['value'], true);
                $colValueArr = is_null(json_decode($colValue))?array():json_decode($colValue);
                ?>
                <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">
                @if(!$isGuestAccess)
                    <select id="{{$k}}:_:{{$val['id']}}" class="multi_select{{$val['id']}}" size="5" multiple="multiple" tabindex="1" onclick="event.stopPropagation();"
                            onchange="updateData(this, 'multiselect')">
                        <option value="">select</option>
                        @foreach($options['options'] as $info)
                            <option value="{{$info}}" @if(in_array($info,$colValueArr)) selected="selected" @endif>{{$info}}</option>
                            {{$info}}<br>
                        @endforeach
                    </select>
                @else
                    {{$colValue}}
                @endif 
                </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '9')
        <?php if ($colValue) {
            $colValue = (int)$colValue;
            $carbonDate = Carbon::createFromTimestamp($colValue);
            $carbonDate->setTimezone('UTC');
            $date = $carbonDate->diffForHumans();
            $dateActual = $carbonDate->toDateTimeString();
        } else {
            $date = '';
            $dateActual = '';
        } ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><div class="col_value" data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</div></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}"><div class="col_value">{{$colValue}}</div></td>
        @else
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><div class="col_value">{{$colValue}}</div></td>
        @endif

        @endforeach
    </tr>
    @endif

    @endforeach
    </tbody>
</table>
</div>
<div>
@if((count($structure) > 1) && !$isGuestAccess) 
<button class="btn btn-primary m-t-3 addcolumn"><i class="glyphicon glyphicon-plus"></i></button>
 @endif
</div>
<input type="hidden" value="{{$tableAuth}}" id="tableAuthKey"/>
<!-- Modal -->
<div id="edit_column" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:800px">
        <!-- Modal content-->
        <div style="background:rgb(237,239,240)" class="modal-content">
            <div class="modal-header">
                <img style="width:21px;height:21px;vertical-align:middle" src="http://localhost:8080/img/docs.svg" alt="docs">
                <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" id="mod-head" class="modal-title">Edit Column</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form id="editColumnDetails">
                <div class="modal-body" style="width:800px">
                    <div class="col-xs-8" id="edit_column_body"></div>
                    <div style="width:20px" class="col-xs-2">&nbsp;</div>
                    <div style="padding-right:0px;padding-left:0px" class="col-xs-4" id="sec_edit_column_body"></div>
                    <div class="col-xs-12">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-xs-3">
                                    Name
                                </div>
                                <div class="form-group col-xs-3">
                                    Type
                                </div>
                                <div class="form-group col-xs-3">
                                    Display
                                </div>
                                <div class="form-group col-xs-2 hidden">
                                    Sequence
                                </div>
                                <div class="form-group col-xs-3 default_value_div">
                                    Drop Down values
                                </div>
                                <div class="form-group col-xs-2">
                                    Unique
                                </div>
                            </div>
                            <div id="ColumnStructure">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="overflow: hidden;width:750px">
                    <input type="hidden" id="eId"/>
                    <input type="hidden" id="tokenKey"/>
                    <div id="columnbutton">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->

<!-- Uploaded Contacts Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Import Table</h4>
        </div>
        <form id="importTableForm" name="importTableForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="importTable">Select File to import</label>
                    <input type="file" id="importTable" name="importTable" class="form-control" style="border: 0px; box-shadow: none;" />
                </div>
            </div>
            <div class="modal-footer">
                {{--  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>  --}}
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Uploaded Contacts Modal -->

<!-- Show Uploaded Contacts Modal -->
<div class="modal fade" id="manageContactsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        <table class="table table-striped">
            <tbody id="mapDataToTable" style="padding-bottom:40px;">
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveTable">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Show Uploaded Contacts Modal -->

<div id="columnsData" style="display:none;">
    <div style="text-align: center;">
        <select class="form-control">
                <option value="">Select Column</option>
            @foreach($structure as $key => $val)
                <option class="column_options_select" value="{{ $key }}">{{ $key }}</option>
            @endforeach
        </select>
    </div>
</div>
<input type="hidden" id="fileName" />

<script>
    var tableId = '{{$tableId}}';
    function sortTable(table, order) {
        var asc   = order === 'asc',
            tbody = table.find('tbody');
    
        tbody.find('tr').sort(function(a, b) {
            if (asc) {
                return $('td:first', a).text().localeCompare($('td:first', b).text());
            } else {
                return $('td:first', b).text().localeCompare($('td:first', a).text());
            }
        }).appendTo(tbody);
    }

    $(document).ready(function(){

        $(".sort-table").click(function(){
            var thisObj = $(this);
            var sortKey = thisObj.attr('data-sort-key').trim();
            var sortOrder = thisObj.attr('data-sort-order').trim();
            var targetId = thisObj.attr('data-target-id').trim();

            window.location.href="/tables/"+targetId+"?sort-key="+sortKey+"&sort-order="+sortOrder;

            if(sortOrder=="ASC")
                thisObj.attr('data-sort-order','DESC');
            else
                thisObj.attr('data-sort-order','ASC');

        });


        /*$("#userTableData").tablesorter();*/
        /*$("#userTableData th").click(function(){
            sortTable($('#userTableData'),'asc');
        });*/
        $(".initiateUpload").click(function(){
            $("#uploadModal").modal('show');
        });
        $("#importTableForm").on('submit', (function (e) {
            e.preventDefault();
            $.ajax({
                url: "/importTable",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function (data){
                    if(data.Message=="Success")
                    {
                        var fileName = data.FileName;
                        $("#fileName").val(fileName);
                        var columnsData = $("#columnsData").html();
                        $("#uploadModal").modal('hide');
                        $("#manageContactsModal").modal('show');
                        $.toast({
                            heading: 'Success',
                            text: 'File Uploaded Successfully, Please map the columns.',
                            showHideTransition: 'slide',
                            icon: 'success',
                        });
                        var dataToAppend = "";
                        for(var i=0;i<data.Data.length;i++)
                        {
                            dataToAppend+="<tr data-attr='"+i+"'>";
                            var dataArray = data.Data[i];
                            for(var j=0;j<dataArray.length;j++)
                            {
                                dataToAppend+="<td class='text-center td-"+i+'_'+j+"'>"+dataArray[j]+"</td>";
                            }
                            dataToAppend+="</tr>";
                        }
                        dataToAppend+="<tr data-attr='"+i+"'>";
                        var dataArray = data.Data[0];
                        var dataToPrepend = "";
                        for(var j=0;j<dataArray.length;j++)
                        {
                            dataToPrepend+="<td style='overflow:visible;'>";
                                dataToPrepend+='<select class="form-control col-select" data-col-sel="column-id-'+j+'"><option value="">Select Column</option>';
                                    $(".column_options_select").each(function(){
                                        dataToPrepend+='<option value="'+$(this).val()+'">'+$(this).val()+'</option>';
                                    });
                                dataToPrepend+='</select>';
                            dataToPrepend+="</td>";   
                        }
                        dataToAppend+="</tr>";
                        $("#mapDataToTable").html(dataToAppend);
                        $("#mapDataToTable").prepend('<tr>'+dataToPrepend+'</tr>');
                    }
                    else
                    {
                        $.toast({
                            heading: 'Oops!',
                            text: 'Something went wrong please try after sometime.',
                            showHideTransition: 'slide',
                            icon: 'error',
                        });
                    }
                },
                error:function(data){

                }
            });
        }));
        $("#saveTable").click(function(){
            var mapArray= [];
            var i=0;
            var status = 0;
            var duplicateStatus = 0;
            $(".col-select").each(function(){
                var attrVal = $(this).attr('data-col-sel');
                if($.inArray($(this).val(), mapArray))
                    duplicateStatus++;
                
                mapArray[i] = $(this).val();
                if($(this).val()!="")
                    status++;
                i++;
            });
            
            if(status==0)
            {
                $.toast({
                    heading: 'Error!',
                    text: 'Please map atleast one column to continue the import',
                    showHideTransition: 'slide',
                    icon: 'error',
                });
            }
            else if(duplicateStatus>0)
            {
                $.toast({
                    heading: 'Error!',
                    text: 'You are trying to map multiple columns of sheet to one column of table.',
                    showHideTransition: 'slide',
                    icon: 'error',
                });
            }
            else
            {
                $.toast({
                    heading: 'Operation Started',
                    text: 'Import operation started please donot close or reload this page',
                    showHideTransition: 'slide',
                    icon: 'info',
                });
                $.ajax({
                    url:"/mapDataToTable",
                    method:"POST",
                    data:{'mappingValue':mapArray , 'fileName':$("#fileName").val() , 'tableAuthKey':$("#tableAuthKey").val()},
                    success:function(respData){
                        if(respData.Message=="Success")
                        {
                            $.toast({
                                heading: 'Success',
                                text: 'Data import successful, realoding page.',
                                showHideTransition: 'slide',
                                icon: 'success',
                                afterHidden: function() {
                                    location.reload();
                                }
                            });
                        }
                    }
                });
            }
        });
        $("#selectall").change(function(){  //"select all" change 
            $(".row-delete").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
            enableDelete();
        });
        $('.addcolumnleft').click(function () {
            $('.default_value_div').css("display", "none");
            var parent = $(this).parent().parent().parent().parent();
            var index = parent.index()-1;
            var columnname = parent.find("div.dropdowncolumn span").text();
            var lists = '<option value="">Select Field Type</option>';
            for (i = 0; i <= optionList.length - 1; i++) {
                lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
            }
            var html = '<input type="hidden" id="column_add_position" value="left"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-3"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-3"><select class="m-t-0 form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-3"><select class="m-t-0 form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-3 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down values" class="value form-control"></textarea></div><div class="form-group col-xs-2"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
            $('#mod-head').html('Add Column');
            $('#ColumnStructure').html(html);
            $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="addColumn()">Add</button>');
            $('#edit_column').modal('show');
        });
        $('.addcolumnright').click(function () {
            $('.default_value_div').css("display", "none");
            var parent = $(this).parent().parent().parent().parent();
            var index = parent.index();
            console.log(index);
            var columnname = parent.find("div.dropdowncolumn span").text();
            var lists = '<option value="">Select Field Type</option>';
            for (i = 0; i <= optionList.length - 1; i++) {
                lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
            }
            var html = '<input type="hidden" id="column_add_position" value="right"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-3"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-3"><select class="m-t-0 form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-3"><select class="m-t-0 form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-3 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down value" class="value form-control"></textarea></div><div class="form-group col-xs-3"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
            $('#mod-head').html('Add Column');
            $('#ColumnStructure').html(html);
           
            $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="addColumn()">Add</button>');
            $('#edit_column').modal('show');
        });
        $('.addcolumn').click(function () {
            $('.default_value_div').css("display", "none");
            var index = 1;
            var lists = '<option value="">Select Field Type</option>';
            for (i = 0; i <= optionList.length - 1; i++) {
                lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
            }
            var html = '<input type="hidden" id="column_add_position" value="right"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-3"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-3"><select class="m-t-0 form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-3"><select class="m-t-0 form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-3 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down value" class="value form-control"></textarea></div><div class="form-group col-xs-3"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
            $('#mod-head').html('Add Column');
            $('#ColumnStructure').html(html);
            $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="addColumn()">Add</button>');
            $('#edit_column').modal('show');
        });
        // $('.dropdowncolumn').hover(
        //     function() {
        //         //console.log('hover over');
        //         $(this).find('span.caret').css({'display' : 'inline-block'});
                
        //         //$(this).children('.dropdown-menu').show();
        //     },
        //     function() {
        //         //console.log('hover out');
        //         $(this).find('span.caret').css({'display' : 'none'});
        //         //$(this).children('.dropdown-menu').hide();
        // });
        $('.hidecolumn').click(function () {
            var parent = $(this).parent().parent().parent().parent();
            var index = parent.index();
            var columnname = parent.find("div.dropdowncolumn span").text();
            $.ajax({
                url: API_BASE_URL + '/hidetablecolumn',
                type: 'POST',
                data: {id: {{$tableId}}, columnname: columnname},
                dataType: 'json',
                success: function (info) {
                    if(info.error)
                    {
                        alert(info.error);
                        return false;
                    }
                    alert(info.success);
                    location.reload();
                }
            });
        });
    });
    var table_incr_id = '<?php echo $tableId;?>';
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
    var totalCount = '{{$allCount}}';
    var table_old_data = [];
    $(document).ready(function(){
        $(".delete-rows-btn").hide();
        $
        $.ajax({
            url: API_BASE_URL + '/tables/structure/'+table_incr_id,
            type: 'GET',
            dataType: 'json',
            success: function (info) {
                table_old_data = info.structure;
            }
        });
    });
    function show_default_value_div(value)
    {
        if(value == 6)
            $('.default_value_div').css("display", "block");
        else
            $('.default_value_div').css("display", "none");
    }
    function showHiddenColumnInfo(){
        var li_value = false;
        var li = '';
        for (i = 0; i < table_old_data.length; i++) {
            if(table_old_data[i].display == 0)
            {
                li += '<li><a onclick="updatehiddencolumn('+table_old_data[i].id+')">'+table_old_data[i].column_name+'</a></li>';
                li_value = true;
            }
        }
        if(li_value)
            $('#showHiddenColumnInfo').html(li);
        else
            $('#showHiddenColumnInfo').html('<li><a href="Javascript:;">There is no hidden column</a></li>');
    }
    function addColumn()
    {   
        var tableolddata_1 = [];
        for (i = 0; i < table_old_data.length; i++) {
            if(table_old_data[i].ordering >= $('#add_column_fieldOrder').val())
            {
                table_old_data[i].ordering = table_old_data[i].ordering+1;
            }   
            var tableolddata1 = {};
            tableolddata1['name'] = table_old_data[i].column_name;
            tableolddata1['ordering'] = table_old_data[i].ordering;
            tableolddata1['unique'] = table_old_data[i].is_unique;
            tableolddata1['display'] = table_old_data[i].display;
            tableolddata1['value'] = JSON.parse(table_old_data[i].default_value).options.toString();
            tableolddata1['type'] = table_old_data[i].column_type_id;
            tableolddata_1.push(tableolddata1);
        }
        if(!$.trim($('#add_column_name').val()).length) {
            //alert('Column name must be required');
            $.toast({
                text: 'Column name must be required.',
                showHideTransition: 'slide',
                icon: 'error'
            });
            $('#add_column_name').addClass('has-error');
            return false;
        }else if(!$.trim($('#add_column_type').val()).length) {
            //alert('Column type must be required');
            $.toast({
                text: 'Column type must be required.',
                showHideTransition: 'slide',
                icon: 'error'
            });
            $('#add_column_type').addClass('has-error');
            return false;
        }else{
            var updateData={};
            updateData['name']=$('#add_column_name').val();
            updateData['type']=$('#add_column_type').val();
            updateData['display']=$('#add_column_display').val();
            updateData['ordering']=$('#add_column_fieldOrder').val();
            updateData['unique']=$('#add_column_uniqe:checked').val()?true:false;
            updateData['value']=$('#add_column_default_value').val();
            var newArr = [];
            newArr.push(updateData);
            $.ajax({
                url: API_BASE_URL + '/configureTable',
                type: 'POST',
                data: {tableData:newArr,tableOldData:tableolddata_1,tableId: table_incr_id, columnId: $('#edit_column_id').val()},
                dataType: 'json',
                success: function (info) {
                    if(info.error)
                    {
                        //alert(info.error);
                        $.toast({
                            text: info.error,
                            showHideTransition: 'slide',
                            icon: 'error'
                        });
                        return false;
                    }
                    //alert(info.msg);
                    //location.reload();
                    $.toast({
                        heading: 'Success',
                        text: info.msg,
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
    function updatehiddencolumn(id)
    {
        $.ajax({
            url: API_BASE_URL + '/showcolumntable',
            type: 'POST',
            data: {columnId:id},
            dataType: 'json',
            success: function (info) {
                if(info.error)
                {
                    alert(info.error);
                    return false;
                }
                alert(info.success);
                location.reload();
            }
        });
    }
    function editcolumn(ColumnName)
    {
        $.ajax({
            url: API_BASE_URL + '/gettablecolumndetails',
            type: 'GET',
            data: {tableid: table_incr_id, columnname: ColumnName},
            dataType: 'json',
            success: function (info) {
                if(info.error)
                {
                    alert(info.error);
                    return false;
                }
                var lists = '<option value="">Select Field Type</option>';
                for (i = 0; i <= optionList.length - 1; i++) {
                    var selected = (info.column_type.id == optionList[i].id) ? 'selected' : '';
                    lists += '<option value="' + optionList[i].id + '" '+selected+'>' + optionList[i].column_name + '</option>'
                }
                var textarea = '';
                var default_value = jQuery.parseJSON(info.default_value);
                for(var i = 0; i < default_value.options.length; i++)
                {
                    if((default_value.options.length-1) == i)
                        textarea += default_value.options[i];
                    else
                        textarea += default_value.options[i]+', ';
                }
                var html = '<div class="row"><input type="hidden" id="edit_column_id" name ="edit_column_id" value="'+info.id+'"/><div class="form-group col-xs-2"><input type="hidden" name="old_edit_column_name" id="old_edit_column_name" value="'+info.column_name+'" /><input type="text" id="edit_column_name" class="form-control" name ="edit_column_name" value="'+info.column_name+'"/></div><div class="form-group col-xs-2"><select class="form-control type m-t-0" name="edit_column_type" id="edit_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-2"><select class="form-control display m-t-0" name="edit_column_display" id="edit_column_display"><option value="1" '+(info.display ==  1 ? 'selected' : '')+'>Show</option><option value="0" '+(info.display ==  0 ? 'selected' : '')+'>Hide</option></select></div><div class="form-group col-xs-2 hidden"><input type="hidden" class="form-control order order-input" name="edit_column_fieldOrder" id="edit_column_fieldOrder" value="'+info.ordering+'"></div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="edit_column_default_value" id="edit_column_default_value" placeholder="Drop down values" class="value form-control">'+textarea+'</textarea></div><div class="form-group col-xs-2"><label><input type="checkbox" name="edit_column_uniqe" id="edit_column_uniqe" class="unique" '+(info.is_unique == 1 ? 'checked' : '')+'> Uniqe</label></div></div>';
                $('#mod-head').html('Edit Column');
                $('#ColumnStructure').html(html);
                $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="editColumnData()">Update</button>');
                show_default_value_div(info.column_type.id);
                $('#edit_column').modal('show');
            }
        });
    }
    function editColumnData()
    {
        if(!$.trim($('#edit_column_name').val()).length) {
            alert('Column name must be required');
            return false;
        }else if(!$.trim($('#edit_column_type').val()).length) {
            alert('Column type must be required');
            return false;
        }else{
            var updateData={};
            updateData['name_edit']= $('#edit_column_name').val() != $('#old_edit_column_name').val() ?true:false;
            updateData['id'] =$('#edit_column_id').val();
            updateData['old_name']=$('#old_edit_column_name').val();
            updateData['name']=$('#edit_column_name').val();
            updateData['type']=$('#edit_column_type').val();
            updateData['display']=$('#edit_column_display').val();
            updateData['ordering']=$('#edit_column_fieldOrder').val();
            //updateData['unique']=$('#edit_column_uniqe:checked').val()?true:false;


            /*if($('#edit_column_uniqe:checked').val())
                updateData['unique']=true;
            else
                updateData['unique']=false;*/
                
            updateData['unique']=$('#edit_column_uniqe').val();

            updateData['value']=$('#edit_column_default_value').val();
            var newArr = [];
            newArr.push(updateData);

            $.ajax({
                url: API_BASE_URL + '/configureTable',
                type: 'POST',
                data: {tableId: table_incr_id, columnId: $('#edit_column_id').val(),tableOldData:newArr},
                dataType: 'json',
                success: function (info) {
                    if(info.error)
                    {
                        alert(info.error);
                        return false;
                    }
                    //alert(info.msg);
                    //location.reload();
                    $.toast({
                        heading: 'Success',
                        text: 'Column Updated',
                        showHideTransition: 'slide',
                        icon: 'success'
                    });
                    setTimeout(function(){
                        location.reload();
                    },1000);
                }
            });
        }
    }
    function enableDelete(){
        var elements = $(".row-delete:checked");
        if(elements.length > 0)
            $(".delete-rows-btn").show();
        else
            $(".delete-rows-btn").hide(); 
        
    }
    function DeleteRecords(){
          var elements = $(".row-delete:checked");
          var deletedRecords = [];
          var counter = 0;
          $(elements).each((index,item) => {
              deletedRecords.push(item.value);
              counter++;
          });

          swal({
            title: 'Are you sure to delete '+counter+' records?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
            if (result.value) {
                    var url = API_BASE_URL + "/deleterecords/{{$tableId}}";
                    $.post(url,{"ids":deletedRecords},(response) => {
                        //console.log(response);
                        $(elements).each((index,item) => {
                            $(item).parent().parent().hide(); 
                        });
                        swal(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        );
                        setTimeout(function(){
                            window.location.reload();
                        },500);
                    });
            }
          });
    }
    function updateData(ths, method) {
        var authKey = $("#tableAuthKey").val();
        var obj;
        var jsonDoc = {};
        jsonDoc['edit_url_callback'] = true;
        jsonDoc['data_source'] = 'manual';
        if (method == 'radio_button') {
            var key_name = $(ths).attr('name');
            key_name = key_name.split(":_:");
            coloumn_name = key_name[0];
            row_id = key_name[1];
            new_value = $(ths).attr('value');
        }
        else if (method == 'dropdown') {
            var key_name = $(ths).attr('id');
            key_name = key_name.split(":_:");
            coloumn_name = key_name[0];
            row_id = key_name[1];
            new_value = $(ths).find(":selected").val();
        } else if (method == 'teammates') {
            var key_name = $(ths).attr('id');
            key_name = key_name.split(":_:");
            coloumn_name = key_name[0];
            row_id = key_name[1];
            new_value = $(ths).find(":selected").val();
        }
        else if (method == 'multiselect') {
            var key_name = $(ths).attr('id');
            key_name = key_name.split(":_:");
            coloumn_name = key_name[0];
            row_id = key_name[1];
            new_value = [];
            $.each($(".multi_select"+row_id+" option:selected"), function(){
                new_value.push($(this).val());
            });
        }
        jsonDoc[coloumn_name] = new_value;
        jsonDoc['id'] = row_id;
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        jsonDoc['_token'] = CSRF_TOKEN;
        obj = jsonDoc;
        $.ajax({
            type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
            dataType: 'json', // Set datatype - affects Accept header
            url: API_BASE_URL + "/add_update", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            data: jsonDoc, // Some data e.g. Valid JSON as a string

            beforeSend: function (xhr) {
                xhr.setRequestHeader('Auth-Key', authKey);
            },
            success: function (data) {
                // ALL_USERS[selectedRow] = data.data;
                //console.log(data)
//                location.reload();
            },
        });
    }
    

//     $(document).ready(function(){
//     $('.dropdowncolumn').hover(function() {
//   $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
// }, function() {
//   $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
// });
// }); 
</script>
