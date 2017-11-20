<table class="table basic table-bordred">
    @foreach($allTabs as $key=>$val)
    @if($key==0)
    <thead id="userThead">
        <tr>
            <th><span class="fixed-header"></span></th>
            @foreach($val as $k => $colName)
            <th><span class="fixed-header">{{$k}}</span></th>
            @endforeach
        </tr>
    </thead>
    <tbody id="all_users">
            <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
            <td></td>
            @foreach($val as $k => $colValue)
            @if(isset($structure[$k]) and $structure[$k]['type'] == 'radio button')
            <td>
                @foreach(explode(',', $structure[$k]['value']) as $info)
                <input type="radio" onchange="updateData(this, 'radio_button')"  name="{{$k}}:_:{{$val['id']}}" value="{{$info}}" @if($info == $colValue) checked @endif >{{$info}}<br>
                @endforeach
            </td>
            @elseif(isset($structure[$k]) and $structure[$k]['type'] == 'dropdown')
            <td><select id="{{$k}}:_:{{$val['id']}}" onchange="updateData(this, 'dropdown')">
                    @foreach(explode(',', $structure[$k]['value']) as $info)
                    <option value="{{$info}}" @if($info == $colValue) selected="selected" @endif >{{$info}}</option>
                    @endforeach
                </select>   
            </td>
            @else
            <td>{{$colValue}}</td>
            @endif
            @endforeach
        </tr>
    </tbody>
    @endif
    @if($key!=0)
    <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}','{{$tableId}}')">
        <td></td>
        @foreach($val as $k => $colValue)
        @if(isset($structure[$k]) and $structure[$k]['type'] == 'radio button')
        <td>
            @foreach(explode(',', $structure[$k]['value']) as $info)
            <input type="radio" onchange="updateData(this, 'radio_button')" name="{{$k}}:_:{{$val['id']}}" value="{{$info}}" @if($info == $colValue) checked @endif >{{$info}}<br>
            @endforeach
        </td>
        @elseif(isset($structure[$k]) and $structure[$k]['type'] == 'dropdown')
        <td><select id="{{$k}}:_:{{$val['id']}}" onchange="updateData(this, 'dropdown')">
                @foreach(explode(',', $structure[$k]['value']) as $info)
                <option value="{{$info}}" @if($info == $colValue) selected="selected" @endif  >{{$info}}</option>
                @endforeach
            </select>   
        </td>
        @else
        <td>{{$colValue}}</td>
        @endif

        @endforeach
    </tr>
    @endif

    @endforeach
</table>


<script>
    var table_incr_id = '<?php echo $tableId;?>';
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
            function updateData(ths, method){
            if (method == 'radio_button'){
                var key_name = $(ths).attr('name');
                key_name = key_name.split(":_:");
                coloumn_name = key_name[0];
                row_id = key_name[1];
                new_value = $(ths).attr('value');
            }
            else if (method == 'dropdown'){
            var key_name = $(ths).attr('id');
                    key_name = key_name.split(":_:");
                    coloumn_name = key_name[0];
                    row_id = key_name[1];
                    new_value = $(ths).find(":selected").text();
            }

            $.ajax({
            type: 'POST',
                    url: API_BASE_URL + "/update",
                    data: { 'coloumn_name': coloumn_name, 'row_id': row_id ,'new_value' : new_value , 'table_id' : table_incr_id},
                    success: function(data) {
                        alert(data['msg']);
                    }
            });
            }
</script>