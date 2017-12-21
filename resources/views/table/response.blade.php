<?php use Carbon\Carbon;?>
<table id="myTable" class="table basic table-bordred">

    @foreach($allTabs as $key=>$val)
    @if($key==0)

    <thead id="userThead">
    <tr>
        <!-- <th><span class="fixed-header"></span></th> -->
        @foreach($val as $k => $colName)
        @if($k!='id')
        <th><span>{{$k}}</span></th>
        @else
        <th hidden><span>{{$k}}</span></th>
        @endif
        @endforeach
    </tr>
    </thead>
    <tbody id="all_users">
    <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
        <!-- <td></td> -->
        @foreach($val as $k => $colValue)
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td>
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif>{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td>
            <select id="{{$k}}:_:{{$val['id']}}" name="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td>
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif
                >@if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td>
            @foreach($options['options'] as $info)
            <input type="checkbox" onchange="updateData(this, 'checkbox')" class="{{$k}}{{$val['id']}}"
                   value="{{$info}}" datacol="{{$k}}" dataid="{{$val['id']}}" @if($info== $colValue) checked @endif>{{$info}}<br>
            @endforeach
        </td>
            @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '9')
                <?php if($colValue){
                    //$carbonDate = new Carbon($colValue);
                    $carbonDate = Carbon::createFromTimestamp($colValue);

                    $date = $carbonDate->diffForHumans();
                }else{
                    $date = '';
                } ?>
                <td>{{$date}}</td>
        @elseif($k == 'id')
        <td hidden>{{$colValue}}</td>
        @else
        <td>{{$colValue}}</td>
        @endif
        @endforeach
    </tr>
    
    @endif
    @if($key!=0)
    <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
        @foreach($val as $k => $colValue)
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td>
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif>{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td><select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td>
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif
                >@if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td>
            @foreach($options['options'] as $info)
            <input type="checkbox" onchange="updateData(this, 'checkbox')" class="{{$k}}{{$val['id']}}"
                   value="{{$info}}" datacol="{{$k}}" dataid="{{$val['id']}}" @if($info== $colValue) checked @endif>{{$info}}<br>
            @endforeach
        </td>
            @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '9')
            <?php if($colValue){
                //$carbonDate = new Carbon($colValue);
                    $carbonDate = Carbon::createFromTimestamp($colValue);
                $date = $carbonDate->diffForHumans();
            }else{
                $date = '';
                } ?>
                <td>{{$date}}</td>
        @elseif($k == 'id')
        <td hidden>{{$colValue}}</td>
        @else
        <td>{{$colValue}}</td>
        @endif

        @endforeach
    </tr>
    @endif

    @endforeach
    </tbody>
</table>


<script>
    var table_incr_id = '<?php echo $tableId;?>';
    var API_BASE_URL = '{{env('
    API_BASE_URL
    ')}}';

    function updateData(ths, method) {
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
            new_value = $(ths).find(":selected").text();
        } else if (method == 'teammates') {
            var key_name = $(ths).attr('id');
            key_name = key_name.split(":_:");
            coloumn_name = key_name[0];
            row_id = key_name[1];
            new_value = $(ths).find(":selected").val();
        }
        else if (method == 'checkbox') {
            var key_name = $(ths).attr('class');
            coloumn_name = $(ths).attr('datacol');
            row_id = $(ths).attr('dataid');
            var new_value = [];
            $("input:checkbox[class=" + key_name + "]:checked").each(function () {
                //new_value.push($(this).val());
                new_value = $(this).val();
            });
        }

        $.ajax({
            type: 'POST',
            url: API_BASE_URL + "/update",
            data: {'coloumn_name': coloumn_name, 'row_id': row_id, 'new_value': new_value, 'table_id': table_incr_id},
            success: function (data) {
                alert(data['msg']);
            }
        });
    }
</script>
