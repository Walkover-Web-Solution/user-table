@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/toast.css')}}"><!--ToastCSS-->
<div class="tablist">
    <ul id="tablist" class="table-filter-ul">
        <!-- <li><a href="javascript:void(0);" class="cd-btn">+ Filter</a></li> -->
        <!-- <li role="presentation">
            <a href="{{env('APP_URL')}}/graph/{{$tableId}}">Graph</a>
        </li> -->
        <li role="presentation">
            <a href="{{env('APP_URL')}}/tables/{{$tableId}}/filter/All">All ({{$allTabCount}})
            </a>
        </li>
        <li role="presentation" id="filterLoadingText">
            <a href="javascript:void(0);">
                Loding Filters...
            </a>
        </li>
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
       <label class="label-filter-select">
        <select id="filter_condition" onchange="changeFilterJsonData('{{$tableId}}', 'search')" class="select-filter">
        <option value="and" @if(isset($tabcondition) && $tabcondition == 'and') selected="selected" @endif><span><i class="glyphicon glyphicon-indent-left"></i> That match all filter</span></option>
        <option value="or" @if(isset($tabcondition) && $tabcondition == 'or') selected="selected" @endif><span><i class="glyphicon glyphicon-indent-left"></i> That match any filter</span></option>
        </select>
        </label>
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
                            @elseif($key == 'between')
                                {{$key.' Last '.$tabFilter[$k][$key]['before'].' days to next '.$tabFilter[$k][$key]['after'].' days'}}
                            @else
                                {{$key.' '.$tabFilter[$k][$key]}}
                            @endif
                            <input type="hidden" name="filter_done_column_name[]" value="{{$k}}">
                            <input type="hidden" name="filter_done_column_type[]" value="{{$key}}">
                            @if($key == 'between')
                                <input type="hidden" name="filter_done_column_type_val[]" id="filter_done_column_type_val_{{$k}}_before" value="{{$tabFilter[$k][$key]['before']}}">
                                <input type="hidden" id="filter_done_column_type_val_{{$k}}_after" value="{{$tabFilter[$k][$key]['after']}}">
                            @else
                                <input type="hidden" name="filter_done_column_type_val[]" value="{{$tabFilter[$k][$key]}}">
                            @endif
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
                                @elseif($key == 'between')
                                    Between (in days)
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
                                @elseif($filter['col_type'] == 'date' && $key != "between" && $key != "days_after" && $key != "days_before" )
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="date" value="{{$tabFilter[$k][$key]}}">
                                @elseif($filter['col_type'] == 'date' && $key == "between")
                                <table class="table-between">
                                <tr>
                                <td>
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}_before" id="{{$k}}_filter_val_{{$key}}_before"
                                       type="text" value="{{$tabFilter[$k][$key]['before']}}" placeholder="Last Days">
                                </td><td>To</td><td>
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}_after" id="{{$k}}_filter_val_{{$key}}_after"
                                       type="text" value="{{$tabFilter[$k][$key]['after']}}" placeholder="Next Days">
                                </td>
                                </tr>
                                </table>
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
                                @elseif($filter['col_type'] == 'date' && $key != "between" && $key != "days_after" && $key != "days_before")
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                       type="date" style="display:none;">
                                @elseif($filter['col_type'] == 'date' && $key == "between")
                                <table class="table-between">
                                <tr>
                                <td>                
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}_before" id="{{$k}}_filter_val_{{$key}}_before"
                                       type="text" style="display:none;">
                                </td><td>To</td><td>
                                <input class="date-filter-input form-check-input filterinput form-control"
                                       name="{{$k}}_filter_val_{{$key}}_after" id="{{$k}}_filter_val_{{$key}}_after"
                                       type="text" style="display:none;">
                                </td>
                                </tr>
                                </table>
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
                                                    @elseif($filter['col_type'] == 'date' && $key != "between" && $key != "days_after" && $key != "days_before" )
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="date" value="{{$activeTabFilter[$k][$key]}}">
                                                    @elseif($filter['col_type'] == 'date' && $key == "between")
                                                    <table class="table-between">
                                                    <tr>
                                                    <td>
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}_before" id="{{$k}}_filter_val_{{$key}}_before"
                                                           type="text" value="{{$activeTabFilter[$k][$key]}}" placeholder="Last Days">
                                                    </td><td>To</td><td>
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}_after" id="{{$k}}_filter_val_{{$key}}_after"
                                                           type="text" value="{{$activeTabFilter[$k][$key]}}" placeholder="Next Days">
                                                    </td>
                                                    </tr>
                                                    </table>
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
                                                    @elseif($filter['col_type'] == 'date' && $key != "between" && $key != "days_after" && $key != "days_before")
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}" id="{{$k}}_filter_val_{{$key}}"
                                                           type="date" style="display:none;">
                                                    @elseif($filter['col_type'] == 'date' && $key == "between")
                                                    <table class="table-between">
                                                    <tr>
                                                    <td>
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}_before" id="{{$k}}_filter_val_{{$key}}_before"
                                                           type="text" style="display:none;" placeholder="Last Days">
                                                    </td><td>To</td><td>
                                                    <input class="date-filter-input form-check-input filterinput form-control"
                                                           name="{{$k}}_filter_val_{{$key}}_after" id="{{$k}}_filter_val_{{$key}}_after"
                                                           type="text" style="display:none;" placeholder="Next Days">
                                                    </td>
                                                    </tr>
                                                    </table>
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
            <div id="show_count" style="float: left;">
            
            </div>
            <a class="label label-filter label-filter-bordered bold" title="modal pop-up" data-target="#send_popup" data-toggle="modal"><span><i class="glyphicon glyphicon-send"></i> Message </i></span></a>
            
            @if(isset($addAction) && $addAction=="yes")
            
                <a class="label label-filter label-filter-bordered bold add-action-filter" title="modal pop-up" data-target="#ifftModal" data-toggle="modal"><span><i class="glyphicon glyphicon-record"></i> Add Action </i></span></a>

            @endif

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
                        <div class="pull-left pos-relative">
                        <a href="javascript:void(0);" id="addBtn" data-keyboard="true"
                         class="label label-filter label-filter-bordered bold m-l-5">
                            <i class="glyphicon glyphicon-plus"></i>
                        </a>
                        <div class="addEntries">
                            <div class="col-sm-12 add-entry-inner">
                                            <a onclick="getUserDetails(event,false,{{$tableId}}, 'Add')"  data-target="#edit_user" data-toggle="modal" class="btn btn-primary">Add an entry now</a>
                            </div>
                            <div class="col-sm-12 add-entry-inner">
                            <a class="text-black import initiateUpload"><span><i class="fa fa-upload" aria-hidden="true"></i></span>
                                        <span class="sp-inline-import">
                                            Import<br>
                                        We can do it manually for you or you can also do it via trigger and send addon available in Google sheets
                                        </span>
                            </a>
                            </div>
                            <div class="col-sm-12 add-entry-inner column">
                                <div>
                                            <h3>API doc</h3>
                                </div>
                               <div>
                                    <a href="https://docs.usertable.in" target="_blank" class="btn btn-default m-r-28">Add</a>
                                    <a href="https://docs.usertable.in" target="_blank" class="btn btn-default m-r-28">Edit</a>
                                    <a href="https://docs.usertable.in" target="_blank" class="btn btn-default m-r-28">Delete</a>
                                    <a href="https://docs.usertable.in" target="_blank" class="btn btn-default">Fetch</a>
                               </div>
                               <div class="text-right col-sm-offset-10 m-t-20">
                                   <a href="https://docs.usertable.in/collection" target="_blank">more</a>
                               </div>
                            </div>
                           
                        </div>
                        </div>
                        <!-- onclick="getUserDetails(event,false,{{$tableId}}, 'Add')"  data-target="#edit_user" -->
                             <a class="label label-filter label-filter-bordered bold m-l-5" href="javascript:void(0);" id="columnSequencing" data-keyboard="true" onclick="openColumnModal()"><span><i class="fa fa-columns"></i>
                            </span></a>
                        </div>
                        <div class="btn-group m-l-5" role="group" aria-label="...">
                        <button type="button" class="btn btn-default btn-lable" onClick="click_show_table();"><span><i class="fa fa-list"></i></span></button>
                        <button type="button" class="btn btn-default btn-lable" onClick="click_show_graph();"><span><i class="fa fa-globe"></i></span></button>
                        </div>
                    </div>
                </div>
    </div>
</div>
<div class="nav-and-table  from-right nav-table-custom-response" id="show_table_div">
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
<!-- Graph Div Start-->
<div class="nav-and-table from-right nav-table-custom-response" style="display: none;" id="show_graph_div">
    <div id="user-board" class="user-dashboard user-custom-dashboard">
        <!-- Tab panes -->
        <div class="scroll-x">
            <div class="scroll-y graph-page-container" id="response">
                <div class="top-chart-container">
                    <div class="ajax-loader-container">
                        <div class="loading-icon">
                            <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>

                    <form class="form-inline graph-form">

                        <div class="form-group" style="display:none;">
                            <label for="email"  class="control-caption">Column</label>
                            <select class="form-control" id="column2">
                                @if(isset($other_columns))
                                @foreach( $other_columns as $other_column)
                                    <option value="{{$other_column}}">{{$other_column}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email" class="control-caption">Date Column </label>
                            <select class="form-control" id="column1" onchange="loadGraph()">
                                @if(isset($date_columns))
                                @foreach( $date_columns as $date_column)
                                    <option value="{{$date_column}}">{{$date_column}}</option>
                                @endforeach
                                @endif
                            </select>

                        </div>

                        <div class="form-group">
                            <label for="email"  class="control-caption">Date Range</label>
                            <input class="form-control" id="barDate" name="date" placeholder="MM/DD/YYY" type="text" value="{{$rangeStart}}"/>
                            To
                            <input class="form-control" id="barDate1" name="date1" placeholder="MM/DD/YYY" type="text" value="{{$rangeEnd}}"/>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnLoadGraph">Load Graph</button>
                    </form>
                    <div class="charts-container">
                        <div class="chart-first">
                            <canvas id="myChart" height="400"></canvas>
                        </div>
                    </div>
                </div>
                <form class="form-inline graph-form">
                    <div class="form-group">
                        <label for="email" class="control-caption">Date Column </label>
                        <select class="form-control" id="column3" onchange="createAllPieCharts();">
                            @if(isset($date_columns))
                            @foreach( $date_columns as $date_column)
                                <option value="{{$date_column}}">{{$date_column}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email"  class="control-caption">Date Range</label>
                        <input class="form-control" id="pieDate" name="pieDate" placeholder="MM/DD/YYY" type="text"  value="{{$rangeStart}}"/>
                        To
                        <input class="form-control" id="pieDate1" name="pieDate1" placeholder="MM/DD/YYY" type="text"  value="{{$rangeEnd}}"/>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnLoadCharts">Load Graph</button>
                </form>

                <div class="charts-container">
                    <div class="pie-chart-container row">
                        @if(isset($other_columns))
                        @foreach( $other_columns as $other_column)
                            <div class="pie-chart col-md-2 col-lg-2 col-sm-4 col-xs-6">
                                <div class="column-caption">Column : {{$other_column}}</div>
                                <canvas id="id_{{$other_column}}" width="200" height="200"></canvas>
                            </div>
                        @endforeach
                        @endif
                        <div class="clearfix"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>



<!-- IFFT Modal -->
    
    <div class="modal fade" id="ifftModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Action on <span style="font-weight: bolder;font-style: italic;" id="filterTitle"></span></h4>
                </div>
                <div class="modal-body">

                    @php
                        $actionEmailTo = "";
                        $actionEmailSubject = "";
                        $actionEmailFrom = "";
                        $actionEmailFromName = "";
                        $actionEmailContent = "";

                        $actionSmsTo = "";
                        $actionSmsSenderId = "";
                        $actionSmsRoute = "";
                        $actionSmsContent = "";


                        $actionColumnName = "";
                        $actionColumnVal = "";
                        $actionWebhookUrl = "";
                        $actionIsArchived = "";

                        $actionColumnId = "";
                    @endphp


                    @if(isset($actionValueData->action_value))
                        @php
                            $actionValueParams = json_decode($actionValueData->action_value);
                        @endphp

                        @if(isset($actionValueParams->ALERT))

                            @if(isset($actionValueParams->ALERT->email_to))
                                @php $actionEmailTo = $actionValueParams->ALERT->email_to; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->email_subject))
                                @php $actionEmailSubject = $actionValueParams->ALERT->email_subject; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->email_from))
                                @php $actionEmailFrom = $actionValueParams->ALERT->email_from; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->email_from_name))
                                @php $actionEmailFromName = $actionValueParams->ALERT->email_from_name; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->email_content))
                                @php $actionEmailContent = $actionValueParams->ALERT->email_content; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->sms_to))
                                @php $actionSmsTo = $actionValueParams->ALERT->sms_to; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->sms_sender_id))
                                @php $actionSmsSenderId = $actionValueParams->ALERT->sms_sender_id; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->sms_route))
                                @php $actionSmsRoute = $actionValueParams->ALERT->sms_route; @endphp
                            @endif

                            @if(isset($actionValueParams->ALERT->sms_content))
                                @php $actionSmsContent = $actionValueParams->ALERT->sms_content; @endphp
                            @endif

                        @endif

                        @if(isset($actionValueParams->MODIFY_COLUMN))

                            @if(isset($actionValueParams->MODIFY_COLUMN->column_name))
                                @php $actionColumnName = $actionValueParams->MODIFY_COLUMN->column_name; @endphp
                            @endif

                            @if(isset($actionValueParams->MODIFY_COLUMN->value))
                                @php $actionColumnVal = $actionValueParams->MODIFY_COLUMN->value; @endphp
                            @endif

                        @endif

                        @if(isset($actionValueParams->WEBHOOK))

                            @if(isset($actionValueParams->WEBHOOK->webhook_url))
                                @php $actionWebhookUrl = $actionValueParams->WEBHOOK->webhook_url; @endphp
                            @endif

                        @endif

                        @if(isset($actionValueParams->ARCHIVE))

                            @if(isset($actionValueParams->ARCHIVE->archive_status))
                                @php $actionIsArchived = $actionValueParams->ARCHIVE->archive_status; @endphp
                            @endif

                        @endif

                    @endif


                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

                            <!-- Add Alert to action -->

                            <div class="panel panel-default">
                              <div class="panel-heading" role="tab" id="headingOne">
                                <h4 class="panel-title">
                                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Add Alert
                                    </a>
                                </h4>
                              </div>
                              <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                <div class="panel-body">
                                    <form id="ifftInitForm" name="ifftInitForm">
                                        <div>
                                            <!-- Nav tabs -->
                                            <ul class="nav nav-tabs" role="tablist" style="margin: 0px !important;">
                                                <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Email</a></li>
                                                <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">SMS</a></li>
                                            </ul>
                                            
                                            <!-- Tab panes -->
                                            <div class="tab-content" style="margin-top:10px;">
                                                <div role="tabpanel" class="tab-pane active" id="home">
                                                    <div class="form-group">
                                                        <label for="actionEmailField">Email From</label>
                                                        <input type="text" class="form-control" id="actionEmailFromField" name="actionEmailFromField" placeholder="example@test.com" style="margin-top: 10px;" value="{{$actionEmailFrom}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionEmailField">Name From</label>
                                                        <input type="text" class="form-control" id="actionFromNameField" name="actionFromNameField" placeholder="Syestem Admin" style="margin-top: 10px;" value="{{$actionEmailFromName}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionEmailField">Send Email To</label>
                                                        <input type="text" class="form-control" id="actionEmailField" name="actionEmailField" placeholder="example@test.com,example1@test.com" style="margin-top: 10px;" value="{{$actionEmailTo}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionEmailField">Email Subject</label>
                                                        <input type="text" class="form-control" id="actionEmailSubjectField" name="actionEmailSubjectField" placeholder="Action is here..." style="margin-top: 10px;" value="{{$actionEmailSubject}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionEmailField">Email Content</label>
                                                        <textarea class="form-control" id="actionEmailContentField" name="actionEmailContentField" placeholder="Mail Content Here..." style="margin-top: 10px;">{{$actionEmailContent}}</textarea>
                                                    </div>
                                                </div>
                                                <div role="tabpanel" class="tab-pane" id="profile">
                                                    <div class="form-group">
                                                        <label for="actionSmsField">Sender ID</label>
                                                        <br />
                                                        <input type="text" class="form-control" id="actionSmsSenderIdField" name="actionSmsSenderIdField" placeholder="1234567890" style="margin-top: 10px;" value="{{$actionSmsSenderId}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionSmsField">Route</label>
                                                        <br />
                                                        <input type="text" class="form-control" id="actionSmsRouteField" name="actionSmsRouteField" placeholder="1" style="margin-top: 10px;" value="{{$actionSmsRoute}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionSmsField">SMS Content</label>
                                                        <br />
                                                        <input type="text" class="form-control" id="actionSmsContentField" name="actionSmsContentField" placeholder="Hi, Action is here.." style="margin-top: 10px;" value="{{$actionSmsContent}}" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="actionSmsField">Send SMS To</label>
                                                        <br />
                                                        <input type="text" class="form-control" id="actionSmsField" name="actionSmsField" placeholder="1234567890,0987654321" style="margin-top: 10px;" value="{{$actionSmsTo}}" />
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        <button type="button" class="btn btn-default" onclick="saveActionToFilter()">Save Action</button>
                                        <input type="hidden" name="activeTabId" id="activeTabId" value="{{ $activeTabId }}" />
                                        <input type="hidden" name="actionId" id="actionId" value="ALERT" />
                                    </form>
                                </div>
                              </div>
                            </div>

                            <!-- Add Alert to action -->

                            <!-- Add Column Modify to action -->

                            <div class="panel panel-default">
                              <div class="panel-heading" role="tab" id="headingTwo">
                                <h4 class="panel-title">
                                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Modify Column
                                    </a>
                                </h4>
                              </div>
                              <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                                <div class="panel-body">
                                        <form id="ifftInitForm2" name="ifftInitForm2">
                                            <div class="form-group">
                                                <label for="actionEmailField">Select Column Type</label>
                                                <div id="selectColumnTypes">
                                                    <select class="modifyColumnSelect" name="modifyColumnSelect">
                                                        <option value="0">Select Column</option>
                                                        @foreach($columnsWithTypes as $key => $val)
                                                            @if($val['column_name']==$actionColumnName)

                                                                @php
                                                                    $actionColumnId = $val['id'];
                                                                @endphp
                                                            
                                                            @endif
                                                            
                                                            <option @if($val['column_name']==$actionColumnName) {{ "selected='selected'" }}  @endif class="modify_column_options" value="{{ $val['column_name'] }}" data-type="{{ $val['column_type']['column_name'] }}" data-id="{{ $val['id'] }}">
                                                                {{ $val['column_name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="actionSmsField">Assign Value</label>
                                                <span id="assignColumnValues">
                                                        @foreach($columnsWithTypes as $key => $val)
                                                        {{-- <option class="modify_column_options" value="{{ $val['column_name'] }}" data-type="{{ $val['column_type']['column_name'] }}" data-id="{{ $val['id'] }}">
                                                            {{ $val['column_name'] }}
                                                        </option> --}}
                                                        <span class="modifyColumnValues" id="column_val_type_{{ $val['id'] }}" style="display:none;">
                                                            @switch($val['column_type']['column_name'])
                                                                @case ("text")
                                                                    <div class="form-group">
                                                                        <input type="text" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif" />
                                                                    </div>
                                                                @break;
                                                                @case ("phone")
                                                                <div class="form-group">
                                                                    <input type="tel" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif" />
                                                                </div>
                                                                @break;
                                                                @case ("any number")
                                                                    <div class="form-group">
                                                                        <input type="number" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif" />
                                                                    </div>
                                                                @break;
                                                                @case ("airthmatic number")
                                                                    <div class="form-group">
                                                                        <input type="number" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif" />
                                                                    </div>
                                                                @break;
                                                                @case ("email")
                                                                    <div class="form-group">
                                                                        <input type="email" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif" />
                                                                    </div>
                                                                @break;
                                                                @case ("dropdown")
                                                                    <div class="form-group">
                                                                        <select name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}">
                                                                            <option> Choose Value</option>
                                                                            @php
                                                                                $defaultValues = json_decode($val['default_value']);
                                                                            @endphp
                                                                            @if(isset($defaultValues->options) && count($defaultValues->options)>0)
                                                                                @foreach($defaultValues->options as $optVal)
                                                                                    <option>{{ $optVal }}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                                @break;
                                                                @case ("date")
                                                                    <div class="form-group">
                                                                        <input type="date" name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}" value="@if($actionColumnId==$val['id']){{$actionColumnVal}}@endif" />
                                                                    </div>
                                                                @break;
                                                                @case ("my teammates")
                                                                    @if(isset($teammates) && count($teammates)>0)
                                                                        <select name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}">
                                                                            <option value=""> Choose Value</option>
                                                                        @foreach($teammates as $mates)
                                                                            <option value="{{$mates['email']}}" @if($actionColumnId==$val['id'] && $mates['email']==$actionColumnVal) {{"selected='selected'"}} @endif>
                                                                                {{ $mates['name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                        </select>
                                                                    @endif
                                                                @break;
                                                                @case ("long text")
                                                                    <div class="form-group">
                                                                        <textarea name="modify_column_vals" class="form-control" id="column_val_field_{{ $val['id'] }}">@if($actionColumnId==$val['id']) {{ $actionColumnVal }} @endif</textarea>
                                                                    </div>
                                                                @break;
                                                            @endswitch
                                                        </span>
                                                    @endforeach
                                                </span>
                                            </div>
                                            <button type="button" class="btn btn-default" onclick="saveColumnAction()">Save Action</button>
                                            <input type="hidden" name="activeTabId" id="activeTabId" value="{{ $activeTabId }}" />
                                            <input type="hidden" name="actionId" id="actionId" value="MODIFY_COLUMN" />
                                            <input type="hidden" name="actualModifiedValue" id="actualModifiedValue" value="" />
                                        </form>
                                </div>
                              </div>
                            </div>

                            <!-- Add Column Modify to action -->
                            
                            <!-- Add Webhook to action -->
                            
                            <div class="panel panel-default">
                              <div class="panel-heading" role="tab" id="headingThree">
                                <h4 class="panel-title">
                                  <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Add Webhook
                                  </a>
                                </h4>
                              </div>
                              <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                <div class="panel-body">
                                        <form id="ifftInitForm3" name="ifftInitForm3">
                                            <div class="form-group">
                                                <label for="actionWebhookField">Webhook Field</label>
                                                <input type="text" class="form-control" id="actionWebhookField" name="actionWebhookField" placeholder="http://requestb.in/Xhyx" style="margin-top: 10px;" value="{{ $actionWebhookUrl }}"  />
                                            </div>
                                            <button type="button" class="btn btn-default" onclick="saveWebhookAction()">Save Action</button>
                                            <input type="hidden" name="activeTabId" id="activeTabId" value="{{ $activeTabId }}" />
                                            <input type="hidden" name="actionId" id="actionId" value="WEBHOOK" />
                                        </form>
                                </div>
                              </div>
                            </div>

                            <!-- Add Webhook to action -->


                            <!-- Add Archive to action -->
                            
                            <div class="panel panel-default">
                              <div class="panel-heading" role="tab" id="headingThree">
                                <h4 class="panel-title">
                                  <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Add To Archive
                                  </a>
                                </h4>
                              </div>
                              <div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                <div class="panel-body">
                                        <form id="ifftInitForm4" name="ifftInitForm4">
                                            <div class="form-group">
                                                <label for="actionWebhookField">Webhook Field</label>
                                                <br /><br />
                                                <input type="checkbox" id="actionArchiveField" name="actionArchiveField" style="margin-top: 10px;" @if($actionIsArchived=="on") {{"checked='checked'"}} @endif /> Add to archive
                                            </div>
                                            <button type="button" class="btn btn-default" onclick="saveArchiveAction()">Save Action</button>
                                            <input type="hidden" name="activeTabId" id="activeTabId" value="{{ $activeTabId }}" />
                                            <input type="hidden" name="actionId" id="actionId" value="ARCHIVE" />
                                        </form>
                                </div>
                              </div>
                            </div>

                            <!-- Add Archive to action -->


                          </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

<!-- IFFT Modal -->




<ul class="nav navbar-nav flex-ul settingUl">
@if((count($structure) > 1) && !$isGuestAccess) 
<li><a class="btn btn-primary" href="https://docs.usertable.in" target="_blank">Configure API</a></li>
<li><a class="text-black import initiateUpload"><span><i class="fa fa-upload" aria-hidden="true"></i></span>import</a></li>
<li class="strong">or</li>
<li><a onclick="getUserDetails(event,false,{{$tableId}}, 'Add')"  data-target="#edit_user" data-toggle="modal" class="btn btn-primary">Add some entries</a></li>
 @endif
</ul>
<!-- Graph Div End-->
@stop
@section('pagescript')
<!-- Scripts -->


<script src="{{ asset('js/app.js') }}"></script>
<script src="{{asset('js/templates.js')}}"></script>
<script src="{{asset('js/functions.js')}}"></script>
<script src="{{asset('js/Chart.bundle.js')}}"></script>
<script src="{{asset('js/bootstrap-datepicker.min.js')}}"></script>
<link href="{{ asset('css/bootstrap-datepicker3.css') }}" rel="stylesheet">
<script type="text/javascript" src="{{  asset('js/toast.js')}}"></script> <!--Toast JS-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqgrid/4.6.0/plugins/jquery.tablednd.js"></script>
<script type="text/javascript" src="{{ url('js/jquery.tablesorter.min.js') }}"></script>
<script type="text/javascript">
    var API_BASE_URL = "{{env('API_BASE_URL')}}";
    var activeTab = '{{$activeTab}}';
    var tableId = '{{$tableId}}';
    var allTabCount = '{{$allTabCount}}';
    var actionColumnId = '{{ $actionColumnId }}';
    var view = 'table';
</script>
<!-- inline scripts -->
<script>
    // $('body').addClass('loader');
</script>
<script>
    $(document).ready(function(){
        if(actionColumnId!="")
        {
            $("#column_val_type_"+actionColumnId).css('display','block');
        }
        $.ajax({
            url:"/getTableFilters/{{ $tableId }}/{{ $activeTab }}",
            method:"GET",
            success:function(respData){
                $("#filterLoadingText").remove();
                $(".table-filter-ul").append(respData);
            }
        });
    });
    function click_show_table()
    {
        view = 'table';
        $('#show_table_div').attr("style", "display:block");
        $('#show_graph_div').attr("style", "display:none");
        $('.settingUl').attr("style", "display:flex");
    }
    function click_show_graph()
    {
        view = 'graph';
        $('#show_graph_div').attr("style", "display:block");
        $('#show_table_div').attr("style", "display:none");
        $('.settingUl').attr("style", "display:none");
        loadGraph();
        createAllPieCharts();
    }
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
            var filtercolumns = [];
            var filterChecked = [];
            var jsonObject = {};
            for(var i = 0; i < $("input[name='filter_done_column_name[]']").length; i++)
            {
                if($("input[name='filter_done_column_name[]']")[i])
                {
                    if($("input[name='filter_done_column_type[]']")[i].value == "between")
                    {
                        var between = {};
                        between['before'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_before").val();
                        between['after'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_after").val();
                        var filter_done_column_type_val = between;
                    }
                    else
                    {
                        var filter_done_column_type_val = $("input[name='filter_done_column_type_val[]']")[i].value;
                    }
                    if ($("input[name='filter_done_column_type[]']")[i].value == "has_any_value" || $("input[name='filter_done_column_type[]']")[i].value == 'is_unknown') {
                        var filter_done_column_type_val = 1;
                    }
                    var subDoc = {};
                    subDoc[$("input[name='filter_done_column_type[]']")[i].value] = filter_done_column_type_val;
                    var subjsonObject = {};
                    subjsonObject[$("input[name='filter_done_column_name[]']")[i].value] = subDoc;
                    jsonObject[i] = subjsonObject;
                }
            }
            $("input[name='filter_columns[]']:checked").each(function () {
                filtercolumns.push($(this).val());
            });
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
                data: {'filter': JSON.stringify(obj), 'tab': tabName, 'tableId': tableId,'condition':condition, 'filtercolumns' : filtercolumns}, // Some data e.g. Valid JSON as a string
                success: function (data) {
                    window.setTimeout(function () {
                        location.reload()
                    }, 2000);
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
    function sendMailSMS(type, test = false) {
        var result = {};
        if (type == 'email') {
            if($('#from_email').val() == '')
            {
                alert('Please enter from email.');
                return false;
            }
            else if($('#email_column').val() == '')
            {
                alert('Please enter email Column.');
                return false;
            }
            else if($('#subject').val() == '')
            {
                alert('Please enter subject.');
                return false;
            }
            else if($('#mailContent').val() == '')
            {
                alert('Please enter mail Content.');
                return false;
            }
            result['from_email'] = $('#from_email').val();
            result['from_name'] = $('#from_name').val();
            result['email_column'] = $('#email_column').val();
            result['subject'] = $('#subject').val();
            result['mailContent'] = $('#mailContent').val();
            if(test !== false)
                result['testemailid'] = $('#testemailid').val();
            //if($('#send_auto').)
        }
        else if (type == 'sms') {
            if($('#sender').val() == '')
            {
                alert('Please enter sender.');
                return false;
            }
            else if($('#route').val() == '')
            {
                alert('Please enter route.');
                return false;
            }
            else if($('#message').val() == '')
            {
                alert('Please enter message.');
                return false;
            }
            else if($('#mobile_columnn').val() == '')
            {
                alert('Please enter mobile columnn.');
                return false;
            }
            result['sender'] = $('#sender').val();
            result['route'] = $('#route').val();
            result['message'] = $('#message').val();
            result['mobile_columnn'] = $('#mobile_columnn').val();
            if(test !== false)
                result['testsmsno'] = $('#testsmsno').val();
        }
        if($("#send_auto").is(":checked"))
            result['send_auto'] = 'yes';
        
        var returnData = makeFilterJsonData(tableId, 'returnData');
        var filter_condition = $('#filter_condition').val();
        sendData(type, returnData[1], result, tableId, filter_condition, returnData[0], test, activeTab);
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
                    $.toast({
                        heading: 'Success',
                        text: 'Table Structure Updated',
                        showHideTransition: 'slide',
                        icon: 'success'
                    });
                    setTimeout(function(){
                        location.reload();
                    },1000);
                    /*alert(info.success);
                    location.reload();*/
                }
            });
        }
    }
    
        function CreatePieChart(element, labels,values,colors,bcolors) {
            var ctx = document.getElementById(element).getContext('2d');
            data = {
                "labels": labels,
                "datasets": [{
                    "label": "",
                    "data": values,
                    "backgroundColor": colors
                }]
            };
            var myPieChart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    legend: {display: false}
                }
            });
            pieCharts.push(myPieChart);
        }
        function CreateBarChart(element, labels, values,colors,bColors) {
            if(window.mybarChart == undefined)
            {
                var ctx = document.getElementById(element).getContext('2d');
                window.mybarChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '',
                            data: values,
                            backgroundColor: colors,
                            borderColor:bColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }else{
                var newData = {
                    labels: labels,
                    datasets: [{
                        label: '',
                        data: values,
                        backgroundColor: colors,
                        borderColor:bColors,
                        borderWidth: 1  
                    }]
                };
                window.mybarChart.data.datasets[0].data = values;
                window.mybarChart.data.labels = labels;
                window.mybarChart.update();
            }
        }
        //Get Graph functions.
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
        function random_rgba() {
            var o = Math.round, r = Math.random, s = 255;
            var opacity = 0.5;// r().toFixed(1);

            return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + opacity + ')';
        }
        function getGraphData(dateColumn, secondColumn) {
            var tableName = "{{$tableId}}";
            var startDate = $("#barDate").val();
            var endDate = $("#barDate1").val();
            var jsonObject = {};
            var coltypeObject = {};
            for(var i = 0; i < $("input[name='filter_done_column_name[]']").length; i++)
            {
                if($("input[name='filter_done_column_name[]']")[i])
                {
                    if($("input[name='filter_done_column_type[]']")[i].value == "between")
                    {
                        var between = {};
                        between['before'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_before").val();
                        between['after'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_after").val();
                        var filter_done_column_type_val = between;
                    }
                    else
                    {
                        var filter_done_column_type_val = $("input[name='filter_done_column_type_val[]']")[i].value;
                    }
                    if ($("input[name='filter_done_column_type[]']")[i].value == "has_any_value" || $("input[name='filter_done_column_type[]']")[i].value == 'is_unknown') {
                        var filter_done_column_type_val = 1;
                    }
                    var subDoc = {};
                    subDoc[$("input[name='filter_done_column_type[]']")[i].value] = filter_done_column_type_val;
                    var subjsonObject = {};
                    subjsonObject[$("input[name='filter_done_column_name[]']")[i].value] = subDoc;
                    jsonObject[i] = subjsonObject;
                    var subcoltypeObject = {};
                    subcoltypeObject[$("input[name='filter_done_column_name[]']")[i].value] = $("input[name='filter_done_column_input_type[]']")[i].value;
                    coltypeObject[i] = subcoltypeObject;
                }
            }
            var condition = $('#filter_condition').val();
            
            var dataUrl = "{{env('APP_URL')}}/graphdatafilter";
            $.post(dataUrl, {'filter' : jsonObject, 'condition' : condition, 'tableName' : tableName, 'dateColumn' : dateColumn, 'secondColumn' : dateColumn, "startDate" : startDate, "endDate" : endDate}, function (response) {
                var data = JSON.parse(response);
                var Total_data = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    Total_data += item.Total;
                }
                var thurshold = Total_data / 25;
                var dates = new Array();
                var values = new Array();
                var colors = new Array();
                var bcolors = new Array();
                var others_count = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    dates.push(item.LabelColumn);
                    values.push(item.Total);
                    colors.push(random_rgba());
                    bcolors.push("rgba(100,100,100,1)");
                }
                CreateBarChart("myChart", dates,values,colors,bcolors);
                $(".top-chart-container .ajax-loader-container").hide();
            });
        }
        function getPieGraphData(dateColumn, secondColumn, element) {
            var tableName = "{{$tableId}}";
            var startDate = $("#pieDate").val();
            var endDate = $("#pieDate1").val();
            var jsonObject = {};
            var coltypeObject = {};
            for(var i = 0; i < $("input[name='filter_done_column_name[]']").length; i++)
            {
                if($("input[name='filter_done_column_name[]']")[i])
                {
                    if($("input[name='filter_done_column_type[]']")[i].value == "between")
                    {
                        var between = {};
                        between['before'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_before").val();
                        between['after'] = $("#filter_done_column_type_val_"+$("input[name='filter_done_column_name[]']")[i].value+"_after").val();
                        var filter_done_column_type_val = between;
                    }
                    else
                    {
                        var filter_done_column_type_val = $("input[name='filter_done_column_type_val[]']")[i].value;
                    }
                    if ($("input[name='filter_done_column_type[]']")[i].value == "has_any_value" || $("input[name='filter_done_column_type[]']")[i].value == 'is_unknown') {
                        var filter_done_column_type_val = 1;
                    }
                    var subDoc = {};
                    subDoc[$("input[name='filter_done_column_type[]']")[i].value] = filter_done_column_type_val;
                    var subjsonObject = {};
                    subjsonObject[$("input[name='filter_done_column_name[]']")[i].value] = subDoc;
                    jsonObject[i] = subjsonObject;
                    var subcoltypeObject = {};
                    subcoltypeObject[$("input[name='filter_done_column_name[]']")[i].value] = $("input[name='filter_done_column_input_type[]']")[i].value;
                    coltypeObject[i] = subcoltypeObject;
                }
            }
            var condition = $('#filter_condition').val();
            
            var dataUrl = "{{env('APP_URL')}}/graphdatafilter";
            $.post(dataUrl, {'filter' : jsonObject, 'condition' : condition, 'tableName' : tableName, 'dateColumn' : dateColumn, 'secondColumn' : secondColumn, "startDate" : startDate, "endDate" : endDate}, function (response) {
                var data = JSON.parse(response);
                var Total_data = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    Total_data += item.Total;
                }
                var thurshold = Total_data / 25;

                var dates = new Array();
                var values = new Array();
                var colors = new Array();
                var bcolors = new Array();

                var others_count = 0;
                for (index = 0; index < data.length; index++) {
                    var item = data[index];
                    if(item.Total > thurshold) {
                        dates.push(item.LabelColumn);
                        values.push(item.Total);
                        colors.push(random_rgba());
                        bcolors.push("rgba(100,100,100,1)");
                    }else{
                        others_count += item.Total;
                    }
                }
                
                dates.push("Others");
                values.push(others_count);
                colors.push(random_rgba());
                bcolors.push("rgba(100,100,100,1)");
                
                
                //console.log(colors);
                if( dates.length > 1){
                     $("#" + element ).parent().show();
                     CreatePieChart(element, dates,values,colors,bcolors);
                }
                else  
                    $("#" + element ).parent().hide();
            });
        }
        function loadGraph() {
            $(".top-chart-container .ajax-loader-container").show();
            var column1 = $("#column1").val();
            getGraphData(column1, column1);
        }
        var pieCharts = [];
        function createAllPieCharts(){
            destroyAllPieCharts();
            var column3 = $("#column3").val();
            @if(isset($other_columns))
            @foreach($other_columns as $other_column)
            getPieGraphData(column3, "{{$other_column}}", "id_{{$other_column}}");
            @endforeach
            @endif
        }
        function destroyAllPieCharts(){
            pieCharts.forEach(function (pieChart) {
                pieChart.destroy();
            });
            pieCharts = [];
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
            if (radioname == 'between')
            {
                var between = [];
                between['before'] = $('#'+col_name+'_filter_val_'+radioname+'_before-'+div_open).val();
                between['after'] = $('#'+col_name+'_filter_val_'+radioname+'_after-'+div_open).val();
                var radioButtonValue = 'last '+between['before']+' days to next '+between['after']+' days';
                var inputRadioButtonValue = '<input type="hidden" name="filter_done_column_type_val[]" id="filter_done_column_type_val_'+col_name+'_before" value="'+between['before']+'"/><input type="hidden" id="filter_done_column_type_val_'+col_name+'_after" value="'+between['after']+'"/>';
            }
            else
            {
                var radioButtonValue = $('#'+col_name+'_filter_val_'+radioname+'-'+div_open).val();
                var inputRadioButtonValue = '<input type="hidden" name="filter_done_column_type_val[]" value="'+radioButtonValue+'"/>';
            }
            if (typeof radioButtonValue === "undefined") {
                radioButtonValue ='';
                var inputRadioButtonValue = '<input type="hidden" name="filter_done_column_type_val[]" value="'+radioButtonValue+'"/>';
            }
            var a_html = '<span><i class="glyphicon glyphicon-stats"></i> '+col_name+' '+radioname+' '+radioButtonValue+' <i class="glyphicon glyphicon glyphicon-trash" onclick="delete_filter_div(\''+col_name+'\', \''+div_open+'\')"></i></span><input type="hidden" name="filter_done_column_name[]" value="'+col_name+'"/><input type="hidden" name="filter_done_column_type[]" value="'+radioname+'"/><input type="hidden" name="filter_done_column_input_type[]" value="'+coltype+'"/>'+inputRadioButtonValue;
            $('#delete_filter_'+div_open+'_'+col_name+' a:first').html(a_html);
            if(view == 'graph')
            {
                loadGraph();
                createAllPieCharts();
            }
        }
    </script>
  
  <script>
    var headerIsFixed = false;             
   $(window).scroll(function() {    
    var scroll = $(window).scrollTop();
    if(scroll >= 230) {
        if (!headerIsFixed) {
            var cloneThead = $("#userThead").clone();
            var appendThead = cloneThead[0];
            appendThead.id = 'fixed_header';
            $('.table-custom-res').append(appendThead);
        }
        headerIsFixed = true;
        $('.user-custom-dashboard').scroll(function() {   
            var scrollPos = $('.user-custom-dashboard').scrollLeft();
            $('#fixed_header').css({
                'left':-scrollPos,
            });
        });
    } 
    else {
       
        headerIsFixed = false;
        $('#fixed_header').remove();
    }
});
</script>
<script>
    $(document).ready(function(){
        $("#addBtn").click(function(){
            $(".addEntries").toggle();
        });
        $("#btnLoadGraph").click(function($){
            loadGraph();
        });
        $("#btnLoadCharts").click(function($){
            createAllPieCharts();
       });
       var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
        $('#barDate').datepicker({
            format: 'mm/dd/yyyy',
            container: container,
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function(){
            // set the "toDate" start to not be later than "fromDate" ends:
            $('#barDate1').datepicker('setStartDate', new Date($(this).val()));
        });
        $('#barDate1').datepicker({
            format: 'mm/dd/yyyy',
            container: container,
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function(){
            // set the "fromDate" end to not be later than "toDate" starts:
            $('#barDate').datepicker('setEndDate', new Date($(this).val()));
        });
        $('#pieDate').datepicker({
            format: 'mm/dd/yyyy',
            container: container,
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function(){
            // set the "fromDate" end to not be later than "toDate" starts:
            $('#pieDate1').datepicker('setStartDate', new Date($(this).val()));
        });
        $('#pieDate1').datepicker({
            format: 'mm/dd/yyyy',
            container: container,
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function(){
            // set the "fromDate" end to not be later than "toDate" starts:
            $('#pieDate').datepicker('setEndDate', new Date($(this).val()));
        });
        $('#testemail').click(function() {
            if ($(this).prop('checked'))
            {
                $('#testemailid').show();
                $('#testemailidbutton').show();
            }
            else
            {
                $('#testemailid').hide();
                $('#testemailidbutton').hide();
            }
            $('#testsmsno').hide();
            $('#testsmsnobutton').hide();
        });
        $('#testsms').click(function() {
            if ($(this).prop('checked'))
            {
                $('#testsmsno').show();
                $('#testsmsnobutton').show();
            }
            else
            {
                $('#testsmsno').hide();
                $('#testsmsnobutton').hide();
            }
            $('#testemailid').hide();
            $('#testemailidbutton').hide();
        });
    });
$(function(){
    var current = window.location.href;
    $(".edit_column_value").click(function(){
        var rowId = $(this).attr('data-value');
        $(".show_field_"+rowId).removeClass('hidden');
        $(".show_span_"+rowId).addClass('hidden');
    });
    $(".noedit_column_value").click(function(){
        var rowId = $(this).attr('data-value');
        $(".show_field_"+rowId).addClass('hidden');
        $(".show_span_"+rowId).removeClass('hidden');
    });
    $(".update-column-data").click(function(){
        var rowId = $(this).attr('data-value');
        $("#edit_column_id").val(rowId);
        $("#old_edit_column_name").val($("#edit_column_name_"+rowId).attr('data-column-name'));
        $("#edit_column_name").val($("#edit_column_name_"+rowId).val());
        $("#edit_column_type").val($("#edit_column_type_"+rowId).val());
        $("#edit_column_display").val($("#edit_column_display_"+rowId).val());
        $("#edit_column_fieldOrder").val($("#column_order_"+rowId).text().trim());


        $("#edit_column_uniqe").val(0);
        if($("#edit_column_uniqe_"+rowId).is(":checked"))
            $("#edit_column_uniqe").val(1);


            editColumnData();
    });
});
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
                    <div class="col-xs-12" style="text-align: right;">
                        <input type="hidden" id="eId"/>
                        <input type="hidden" id="tokenKey"/>
                        <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
                        <button type="button" style="width:75px;height:40px" class="btn btn-success" data-dismiss="modal" onclick="editUserData('edit')">
                            Update
                        </button>
                    </div>
                    <div class="col-xs-8">
                        <h3 style="margin-left:25px;font-size:18px;font-weight:600;margin-top:20px">Activity</h3>
                        <br>
                        <br>
                        <div id="activity_log">

                        </div>
                    </div>
                    <div class="modal-footer" style="overflow: hidden;width:750px">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->

<!-- Modal -->
<div id="column_sequence" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div style="background:rgb(237,239,240)" class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Reorder Columns</span>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form id="editColumnSequence">
                <div class="modal-body modal-lg">
                    <div class="col-xs-8" id="edit_column_body"></div>
                    <div style="width:20px" class="col-xs-2">&nbsp;</div>
                    <div style="padding-right:0px;padding-left:0px" class="col-xs-4" id="sec_edit_users_body"></div>
                    <div class="col-xs-12">
                    @if(!empty($structure) && !$isGuestAccess)
                        <table id="table-1q" class="table basic table-bordred" >
                            <thead>
                                <tr id="0">
                                    <th>Sequence</th>
                                    <th>Column Name</th>
                                    <th>Type</th>
                                    <th>Display</th>
                                    <th>Unique</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            @foreach($structure as $key => $val)
                                @php
                                    $i = $val['id'];
                                @endphp
                                <tr id="{{$val['id']}}">
                                    <td id="column_order_{{ $i }}">{{$val['ordering']}}</td>
                                    <td> 
                                        <input type="text" class="form-control column-name-field common-class-show-field show_field_{{ $i }} hidden" value="{{ $key }}" data-column-name="{{ $key }}" name="edit_column_name_{{ $i }}" id="edit_column_name_{{ $i }}" />
                                        <span class="show_span_{{ $i }}">
                                            {{ $key }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $columnTypeArray = array();
                                        @endphp
                                        <select class="form-control common-class-show-field show_field_{{ $i }} hidden" name="edit_column_type_{{ $i }}" id="edit_column_type_{{ $i }}">
                                            <option value="">Select Column</option>
                                            @foreach($columnTypes as $key => $value)
                                                <option @if($value->id==$val['column_type_id']) {{ "selected='selected'" }}  @endif class="column_options" value="{{ $value->id }}">{{ $value->column_name }}</option>
                                                @php $columnTypeArray[$value->id] = $value->column_name; @endphp
                                            @endforeach
                                        </select>
                                        <span class="show_span_{{ $i }}">
                                            {{ $columnTypeArray[$val['column_type_id']] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- <input type="checkbox" id="reorder_column_{{$val['id']}}" name="reorder_column_{{$val['id']}}" @if($val['display'] == 0) checked="checked" @endif /> --}}
                                        <select class="form-control display m-t-0 common-class-show-field show_field_{{ $i }} hidden" name="edit_column_display_{{ $i }}" id="edit_column_display_{{ $i }}">
                                            <option value="1" @if($val['display'] == 1) {{ "selected='selected'" }} @endif>Show</option>
                                            <option value="0" @if($val['display'] == 0) {{ "selected='selected'" }} @endif>Hide</option>
                                        </select>
                                        <span class="show_span_{{ $i }}">
                                                @if($val['display'] == 1) {{ "Shown" }} @else {{ "Hidden" }} @endif
                                        </span>
                                    </td>
                                    <td>
                                        <input class="common-class-show-field show_field_{{ $i }} hidden" type="checkbox"  @if($val['unique'] == 0) checked="checked" @endif name="edit_column_uniqe_{{ $i }}" id="edit_column_uniqe_{{ $i }}"/>
                                        <span class="show_span_{{ $i }}">
                                                @if($val['unique'] == 1) {{ "Yes" }} @else {{ "No" }} @endif
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-small btn-primary show_span_{{ $i }} edit_column_value" data-value="{{ $i }}"><i class="glyphicon glyphicon-pencil"></i></button>
                                        <button type="button" class="btn btn-small btn-success common-class-show-field show_field_{{ $i }} hidden update-column-data" data-value="{{ $i }}"><i class="glyphicon glyphicon-saved"></i></button>
                                        <button type="button" class="btn btn-small btn-danger common-class-show-field show_field_{{ $i }} noedit_column_value hidden" data-value="{{ $i }}"><i class="glyphicon glyphicon-remove"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    </div>
                </div>
                <div class="modal-footer" style="overflow: hidden;">
                    <div class="hidden">
                            <input type="text" id="edit_column_id" name="edit_column_id" class="form-control" />
                            <input type="text" name="old_edit_column_name" id="old_edit_column_name" class="form-control" />
                            <input type="text" id="edit_column_name" class="form-control" name="edit_column_name" />

                            <input type="text" name="edit_column_type" id="edit_column_type" class="form-control" />

                            <input type="text" name="edit_column_display" id="edit_column_display" class="form-control" />

                            <input type="text" class="form-control order order-input" name="edit_column_fieldOrder" id="edit_column_fieldOrder" />

                            <input type="text" class="value form-control" name="edit_column_uniqe" id="edit_column_uniqe" class="unique" />

                            <textarea type="text" name="edit_column_default_value" id="edit_column_default_value" placeholder="Drop down values" class="value form-control"></textarea>

                    </div>
                    <button type="button" class="btn btn-success" onclick="updateColumnSequence()">
                        Update Order
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
                        @foreach($structure as $key => $value)
                        <label style="min-width: 150px;">
                            <input type="checkbox" name="filter_columns[]" value="{{$value['id']}}" @if(isset($filtercolumns) && in_array($key, $filtercolumns)) checked="checked" @elseif(empty($filtercolumns)) checked="checked" @endif/>{{$key}}
                        </label>
                        @endforeach
                    </div>
                    </div>
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
                                                                                       value="now" checked="checked">Now</label>
                        <label onclick="timeToSend('auto')" class="radio-inline"><input type="radio" value="auto"
                                                                                        name="type" id="send_auto">Auto</label>
                    </a>
                </li>

            </ul>
            <div class="modal-body">


                <div class="option_box" id="now">Send Now option block</div>
                <div class="option_box" id="auto">Send Auto option block</div>

                <div id="setTabs" class="tab-content">
                    <div class="tab-pane active" id="email">
                        <form class="" id="emailForm">
                        @if (!empty($tableEmailApi))
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
                                <select class="form-control" id="email_column" name="email_column">
                                    @if(!empty($structure))
                                    @foreach($structure as $key => $val)
                                    @if($val['type'] == 'email')
                                    <option value="{{$key}}">{{$key}}</option>
                                    @endif
                                    @endforeach
                                    @endif
                                </select>
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
                                <button type="button" class="btn btn-primary btn-md" onclick="sendMailSMS('email')">Send
                                </button>
                                <label><input type="checkbox" id="testemail"/>Test</label>
                                <table style="width: 70%; float: right;">
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" id="testemailid" style="display: none;" placeholder="Email Id"/>
                                        </td>
                                        <td>
                                            <input type="button" class="btn btn-primary btn-md" value="Test" style="display: none;" id="testemailidbutton" onclick="sendMailSMS('email', 'test');"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @else
                        <div class="form-group">Email API key does not exist, please enter your email api key <a href="{{env('APP_URL')}}/configure/{{$tableId}}">Configure</a></div>
                        @endif
                        </form>
                    </div>
                    <div class="tab-pane" id="sms">
                        <form class="" id="smsForm">
                        @if (!empty($tableSmsApi))
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
                                <select class="form-control" id="mobile_columnn" name="mobile_columnn">
                                    @if(!empty($structure))
                                    @foreach($structure as $key => $val)
                                    @if($val['type'] == 'phone')
                                    <option value="{{$key}}">{{$key}}</option>
                                    @endif
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary btn-md" onclick="sendMailSMS('sms')" >Send
                                </button>
                                <label><input type="checkbox" id="testsms"/>Test</label>
                                <table style="width: 70%; float: right;">
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" id="testsmsno" style="display: none;" maxlength="10" placeholder="Mobile No"/>
                                        </td>
                                        <td>
                                            <input type="button" class="btn btn-primary btn-md" style="display: none;" id="testsmsnobutton" value="Test" onclick="sendMailSMS('sms', 'test');"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @else
                        <div class="form-group">SMS API key does not exist, please enter your SMS api key <a href="{{env('APP_URL')}}/configure/{{$tableId}}">Configure</a></div>
                        @endif
                        </form>
                    </div>
                    <div class="tab-pane" id="webhook">

                        <div class="form-group">
                            <label class="" for="">URL : </label>
                            <input type="" class="form-control " id="url" name="mobile_columnn">
                        </div>
                        <button type="button" class="btn btn-primary btn-md" onclick="sendMailSMS('webhook')">Send
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@stop
