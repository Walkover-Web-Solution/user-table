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
        <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}')">
            <td></td>
            @foreach($val as $k => $colValue)
            <td>{{$colValue}}</td>
            @endforeach
        </tr>
    </tbody>
    @endif
    @if($key!=0)
    <tr data-toggle="modal" data-target="#edit_user" onclick="getUserDetails('{{$val['id']}}')">
        <td></td>
        @foreach($val as $k => $colValue)
        <td>{{$colValue}}</td>
        @endforeach
    </tr>
    @endif

    @endforeach
</table>