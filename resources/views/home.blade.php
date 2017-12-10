@extends('layouts.app')
@section('content')
<div class="tablist">
    <ul id="tablist">
        <li><a href="javascript:void(0);" class="cd-btn">+ Filter</a></li>
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
        <!-- Right Side Of Navbar -->
        <ul class="nav navbar-right user_dropdown">
            <!-- Authentication Links -->
            @guest
            <li><a href="{{env('SOCKET_LOGIN_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Login</a></li>
            <li><a href="{{env('SOCKET_SIGNUP_URL')}}&redirect_uri={{env('APP_URL')}}/socketlogin">Register</a></li>
            @else
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
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
        <form class="search-form pull-right" action="" name="queryForm"
              onsubmit="searchKeyword(event, query.value)">
            <label for="searchInput"><i class="fa fa-search"></i></label>
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
                                           onclick="showDiv('condition_{{$k}}')" aria-label="..." checked="checked">
                                    @else
                                    <input type="checkbox" class="filterConditionName" dataid="{{$k}}"
                                           onclick="showDiv('condition_{{$k}}')" aria-label="...">
                                    @endif
                                    {{$k}}</label>
                            </div>
                            @if(isset($activeTabFilter[$k]))
                            <div id="condition_{{$k}}" class="filter-option">
                                @else
                                <div id="condition_{{$k}}" class="hide filter-option">
                                    @endif
                                    @foreach($filter as $key =>$option)
                                    <div class="form-check">
                                        <label class="form-check-label radio-label">
                                            @if(isset($activeTabFilter[$k][$key]))
                                            <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                                   onclick="showFilterInputText(this,'{{$k}}')" type="radio"
                                                   aria-label="..." checked="checked">
                                            @else
                                            <input class="form-check-radio" name="{{$k}}_filter" dataid="{{$key}}"
                                                   onclick="showFilterInputText(this,'{{$k}}')" type="radio"
                                                   aria-label="...">
                                            @endif
                                            {{$key}}
                                            @if(isset($activeTabFilter[$k][$key]))
                                            <input class="form-check-input filterinput{{$k}} form-control"
                                                   name="{{$k}}_filter_text" id="{{$k}}_filter_text_{{$key}}" type="text" value="{{$activeTabFilter[$k][$key]}}">
                                            @else
                                            <input class="form-check-input filterinput{{$k}} form-control"
                                                   name="{{$k}}_filter_text" id="{{$k}}_filter_text_{{$key}}" type="text">
                                            @endif
                                        </label>
                                    </div>
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
            <div class="scroll-y flex" id="response">
                @include('table.response')
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

<script type="text/javascript">
    var API_BASE_URL = '{{env('API_BASE_URL')}}';
    var activeTab = '{{$activeTab}}';
    var tableId = '{{$tableId}}';
</script>
<!-- inline scripts -->
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function () {
    initFilterSlider();
    if (activeTab != 'All'){
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
    var filterCheckedElement = $(".filterConditionName:checked")
            filterCheckedElement.each(function () {
            dataid = $(this).attr('dataid');
            filterChecked.push($(this).attr('dataid'));
            var radioButton = $("#condition_" + dataid + " input:checked");
            var radioname = radioButton.attr('dataid')
                    var radioButtonValue = $("#" + dataid + "_filter_text_" + radioname).val();
            var subDoc = {};
            subDoc[radioname] = radioButtonValue
                    jsonObject[dataid] = subDoc;
            });
    var tabName = $('#saveAsInput').val();
    obj = jsonObject;
    $.ajaxSetup({
    headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
    });
    console.log(obj);
    $.ajax({
    type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
            dataType: 'json', // Set datatype - affects Accept header
            url: API_BASE_URL + "/filter/save", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            data: {'filter':JSON.stringify(obj), 'tab':tabName, 'tableId':tableId}, // Some data e.g. Valid JSON as a string
            success: function (data) {
            console.log(data);
            window.setTimeout(function(){
            location.reload()
            }, 2000);
            }
    });
    });
    $(".form-check-input").on('keyup', function() {
    clearInterval(myInterval);
    //var tableId = '{{ collect(request()->segments())->last() }}';
    // console.log('clear interval');
    if (globaltimeout != null) clearTimeout(globaltimeout);
    globaltimeout = setTimeout(function() {
    makeFilterJsonData(tableId);
    }, 600);
    })

            $(".form-check-input").blur(function() {
    window.setTimeout(function() {
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
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <!-- <div class="modal-header login-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Edit User</h4>
            </div> -->
            <form id="editUserDetails">
                <div class="modal-body" id="edit_users_body">
                </div>

                <div class="modal-footer">
                    <input type="hidden" id="eId"/>
                    <input type="hidden" id="tokenKey"/>
                    <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                    <button type="button" class="btn btn-success" data-dismiss="modal" onclick="editUserData()">
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
<!-- /.modal -->
@stop
