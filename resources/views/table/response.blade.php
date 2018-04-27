<?php use Carbon\Carbon; ?>
<style>
    table th, table td{
        overflow:hidden;
        /* max-width:320px; */
        text-overflow: ellipsis;
        /* width: 100%; */
    }
    .dropdowncolumn { position: absolute; top: 0px;}
    .dropdown-menu { position:relative; top:30px;}
    .dropdowncolumn span.caret { display: none;}
    .dropdowncolumn .dropdown-menu{top:37px !important;}
    .default_value_div {display: none;}
</style>
<table class="table-custom table-bordred table-custom-res">
    @if(empty($structure) && !$isGuestAccess)
        <thead id="userThead">
            <tr><th><span class=""></span></th><th><span class=""><button class="btn btn-primary addcolumn">Add Column</button></span></th></tr>
        </thead>
    @elseif(!empty($structure) && !$isGuestAccess && empty($allTabs))
        <thead id="userThead">
            <tr>
                <th><span class=""></span></th>
                <th hidden><span class=""></span></th>
                @foreach($structure as $key => $val)
                @if($val['display'] != 0)
                <th>
                    <div class="dropdowncolumn">
                        <span class="dropdown-toggle" data-toggle="dropdown"><span class="gluphicon glyphicon-email"></span>{{$key}}
                            <span class="caret"></span>
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
         <th><span class=""><input type="checkbox" id="selectall" /></span></th>
        @endif
        @foreach($val as $k => $colName)
            @if($k == 'is_deleted')
                @continue;
            @endif
        @if($k!='id')
        @if(!$isGuestAccess)
            <th>
            <div class="dropdowncolumn">
                <span class=" dropdown-toggle" data-toggle="dropdown">{{$k}}
                    <span class="caret"></span>
                </span>
            <ul class="dropdown-menu">
                <li><a href="Javascript:;" class="hidecolumn">Hide</a></li>
                <li><a href="Javascript:;" onClick="editcolumn('{{$k}}');">Edit</a></li>
                <li><a href="Javascript:;" class="addcolumnleft">Add to left</a></li>
                <li><a href="Javascript:;" class="addcolumnright">Add to right</a></li>
            </ul>
            </div>
        </th>
        @else
        <th>
           <span class="dropdown-toggle" data-toggle="dropdown">{{$k}}</span>
        </th>
        @endif
        @else
        <th hidden class=""><span>{{$k}}</span></th>
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
            $carbonDate = Carbon::createFromTimestamp($colValue);
            $carbonDate->setTimezone('UTC');
            $date = $carbonDate->diffForHumans();
            $dateActual = $carbonDate->toDateTimeString();
        } else {
            $date = '';
            $dateActual = '';
        } ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><span data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</span></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}">{{$colValue}}</td>
        @else
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">{{$colValue}}</td>
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
            $carbonDate = Carbon::createFromTimestamp($colValue);
            $carbonDate->setTimezone('UTC');
            $date = $carbonDate->diffForHumans();
            $dateActual = $carbonDate->toDateTimeString();
        } else {
            $date = '';
            $dateActual = '';
        } ?>
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}"><span data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</span></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}">{{$colValue}}</td>
        @else
        <td class="{{$k}}" id="dt_{{$structure[$k]['column_type_id']}}">{{$colValue}}</td>
        @endif

        @endforeach
    </tr>
    @endif

    @endforeach
    </tbody>
</table>
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
                    <div style="width:20px" class="col-xs-1">&nbsp;</div>
                    <div style="padding-right:0px;padding-left:0px" class="col-xs-4" id="sec_edit_column_body"></div>
                    <div class="col-xs-12">
                        <div class="panel-body">
                            <div class="row">
                                <div class="form-group col-xs-2">
                                    Name
                                </div>
                                <div class="form-group col-xs-2">
                                    Type
                                </div>
                                <div class="form-group col-xs-2">
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
<script>
    $(document).ready(function(){
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
            var html = '<input type="hidden" id="column_add_position" value="left"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-2"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-2"><select class="form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-2"><select class="form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-2 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down values" class="value form-control"></textarea></div><div class="form-group col-xs-1"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
            $('#mod-head').html('Add Column');
            $('#ColumnStructure').html(html);
            $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="addColumn()">Add</button>');
            $('#edit_column').modal('show');
        });
        $('.addcolumnright').click(function () {
            $('.default_value_div').css("display", "none");
            var parent = $(this).parent().parent().parent().parent();
            var index = parent.index();
            var columnname = parent.find("div.dropdowncolumn span").text();
            var lists = '<option value="">Select Field Type</option>';
            for (i = 0; i <= optionList.length - 1; i++) {
                lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
            }
            var html = '<input type="hidden" id="column_add_position" value="right"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-2"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-2"><select class="form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-2"><select class="form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-2 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down value" class="value form-control"></textarea></div><div class="form-group col-xs-1"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
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
            var html = '<input type="hidden" id="column_add_position" value="right"/><input type="hidden" id="add_column_fieldOrder" name="add_column_fieldOrder" value="'+index+'"/><div class="row"><div class="form-group col-xs-2"><input type="text" placeholder="Column Name" class="form-control" name="add_column_name" id="add_column_name"/></div><div class="form-group col-xs-2"><select class="form-control type" name="add_column_type" id="add_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-2"><select class="form-control display" name="add_column_display" id="add_column_display"><option value="1">Show</option><option value="0">Hide</option></select></div><div class="form-group col-xs-2 hidden">'+index+'</div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="add_column_default_value" id="add_column_default_value" placeholder="Drop down value" class="value form-control"></textarea></div><div class="form-group col-xs-1"><label><input type="checkbox" name="add_column_uniqe" id="add_column_uniqe" class="unique"> Uniqe</label></div></div>';
            $('#mod-head').html('Add Column');
            $('#ColumnStructure').html(html);
            $('#columnbutton').html('<button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="addColumn()">Add</button>');
            $('#edit_column').modal('show');
        });
        $('.dropdowncolumn').hover(
            function() {
                //console.log('hover over');
                $(this).find('span.caret').css({'display' : 'inline-block'});
                $(this).children('.dropdown-menu').show();
            },
            function() {
                //console.log('hover out');
                $(this).find('span.caret').css({'display' : 'none'});
                $(this).children('.dropdown-menu').hide();
        });
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
            alert('Column name must be required');
            return false;
        }else if(!$.trim($('#add_column_type').val()).length) {
            alert('Column type must be required');
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
                        alert(info.error);
                        return false;
                    }
                    alert(info.msg);
                    location.reload();
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
                var html = '<div class="row"><input type="hidden" id="edit_column_id" name ="edit_column_id" value="'+info.id+'"/><div class="form-group col-xs-2"><input type="hidden" name="old_edit_column_name" id="old_edit_column_name" value="'+info.column_name+'" /><input type="text" id="edit_column_name" class="form-control" name ="edit_column_name" value="'+info.column_name+'"/></div><div class="form-group col-xs-2"><select class="form-control type" name="edit_column_type" id="edit_column_type" onchange="show_default_value_div(this.value);">'+ lists +'</select></div><div class="form-group col-xs-2"><select class="form-control display" name="edit_column_display" id="edit_column_display"><option value="1" '+(info.display ==  1 ? 'selected' : '')+'>Show</option><option value="0" '+(info.display ==  0 ? 'selected' : '')+'>Hide</option></select></div><div class="form-group col-xs-2 hidden"><input type="hidden" class="form-control order order-input" name="edit_column_fieldOrder" id="edit_column_fieldOrder" value="'+info.ordering+'"></div><div class="form-group col-xs-3 default_value_div"><textarea type="text" name="edit_column_default_value" id="edit_column_default_value" placeholder="Drop down values" class="value form-control">'+textarea+'</textarea></div><div class="form-group col-xs-1"><label><input type="checkbox" name="edit_column_uniqe" id="edit_column_uniqe" class="unique" '+(info.is_unique == 1 ? 'checked' : '')+'> Uniqe</label></div></div>';
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
            updateData['unique']=$('#edit_column_uniqe:checked').val()?true:false;
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
                    alert(info.msg);
                    location.reload();
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
          $(elements).each((index,item) => {
              deletedRecords.push(item.value);
          });
          var url = API_BASE_URL + "/deleterecords/{{$tableId}}";
          $.post(url,{"ids":deletedRecords},(response) => {
              //console.log(response);
             $(elements).each((index,item) => {
            $(item).parent().parent().hide(); 
          });
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
    

    $(document).ready(function(){
    $('.dropdowncolumn').hover(function() {
  $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
}, function() {
  $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
});
}); 
</script>
