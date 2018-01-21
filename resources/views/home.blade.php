@extends('layouts.app')
@section('content')
<div class="tablist">
    <ul id="tablist">
        <li><a href="javascript:void(0);" class="cd-btn">+ Filter</a></li>
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
            <!--<a href="{{ collect(request()->segments())->last() }}/{{$tabName}}">{{$tabName}}-->
            <a href="{{env('APP_URL')}}/tables/{{$tableId}}/filter/{{$tabName}}">{{$tabName}} ({{$tabCount}})
            </a>
        </li>
        @endforeach
        @endforeach
        <li class="delete-rows-btn"><a href="#" onclick="DeleteRecords();return false;">Delete</a></li>
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
                    <!-- {{ Auth::user()->name }}  -->
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

        <li class="pull-right">
            <a href="javascript:void(0);" id="addBtn" data-keyboard="true" data-target="#edit_user"
                                  data-toggle="modal" onclick="getUserDetails(event,false,{{$tableId}})">
                <i class="glyphicon glyphicon-plus"></i>
            </a>
        </li>


        <form class="search-form pull-right" action="" name="queryForm"
              onsubmit="searchKeyword(event, query.value)">
            <label for="searchInput"><i class="glyphicon glyphicon-search" data-toggle="tooltip" data-placement="bottom"
                                        title="search data"></i></label>
            <input type="text" name="query" class="form-control" placeholder="Search for..."
                   aria-label="Search for..." id="searchInput">
        </form>
       
    </ul>
</div>

<div class="cd-panel">
    <div class="cd-wrp">
        <header class="cd-panel-header">
            <a href="javascript:void(0);" class="pull-right cd-panel-close">Close</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="saveTab()" data-dismiss="modal">Save
                filter
            </button>
        </header>
        <div class="nav-side-menu cd-panel-container">
            <div class="filter-list ">
                <ul id="filter-content" class="menu-content cd-panel-content">
                    <form id="filterForm">
                        @foreach($filters as $k=>$filter)
                        <li class="active">
                            <div class="form-check">
                                <label class="form-check-label">
                                    @if(isset($activeTabFilter[$k]))
                                    <input type="checkbox" class="filterConditionName" dataid="{{$k}}"
                                           datacoltype="{{$filter['col_type']}}"
                                           onclick="showDiv('condition_{{$k}}')" aria-label="..." checked="checked">
                                    @else
                                    <input type="checkbox" class="filterConditionName" dataid="{{$k}}"
                                           datacoltype="{{$filter['col_type']}}"
                                           onclick="showDiv('condition_{{$k}}')" aria-label="...">
                                    @endif
                                    {{$k}}</label>
                            </div>
                            @if(isset($activeTabFilter[$k]))
                            <div id="condition_{{$k}}" class="filter-option">
                                @else
                                <div id="condition_{{$k}}" class="hide filter-option">
                                    @endif
                                    @foreach($filter['col_filter'] as $key =>$option)
                                        @if(!empty($option) && $option == 'group')
                                            <div class="filter_group">{{$key}}</div>
                                        @endif
                                        @if($option != 'group')
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
                                                    More than (in days)
                                                @elseif($key == 'days_before')
                                                    less than (in days)
                                                @else
                                                    {{str_replace("days_","",$key)}}
                                                @endif
                                                @if($key != "is_unknown" && $key != "has_any_value")
                                                @if(isset($activeTabFilter[$k][$key]))
                                                @if($filter['col_type'] == 'my teammates')
                                                <select class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}">
                                                    @foreach($filter['col_options'] as $ind=>$opt)
                                                    <option value="{{$opt['email']}}" {{($activeTabFilter[$k][$key]== $opt[
                                                    'email'])?'selected':''}}>{{$opt['name']}}</option>
                                                    @endforeach
                                                </select>
                                                @elseif($filter['col_type'] == 'dropdown')
                                                <select class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}">
                                                    <option value=""></option>
                                                    @foreach($filter['col_options'] as $ind=>$opt)
                                                    <option value="{{$opt}}">{{$opt}}</option>
                                                    @endforeach
                                                </select>
                                                @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before" )
                                                    <input class="date-filter-input form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        type="date" value="{{$activeTabFilter[$k][$key]}}">
                                                @else
                                                    <input class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        type="text" value="{{$activeTabFilter[$k][$key]}}">
                                                @endif
                                                @else
                                                @if($filter['col_type'] == 'my teammates')
                                                <select class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        style="display:none;">
                                                    @foreach($filter['col_options'] as $ind=>$opt)
                                                    <option value="{{$opt['email']}}">{{$opt['name']}}</option>
                                                    @endforeach
                                                </select>
                                                @elseif($filter['col_type'] == 'dropdown')
                                                <select class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        style="display:none;">
                                                    <option value=""></option>
                                                    @foreach($filter['col_options'] as $ind=>$opt)
                                                    <option value="{{$opt}}">{{$opt}}</option>
                                                    @endforeach
                                                </select>
                                                @elseif($filter['col_type'] == 'date' && $key != "days_after" && $key != "days_before")
                                                    <input class="date-filter-input form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        type="date" style="display:none;">
                                                @else
                                                    <input class="form-check-input filterinput{{$k}} form-control"
                                                        name="{{$k}}_filter_val_" id="{{$k}}_filter_val_{{$key}}"
                                                        type="text" style="display:none;" size="4">
                                                @endif
                                                @endif
                                                @endif
                                            </label>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                        </li>
                        @endforeach
                    </form>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="nav-and-table  from-right">
    <div id="user-board" class="user-dashboard">
        <!-- Tab panes -->
        <div class="scroll-x flex">
            <div class="scroll-y flex" id="def_response">
                @include('table.response')
            </div>
            <div class="scroll-y flex" id="response">
                
            </div>
        </div>
    </div>
</div>

<a href="javascript:void(0);" id="myBtn" title="modal pop-up" data-target="#send_popup" data-toggle="modal"><span><img
                id="wiz" src="{{ asset('img/sending.svg') }}" alt="sending"/></span></a>
@stop
@section('pagescript')
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{asset('js/templates.js')}}"></script>
<script src="{{asset('js/functions.js')}}"></script>

<script type="text/javascript">
    var API_BASE_URL = "{{env('API_BASE_URL')}}";
    var activeTab = '{{$activeTab}}';
    var tableId = '{{$tableId}}';
</script>
<!-- inline scripts -->
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function () {
        initFilterSlider();
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
        $('#saveAsInput').hide();
        $('#saveTabButton').click(function () {
            var filterChecked = [];
            var jsonObject = {};
            var filterCheckedElement = $(".filterConditionName:checked");
            filterCheckedElement.each(function () {
                dataid = $(this).attr('dataid');
                filterChecked.push($(this).attr('dataid'));
                var radioButton = $("#condition_" + dataid + " input:checked");
                var radioname = radioButton.attr('dataid');
                var radioButtonValue = $("#" + dataid + "_filter_val_" + radioname).val();
                if (radioname == "has_any_value" || radioname == 'is_unknown') {
                    radioButtonValue = "1";
                }
                var subDoc = {};
                subDoc[radioname] = radioButtonValue;
                jsonObject[dataid] = subDoc;
            });
            var tabName = $('#saveAsInput').val();
            obj = jsonObject;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
                dataType: 'json', // Set datatype - affects Accept header
                url: API_BASE_URL + "/filter/save", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
                data: {'filter': JSON.stringify(obj), 'tab': tabName, 'tableId': tableId}, // Some data e.g. Valid JSON as a string
                success: function (data) {
                    window.setTimeout(function () {
                        location.reload()
                    }, 2000);
                }
            });
        });
        $(".form-check-input").on('change', function () {
            clearInterval(myInterval);
            //var tableId = '{{ collect(request()->segments())->last() }}';
            if (globaltimeout != null) clearTimeout(globaltimeout);
            globaltimeout = setTimeout(function () {
                makeFilterJsonData(tableId, 'Search');
            }, 600);
        });

        $(".form-check-input").on('keyup', function () {
            clearInterval(myInterval);
            //var tableId = '{{ collect(request()->segments())->last() }}';
            if (globaltimeout != null) clearTimeout(globaltimeout);
            globaltimeout = setTimeout(function () {
                makeFilterJsonData(tableId, 'Search');
            }, 600);
        });

        $(".form-check-input").blur(function () {
            window.setTimeout(function () {
                //startInterval();
            }, 20000);
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
            <div class="modal-header">
                <img style="width:21px;height:21px;vertical-align:middle" src="{{ asset('img/docs.svg') }}" alt="docs">
                <span style="font-size:18px;vertical-align:middle;margin-left:5px;font-weight:700" id="mod-head" class="modal-title">Edit User</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form id="editUserDetails">
                <div class="modal-body" style="width:800px">
                    <div class="col-xs-8" id="edit_users_body"></div>
                    <div style="width:20px" class="col-xs-1">&nbsp;</div>
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

<!-- tab modal -->
<div class="modal fade" id="saveTabModel" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Save Tab</h4>
            </div>

            <div class="modal-body">
                <form class="clearfix">
                    <div class="checkbox">
                        <label class="radio inline no_indent">
                            <input type="radio" value="" name="tabName" onChange='SaveAsNew(false)'>Update in
                            current tab
                        </label>
                    </div>

                    <div class="checkbox">
                        <label class="radio inline no_indent">
                            <input type="radio" value="" name="tabName" id="showSaveAs" onChange='SaveAsNew(true)'>Save
                            as
                        </label>
                    </div>

                    <div class="col-xs-8">
                        <input type="text" class="form-control" value="" id="saveAsInput">
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


    @stop
    <script type="text/javascript">
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

    </script>
