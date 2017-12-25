<?php use Carbon\Carbon; ?>
<table class="table basic table-bordred">

    @foreach($allTabs as $key=>$val)
    @if($key==0)

    <thead id="userThead">
    <tr>
        <!-- <th><span class="fixed-header"></span></th> -->
        @foreach($val as $k => $colName)
        @if($k!='id')
        <th><span class="fixed-header">{{$k}}</span></th>
        @else
        <th hidden class="fixed-header"><span>{{$k}}</span></th>
        @endif
        @endforeach
    </tr>
    </thead>
    <tbody id="all_users">
    <tr data-toggle="modal" id="tr{{$val['id']}}" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
        <!-- <td></td> -->
        @foreach($val as $k => $colValue)
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td class="{{$k}}">
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td class="{{$k}}">
            <select id="{{$k}}:_:{{$val['id']}}" name="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                <option value="">select</option>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td class="{{$k}}">
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif>
                @if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif
                </option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td class="{{$k}}">
            @foreach($options['options'] as $info)
            <input type="checkbox" onchange="updateData(this, 'checkbox')" class="{{$k}}{{$val['id']}}"
                   value="{{$info}}" datacol="{{$k}}" dataid="{{$val['id']}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
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
        <td><span data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</span></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}">{{$colValue}}</td>
        @else
        <td class="{{$k}}">{{$colValue}}</td>
        @endif
        @endforeach
    </tr>

    @endif
    @if($key!=0)
    <tr id="tr{{$val['id']}}" data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
        @foreach($val as $k => $colValue)
        @if(isset($structure[$k]) and $structure[$k]['column_type_id'] == '7')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td class="{{$k}}">
            @foreach($options['options'] as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}"
                   value="{{$info}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '6')
        <td class="{{$k}}"><select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'dropdown')">
                <?php $options = json_decode($structure[$k]['value'], true); ?>
                <option value="">select</option>
                @foreach($options['options'] as $info)
                <option value="{{$info}}" @if($info== $colValue) selected="selected" @endif>{{$info}}</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '10')
        <td class="{{$k}}">
            <select id="{{$k}}:_:{{$val['id']}}" onclick="event.stopPropagation();"
                    onchange="updateData(this, 'teammates')">
                @foreach($teammates as $team)
                <option value="{{$team['email']}}" @if($team[
                'email'] == $colValue) selected="selected" @endif>
                @if(!empty($team['name'])){{$team['name']}}@else{{$team['email']}}@endif</option>
                @endforeach
            </select>
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['column_type_id'] == '8')
        <?php $options = json_decode($structure[$k]['value'], true); ?>
        <td class="{{$k}}">
            @foreach($options['options'] as $info)
            <input type="checkbox" onchange="updateData(this, 'checkbox')" class="{{$k}}{{$val['id']}}"
                   value="{{$info}}" datacol="{{$k}}" dataid="{{$val['id']}}" @if($info== $colValue) checked @endif
                   onclick="event.stopPropagation();">{{$info}}<br>
            @endforeach
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
        <td class="{{$k}}"><span data-toggle="tooltip" data-placement="top" title="{{$dateActual}}">{{$date}}</span></td>
        @elseif($k == 'id')
        <td hidden class="{{$k}}">{{$colValue}}</td>
        @else
        <td class="{{$k}}">{{$colValue}}</td>
        @endif

        @endforeach
    </tr>
    @endif

    @endforeach
    </tbody>
</table>
<input type="hidden" value="{{$tableAuth}}" id="tableAuthKey"/>


<script>
    var table_incr_id = '<?php echo $tableId;?>';
    var API_BASE_URL = '{{env('
    API_BASE_URL
    ')}}';

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
                location.reload();
            },
        });
    }
</script>
