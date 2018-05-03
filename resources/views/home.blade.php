@extends('layouts.app')
@section('content')
<div class="tablist">
    <ul id="tablist">
        <!-- <li><a href="javascript:void(0);" class="cd-btn">+ Filter</a></li> -->
        <li role="presentation">
            <a href="{{env('APP_URL')}}/graph/{{$tableId}}">Graph</a>
        </li>
        <li role="presentation">
            <a href="{{env('APP_URL')}}/tables/{{$tableId}}/filter/All">All ({{$allTabCount}})
            </a>
        </li>
        @foreach($arrTabCount as $tabDetail)
        @foreach($tabDetail as $tabName => $tabCount)

        <li role="presentation">
            <a href="{{env('APP_URL')}}/tables/{{$tableId}}/filter/{{$tabName}}">{{$tabName}} ({{$tabCount}})
            </a>
        </li>
        @endforeach
        @endforeach
        @if(!$isGuestAccess)
        <!-- <li class="delete-rows-btn"><a href="#" onclick="DeleteRecords(); return false;">Delete</a></li> -->
        @endif
        <!-- Right Side Of Navbar -->
        <ul class="nav navbar-right user_dropdown">
            <!-- Authentication Links -->
            @guest
            <li><a href="{{env('SOCKET_LOGIN_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Login</a></li>
            <li><a href="{{env('SOCKET_SIGNUP_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Register</a></li>
            @else
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                   aria-haspopup="true">
                    {{$userTableName}} <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="{{route('tables')}}">Dashboard</a></li>
                    <li><a href="{{env('APP_URL')}}/graph/{{$tableId}}">Table Graph</a></li>
                    <li>
                        <a href="{{ route('profile') }}">
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                </ul>
            </li>
            @endguest
        </ul>
    </ul>
</div>

<div class="mt20 mtb20">
    <div class="col-sm-10">
        <select id="filter_condition" onchange="changeFilterJsonData('{{$tableId}}', 'search')" class="select-filter">
        <option value="and" @if(isset($tabcondition) && $tabcondition == 'and') selected="selected" @endif><span><i class="glyphicon glyphicon-indent-left"></i> That match all filter</span></option>
        <option value="or" @if(isset($tabcondition) && $tabcondition == 'or') selected="selected" @endif><span><i class="glyphicon glyphicon-indent-left"></i> That match any filter</span></option>
        </select>
        @foreach($activeTabFilter as $i => $tabFilter)
        @foreach($filters as $k=>$filter)
            @if(!isset($tabFilter[$k]))
                @continue
            @endif
            <div id="delete_filter_{{$i}}_{{$k}}" class="dropdown dropdown-filter-main filter-column">
                <a class="label label-filter dropdown" data-toggle="dropdown">
                    <span>
                        <i class="glyphicon glyphicon-stats"></i> 
                        {{$k}}
                        @foreach($filter['col_filter'] as $key =>$option)
                            @if(!isset($tabFilter[$k][$key]))
                                @continue
                            @endif
                            @if(in_array($key, array('is_unknown', 'has_any_value')))
                                {{$key.' null'}}
                            @else
                                {{$key.' '.$tabFilter[$k][$key]}}
                            @endif
                            <input type="hidden" name="filter_done_column_name[]" value="{{$k}}">
                            <input type="hidden" name="filter_done_column_type[]" value="{{$key}}">
                            <input type="hidden" name="filter_done_column_type_val[]" value="{{$tabFilter[$k][$key]}}">
                            <input type="hidden" name="filter_done_column_input_type[]" value="{{$filter['col_type']}}">
                        @endforeach
                <i class="glyphicon glyphicon glyphicon-trash" onclick="delete_filter_div('{{$k}}', '{{$i}}')"></i></span>
                </a>
            <ul class="dropdown-menu dropdown-menu-filter">
            @foreach($filter['col_filter'] as $key =>$option)
                @if(!empty($option) && $option == 'group')
                    <li class="li-radio">
                        <div class="form-check">
                            <label class="form-check-label radio-label">{{$key}}</label>
                        </div>
                    </li>
                @endif
                @if($option != 'group')
                    <li class="li-radio">
                        <div class="form-check">
                            <label class="form-check-label radio-label">
                                @if(isset($tabFilter[$k][$key]))
                                    <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                       id="{{$k}}_filter_text_{{$key}}"
                                       datacoltype="{{$filter['col_type']}}"
                                       onclick="showFilterInputText(this,'{{$k}}',{{$tableId}})"
                                       type="radio"
                                       aria-label="..." checked="checked">
                                @else
                                    <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                           id="{{$k}}_filter_text_{{$key}}"
                                           datacoltype="{{$filter['col_type']}}"
                                           onclick="showFilterInputText(this,'{{$k}}',{{$tableId}})"
                                           type="radio"
                                           aria-label="...">
                                @endif

                                @if($key == 'days_after')
                                    After (in days)
                                @elseif($key == 'days_before')
                                    Before (in days)
                                @else
                                    {{str_replace("days_","",$key)}}
                                @endif
                                @if($key != "is_unknown" && $key != "has_any_value")
                                    @if(isset($tabFilter[$k][$key]))
                                @if($filter['col_type'] == 'my teammates')
                                <select class="form-check-input filterinput form-control"
                                        name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}">
                                    @foreach($filter['col_options'] as $ind=>$opt)
                                    <option value="{{$opt['email']}}" {{($tabFilter[$k][$key]== $opt[
                                            'email'])?'selected':''}}>{{$opt['name']}}</option>
                                    @endforeach
                                </select>
                                @elseif($filter['col_type'] == 'dropdown')
                                <select class="form-check-input filterinput form-control"
                                        name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}">
                                    @foreach($filter['col_options'] as $ind=>$opt)
                                    <option value="{{$opt}}">{{$opt}}</option>
                                    @endforeach
                                </select>
                                @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before" )
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="date" value="{{$tabFilter[$k][$key]}}">
                                @else
                                <input class="form-check-input filterinput{{$k}} form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="text" value="{{$tabFilter[$k][$key]}}">
                                @endif
                                @else
                                @if($filter['col_type'] == 'my teammates')
                                <select class="form-check-input filterinput form-control"
                                        name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                        style="display:none;">
                                    @foreach($filter['col_options'] as $ind=>$opt)
                                    <option value="{{$opt['email']}}">{{$opt['name']}}</option>
                                    @endforeach
                                </select>
                                @elseif($filter['col_type'] == 'dropdown')
                                <select class="form-check-input filterinput form-control"
                                        name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                        style="display:none;">
                                    @foreach($filter['col_options'] as $ind=>$opt)
                                    <option value="{{$opt}}">{{$opt}}</option>
                                    @endforeach
                                </select>
                                @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before")
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="date" style="display:none;">
                                @else
                                <input class="form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="text" style="display:none;" size="4">
                                @endif
                                @endif
                                @endif
                            </label>
                        </div>
                    </li>
                @endif
            @endforeach
                    <li class="li-radio">
                        <div class="form-check">
                            <label class="form-check-label radio-label"><a href="javascript:void(0);" onclick="makeFilterJsonData('{{$tableId}}','search','{{$k}}', '{{$i}}');hideDropdown('{{$k}}', '{{$i}}')">Done</a></label>
                        </div>
                    </li>
                </ul>
            </div>
        @endforeach
        @endforeach
    <div class="dropdown dropdown-filter-main" id="add_column_filter">
            <a href="" class="dropdown dropdown-filters filter-link" data-toggle="dropdown" id="show"><i class="glyphicon glyphicon-plus"></i>  Add Filter</a>
            <ul class="dropdown-menu dropdown-menu-filter">
                    <li class="li-checkbox">
                        <div class="checkbox">
                        <div class="filter-list ">
                <ul id="filter-content" class="menu-content cd-panel-content">
                        @foreach($filters as $k=>$filter)
                        <li class="active" onclick="show_column_type('{{$k}}');">
                            <div class="form-check">
                                <label class="form-check-label">
                                    {{$k}}
                                    </label>
                            </div>
                            @if(isset($activeTabFilter[$k]))
                                <div id="condition_{{$k}}" class="filter-option">
                                    <ul class="dropdown-menu dropdown-menu-filter">
                            @else
                                <div id="condition_{{$k}}" class="hide filter-option">
                                    <ul class="dropdown-menu dropdown-menu-filter">
                            @endif
                            <form id="filterForm">
                            @foreach($filter['col_filter'] as $key =>$option)
                                        @if(!empty($option) && $option == 'group')
                                            <li class="li-radio">
                                                <div class="form-check">
                                                    <label class="form-check-label radio-label">{{$key}}</label>
                                                </div>
                                            </li>
                                        @endif
                                    @if($option != 'group')
                                        <li class="li-radio">
                                            <div class="form-check">
                                                <label class="form-check-label radio-label">
                                                    @if(isset($activeTabFilter[$k][$key]))
                                                        <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                                           id="{{$k}}_filter_text_{{$key}}"
                                                           datacoltype="{{$filter['col_type']}}"
                                                           onclick="showFilterInputText(this,'{{$k}}',{{$tableId}})"
                                                           type="radio"
                                                           aria-label="..." checked="checked">
                                                    @else
                                                        <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                                               id="{{$k}}_filter_text_{{$key}}"
                                                               datacoltype="{{$filter['col_type']}}"
                                                               onclick="showFilterInputText(this,'{{$k}}',{{$tableId}})"
                                                               type="radio"
                                                               aria-label="...">
                                                    @endif
                                                    
                                                    @if($key == 'days_after')
                                                        After (in days)
                                                    @elseif($key == 'days_before')
                                                        Before (in days)
                                                    @else
                                                        {{str_replace("days_","",$key)}}
                                                    @endif
                                                    @if($key != "is_unknown" && $key != "has_any_value")
                                                        @if(isset($activeTabFilter[$k][$key]))
                                                    @if($filter['col_type'] == 'my teammates')
                                                    <select class="form-check-input filterinput form-control"
                                                            name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}">
                                                        @foreach($filter['col_options'] as $ind=>$opt)
                                                        <option value="{{$opt['email']}}" {{($activeTabFilter[$k][$key]== $opt[
                                                                'email'])?'selected':''}}>{{$opt['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                    @elseif($filter['col_type'] == 'dropdown')
                                                    <select class="form-check-input filterinput form-control"
                                                            name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}">
                                                        @foreach($filter['col_options'] as $ind=>$opt)
                                                        <option value="{{$opt}}">{{$opt}}</option>
                                                        @endforeach
                                                    </select>
                                                    @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before" )
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="date" value="{{$activeTabFilter[$k][$key]}}">
                                                    @else
                                                    <input class="form-check-input filterinput{{$k}} form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="text" value="{{$activeTabFilter[$k][$key]}}">
                                                    @endif
                                                    @else
                                                    @if($filter['col_type'] == 'my teammates')
                                                    <select class="form-check-input filterinput form-control"
                                                            name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                            style="display:none;">
                                                        @foreach($filter['col_options'] as $ind=>$opt)
                                                        <option value="{{$opt['email']}}">{{$opt['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                    @elseif($filter['col_type'] == 'dropdown')
                                                    <select class="form-check-input filterinput form-control"
                                                            name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                            style="display:none;">
                                                        @foreach($filter['col_options'] as $ind=>$opt)
                                                        <option value="{{$opt}}">{{$opt}}</option>
                                                        @endforeach
                                                    </select>
                                                    @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before")
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="date" style="display:none;">
                                                    @else
                                                    <input class="form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="text" style="display:none;" size="4">
                                                    @endif
                                                    @endif
                                                    @endif
                                                </label>
                                            </div>
                                        </li>
                                    @endif
                            @endforeach
                                        <li class="li-radio">
                                            <div class="form-check">
                                                <label class="form-check-label radio-label change-filter-function"><a href="javascript:void(0);" onclick="makeFilterJsonData('{{$tableId}}','search','{{$k}}', '0');hideDropdown('{{$k}}', '0')">Done</a></label>
                                            </div>
                                        </li>
                            
                    </form>
                                    </ul>
                                </div>
                        </li>
                        @endforeach
                </ul>
            </div>
                        </div>
                    </li>
                   
              </ul>
    </div>
    <a class="filter-link m-l-15" href="#" onclick="saveTab()" data-dismiss="modal">Save segment </a>
    </div>
</div>
<div>
    <div class="topborder">
        <div class="col-sm-5 mb20">
            @foreach($arrTabCount as $tabDetail)
                @foreach($tabDetail as $tabName => $tabCount)
                @if($activeTab == $tabName)
                    <span class="sp_view_count">{{$tabDetail[$activeTab]}} users match</span><span class="total_count">of {{$allTabCount}}</span>
                @endif
                @endforeach
            @endforeach
            <a class="label label-filter label-filter-bordered bold" title="modal pop-up" data-target="#send_popup" data-toggle="modal"><span><i class="glyphicon glyphicon-send"></i> Message </i></span></a>
            @if(!$isGuestAccess)
            <a class="filter-link m-l-5 delete-rows-btn" href="#" data-toggle="dropdown" onclick="DeleteRecords(); return false;"><span> Delete</i></span></a>
            @endif
        </div>
                <div class="col-sm-5 pull-right">
                    <div class="pull-right">
                        <div class="inline-b">
                        <form class="search-form detail-form table-search" action="" name="queryForm"
                            onsubmit="searchKeyword(event, query.value)">
                            <label for="searchInput" class="label label-filter label-filter-bordered label-search-icon bold m-l-5"><i class="fa fa-search"></i></label>
                            <input type="text" name="query" class="form-control" placeholder="Search for..."
                                aria-label="Search for..." id="searchInput">
                        </form>
                        <a href="javascript:void(0);" id="addBtn" data-keyboard="true" data-target="#edit_user"
                        data-toggle="modal" onclick="getUserDetails(event,false,{{$tableId}}, 'Add')" class="label label-filter label-filter-bordered bold m-l-5">
                            <i class="glyphicon glyphicon-plus"></i>
                        </a>
                             <a class="label label-filter label-filter-bordered bold m-l-5" href="javascript:void(0);" id="columnSequencing" data-keyboard="true" onclick="openColumnModal()"><span><i class="fa fa-columns"></i>
                            <i class="caret"></i></span></a>
                        </div>
                        <div class="btn-group m-l-5" role="group" aria-label="...">
                        <button type="button" class="btn btn-default btn-lable"><span><i class="fa fa-list"></i></span></button>
                        <button type="button" class="btn btn-default btn-lable"><span><i class="fa fa-globe"></i></span></button>
                        </div>
                    </div>
                </div>
    </div>
</div>
<div class="nav-and-table  from-right nav-table-custom-response">
    <div id="user-board" class="user-dashboard user-custom-dashboard">
        <!-- Tab panes -->
        <div class="scroll-x flex" style="overflow:visible;">
            <div class="scroll-y flex w-100per" id="def_response">
                @include('table.response')
            </div>
            <div class="scroll-y flex" id="response">

            </div>
        </div>
    </div>
</div>
@stop
@section('pagescript')
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{asset('js/templates.js')}}"></script>
<script src="{{asset('js/functions.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/plugins/jquery.tablednd.js"></script>
<script type="text/javascript">
    var API_BASE_URL = "{{env('API_BASE_URL')}}";
    var activeTab = '{{$activeTab}}';
    var tableId = '{{$tableId}}';
    var allTabCount = '{{$allTabCount}}';
</script>
<!-- inline scripts -->
<script>
    // $('body').addClass('loader');
</script>
<script>
    function show_column_type(column_name)
    {
        var div_open = $('.filter-column').length;
        $('#condition_'+column_name).find("select").each(function(){
            if($(this).attr("name").indexOf('-') != -1)
            {
                $(this).attr("name", $(this).attr("name").split('-')[0]+'-'+div_open);
                $(this).attr("id", $(this).attr("id").split('-')[0]+'-'+div_open);
            }
            else
            {
                $(this).attr("name", $(this).attr("name")+'-'+div_open);
                $(this).attr("id", $(this).attr("id")+'-'+div_open);
            }
        });
        $('#condition_'+column_name).find("input").each(function(){
            if($(this).attr("name").indexOf('-') != -1)
            {
                $(this).attr("name", $(this).attr("name").split('-')[0]+'-'+div_open);
                $(this).attr("id", $(this).attr("id").split('-')[0]+'-'+div_open);
            }
            else
            {
                $(this).attr("name", $(this).attr("name")+'-'+div_open);
                $(this).attr("id", $(this).attr("id")+'-'+div_open);
            }
        });
        $('#condition_'+column_name).find("label.change-filter-function").each(function(){
            $(this).html('<a href="javascript:void(0);" onclick="makeFilterJsonData(\''+tableId+'\', \'search\', \''+column_name+'\', \''+div_open+'\');hideDropdown(\''+column_name+'\', \''+div_open+'\')">Done</a>');
        });
        var column_html = $('#condition_'+column_name).html();
        var add_filter_html='<div class="dropdown dropdown-filter-main open filter-column" id="delete_filter_'+div_open+'_'+column_name+'"><input type="hidden" id="'+div_open+'" value="'+column_name+'"/><a class="label label-filter dropdown" data-toggle="dropdown"><span><i class="glyphicon glyphicon-stats"></i> '+column_name+' <i class="glyphicon glyphicon glyphicon-trash" onclick="delete_filter_div(\''+column_name+'\', '+div_open+')"></i></span></a>'+$('#condition_'+column_name).html()+'</div>';
        $('#add_column_filter').before(add_filter_html);
        
        var elemToOpen = $('#delete_filter_'+div_open+'_'+column_name)[0];
        setTimeout(function() {
            $(elemToOpen).addClass('open');            
        }, 300)
    }
    function delete_filter_div(column_name, div_open)
    {
        $('#delete_filter_'+div_open+'_'+column_name).remove();
        makeFilterJsonData(tableId, 'search', column_name, div_open);
    }
    
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function () {
        if (activeTab != 'All') {
            $('.cd-panel').addClass('is-visible');
        }
        // toggle navigation
        $('[data-toggle="offcanvas"]').click(function () {
            $("#navigation").toggleClass("hidden-xs");
        });
        // init tooltip
        $("[data-toggle=tooltip]").tooltip();
        // toggle checkboxes
        $("#mytable #checkall").click(function () {
            if ($("#mytable #checkall").is(':checked')) {
                    $("#mytable input[type=checkbox]").each(function () {
                        $(this).prop("checked", true);
                    });
            } else {
                $("#mytable input[type=checkbox]").each(function () {
                    $(this).prop("checked", false);
                });
            }
        });
        $('#saveTabModel').hide();
        // $('#saveAsInput').hide();
        $('#saveTabButton').click(function () {
            var filterChecked = [];
            var jsonObject = {};
            for(var i = 0; i < $("input[name='filter_done_column_name[]']").length; i++)
            {
                if($("input[name='filter_done_column_name[]']")[i])
                {
                    if ($("input[name='filter_done_column_type[]']")[i].value == "has_any_value" || $("input[name='filter_done_column_type[]']")[i].value == 'is_unknown') {
                        $("input[name='filter_done_column_type_val[]']")[i].value = 1;
                    }
                    var subDoc = {};
                    subDoc[$("input[name='filter_done_column_type[]']")[i].value] = $("input[name='filter_done_column_type_val[]']")[i].value;
                    var subjsonObject = {};
                    subjsonObject[$("input[name='filter_done_column_name[]']")[i].value] = subDoc;
                    jsonObject[i] = subjsonObject;
                }
            }
        var tabName = $('#saveAsInput').val();
        obj = jsonObject;
        var condition = $('#filter_condition').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
                dataType: 'json', // Set datatype - affects Accept header
                url: API_BASE_URL + "/filter/save", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
                data: {'filter': JSON.stringify(obj), 'tab': tabName, 'tableId': tableId,'condition':condition}, // Some data e.g. Valid JSON as a string
                success: function (data) {
                    return false;
                    //window.setTimeout(function () {
                      //  location.reload()
                    //}, 2000);
                }
            });
        });
    });
    function SaveAsNew(state) {
        if (state) {
            $('#saveAsInput').val('');
            $('#saveAsInput').show();
        } else {
            $('#saveAsInput').hide();
            $('#saveAsInput').val(activeTab);
        }
    }
    var API_BASE_URL = "{{env('API_BASE_URL')}}";
    var activeTab = '{{$activeTab}}';
    var tableId = '{{$tableId}}';
    function sendMailSMS(type) {
        if (type == 'email') {
            var formData = $("#emailForm").serializeArray();
        }
        if (type == 'sms') {
            var formData = $("#smsForm").serializeArray();
        }
        var result = {};
        $.each(formData, function () {
            result[this.name] = this.value;
        });
        var JsonData = makeFilterJsonData(tableId, 'returnData');
        sendData(type, JsonData, result, tableId);
    }

    function timeToSend(type) {
        if (type == 'auto') {
            $('#auto').removeClass('hide');
            $('#now').addClass('hide');
        } else {
            $('#auto').addClass('hide');
            $('#now').removeClass('hide');
        }
    }
    function openColumnModal(){
        $('#column_sequence').modal('show');
        $("#table-1q").tableDnD();
    }
    function updateColumnSequence(){
        var tablearray = [];
        var displayarray = [];
        $("#table-1q tr").each(function() {
            if(this.id != 0)
            {
                var val = $('#reorder_column_'+this.id+':checked').val() ? 0 : 1
                var obj = {'key':this.id,'val':val};
                displayarray.push(obj);
                tablearray.push(this.id);
            }
        });
        if(tablearray.length > 1)
        {
            $.ajax({
                url: API_BASE_URL + '/rearrangeSequenceColumn',
                type: 'POST',
                data: {tableArray : tablearray, displayArray : displayarray},
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
    }
    </script> 
    <script>
        $(".tablist li").click(function() {
           if ($(".tablist li").removeClass("active")) {
               $(this).removeClass("active");
           }
           $(this).addClass("active");
       });
       function hideDropdown(col_name, div_open)
       {
            $('#delete_filter_'+div_open+'_'+col_name).removeClass('open');
            var radio_type = $("#delete_filter_"+div_open+"_"+col_name+" input[type='radio']:checked");
            var radioname = radio_type.attr('dataid');
            var coltype = radio_type.attr('datacoltype');
            var radioButtonValue = $('#'+col_name+'_filter_val_'+radioname+'-'+div_open).val();
            if (typeof radioButtonValue === "undefined") {
                radioButtonValue ='';
            }
            var a_html = '<span><i class="glyphicon glyphicon-stats"></i> '+col_name+' '+radioname+' '+radioButtonValue+' <i class="glyphicon glyphicon glyphicon-trash" onclick="delete_filter_div(\''+col_name+'\', \''+div_open+'\')"></i></span><input type="hidden" name="filter_done_column_name[]" value="'+col_name+'"/><input type="hidden" name="filter_done_column_type[]" value="'+radioname+'"/><input type="hidden" name="filter_done_column_type_val[]" value="'+radioButtonValue+'"/><input type="hidden" name="filter_done_column_input_type[]" value="'+coltype+'"/>';
            $('#delete_filter_'+div_open+'_'+col_name+' a:first').html(a_html);
        }
    </script>
  
  <script>
   $(window).scroll(function() {    
    var scroll = $(window).scrollTop();

    if(scroll >= 200) {
        $("thead").addClass("fix-header");
    } else {
        $("thead").removeClass("fix-header");
    }
});
</script>
 <script>
   $(function(){
    var current = window.location.href;
    //if(window.location.href == $(#tablist li a[]))
});

console.log(window.location.href);
 </script>

@stop
@section('models')
<!-- Modal -->
<div id="add_project" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header login-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Add Project</h4>
            </div>
            <div class="modal-body">
                <input type="text" placeholder="Project Title" name="name">
                <input type="text" placeholder="Post of Post" name="mail">
                <input type="text" placeholder="Author" name="passsword">
                <textarea placeholder="Desicrption"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel" data-dismiss="modal">Close</button>
                <button type="button" class="add-project" data-dismiss="modal">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->

<!-- Modal -->
<div id="edit_user" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:800px">
        <!-- Modal content-->
        <div style="background:rgb(237,239,240)" class="modal-content">
            <div class="modal-header" id="modal_header_column">
                <img style="width:21px;height:21px;vertical-align:middle" src="{{ asset('img/docs.svg') }}" alt="docs">
                <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" id="mod-head_edit" class="modal-title">Edit User</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form id="editUserDetails">
                <div class="modal-body" style="width:800px">
                    <div class="col-xs-8" id="edit_users_body"></div>
                    <input type='hidden' id="is_edit" value="">
                    <!-- <div style="width:20px" class="col-xs-1">&nbsp;</div> -->
                    <div style="padding-right:0px;padding-left:0px" class="col-xs-4" id="sec_edit_users_body"></div>
                    <div class="col-xs-8">
                        <h3 style="margin-left:25px;font-size:18px;font-weight:600;margin-top:20px">Activity</h3>
                        <br>
                        <br>
                        <div id="activity_log">

                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="overflow: hidden;width:750px">
                    <input type="hidden" id="eId"/>
                    <input type="hidden" id="tokenKey"/>
                    <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                    <button type="button" style="width:75px;height:40px" class="btn btn-success" data-dismiss="modal" onclick="editUserData('edit')">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->

<!-- Modal -->
<div id="column_sequence" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:800px">
        <!-- Modal content-->
        <div style="background:rgb(237,239,240)" class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Reorder Columns</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form id="editColumnSequence">
                <div class="modal-body">
                    <div class="col-xs-8" id="edit_column_body"></div>
                    <div style="width:20px" class="col-xs-2">&nbsp;</div>
                    <div style="padding-right:0px;padding-left:0px" class="col-xs-4" id="sec_edit_users_body"></div>
                    <div class="col-xs-12">
                    @if(!empty($structure) && !$isGuestAccess)
                    <table id="table-1q" class="table basic table-bordred" >
                        <thead><tr id="0"><th>Sequence</th><th>Column Name</th><th>Hidden</th></tr></thead>
                    @foreach($structure as $key => $val)
                    <tr id="{{$val['id']}}"><td>{{$val['ordering']}}</td><td>{{$key}}</td><td><input type="checkbox" id="reorder_column_{{$val['id']}}" name="reorder_column_{{$val['id']}}" @if($val['display'] == 0) checked="checked" @endif /></td></tr>
                    @endforeach
                    </table>
                    @endif
                    </div>
                </div>
                <div class="modal-footer" style="overflow: hidden;width:750px">
                    <button type="button" style="width:75px;height:40px" class="btn btn-success" onclick="updateColumnSequence()">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->

<!-- default type -->
<div id="default_type" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div style="background:rgb(237,239,240)" class="modal-content">
            <div class="modal-header">
                <img style="width:21px;height:21px;vertical-align:middle" src="{{ asset('img/docs.svg') }}" alt="docs">
                <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" id="mod-head" class="modal-title">default values</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form>
                <div class="modal-body">
                    <div>
                        <h3>Activity</h3>
                        <br>
                        <br>
                        <div class="form-group col-xs-12">
                            <label class="">
                                <textarea class="form-control custom-input" name="" id="" cols="73" rows="5"></textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" style="width:75px;height:40px" class="btn btn-success" data-dismiss="modal" onclick="editUserData('edit')">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end modal -->

<!-- tab modal -->
<div class="modal fade" id="saveTabModel" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center"><strong>Save segment</strong></h4>
            </div>

            <div class="modal-body">
                <form class="clearfix">
                    <div class="row">
                    <div class="checkbox col-sm-12">
                        <label class="radio inline no_indent">
                            <input type="radio" value="" name="tabName" onChange='SaveAsNew(false)'>
                            Save changes to the segment <span id="replacetabName"> 'vijay'</span>
                        </label>
                    </div>
                    </div>
                    <div class="row">

                    <div class="checkbox col-sm-4 p-r-zero">
                        <label class="radio inline no_indent">
                            <input type="radio" value="" name="tabName" id="showSaveAs" onChange='SaveAsNew(true)' checked>Create new segment
                        </label>
                    </div>
                    <div class="col-sm-4 col-xs-5 p-l-zero">
                        <input type="text" class="form-control newSegment" value="" id="saveAsInput">
                    </div>
                    </div>

                   
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="saveTabButton">Save changes
                </button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<!-- add user modal || plus button -->
<div id="add_user" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-md" role="content">
        <!-- Modal content-->
        <div class="modal-content">
            <!-- <div class="modal-header login-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Edit User</h4>
            </div> -->
            <div class="row">
                <form id="addUserDetails">
                    <div class="modal-body pull-left" id="add_users_body">

                    </div>
            </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="editUserData('add')">
                    Add
                </button>
            </div>
        </div>
    </div>
</div>

<!-- send modal -->
<div id="send_popup" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md" role="content">
        <div class="modal-content">
            <ul class="nav nav-tabs" aria-labelledby="menu1">
                <li class="active"><a href="#email" data-toggle="tab">Email</a></li>
                <li class=""><a href="#sms" data-toggle="tab">SMS</a></li>
                <li class=""><a href="#webhook" data-toggle="tab">Webhook</a></li>
                <li class="pull-right">
                    <a href="javascript:void(0);" onclick="event.stopPropagation();">
                        <label onclick="timeToSend('now')" class="radio-inline"><input type="radio" name="type"
                                                                                       value="">Now</label>
                        <label onclick="timeToSend('auto')" class="radio-inline"><input type="radio" value=""
                                                                                        name="type">Auto</label>
                    </a>
                </li>

            </ul>
            <div class="modal-body">


                <div class="option_box" id="now">Send Now option block</div>
                <div class="option_box" id="auto">Send Auto option block</div>

                <div id="setTabs" class="tab-content">
                    <div class="tab-pane active" id="email">
                        <form class="" id="emailForm">
                            <div class="form-group">
                                <label class="" for="">From Email: </label>
                                <input type="text" class="form-control" id="from_email" name="from_email"/>
                            </div>

                            <div class="form-group">
                                <label class="" for="">From Name: </label>
                                <input type="text" class="form-control" id="from_name" name="from_name"/>
                            </div>

                            <div class="form-group">
                                <label class="" for="">Email Column: </label>
                                <input type="text" class="form-control" id="email_column" name="email_column"/>
                            </div>

                            <div class="form-group">
                                <label class="" for="">Subject: </label>
                                <input type="text" class="form-control" id="subject" name="subject"/>
                            </div>

                            <div class="form-group">
                                <label class="" for="">Mail Content: </label>
                                <textarea id="mailContent" class="form-control" name="mailContent"></textarea>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-primary btn-md" onclick="sendMailSMS('email')"
                                        data-dismiss="modal">Send
                                </button>
                            </div>

                        </form>
                    </div>
                    <div class="tab-pane" id="sms">
                        <form class="" id="smsForm">

                            <div class="form-group">
                                <label class="" for="">Sender Id : </label>
                                <input type="" class="form-control " id="sender" name="sender">
                            </div>

                            <div class="form-group">
                                <label class="" for="">Route : </label>
                                <input type="" class="form-control " id="route" name="route">
                            </div>

                            <div class="form-group">
                                <label class="" for="">Message Content : </label>
                                <input type="" class="form-control " id="message" name="message">
                            </div>

                            <div class="form-group">
                                <label class="" for="">Mobile Column : </label>
                                <input type="" class="form-control " id="mobile_columnn" name="mobile_columnn">
                            </div>

                            <button type="button" class="btn btn-primary btn-md" onclick="sendMailSMS('sms')"
                                    data-dismiss="modal">Send
                            </button>
                        </form>
                    </div>
                    <div class="tab-pane" id="webhook">

                        <div class="form-group">
                            <label class="" for="">URL : </label>
                            <input type="" class="form-control " id="url" name="mobile_columnn">
                        </div>
                        <button type="button" class="btn btn-primary btn-md" onclick="" data-dismiss="modal">Send
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

@stop


 