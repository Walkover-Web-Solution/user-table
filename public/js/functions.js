var results = [];
var ALL_FIELDS = [];
var activeTab = "All";
var COL_FIELD = {};
var selectedRow = '';
var myInterval;
var interval = 3000; // after every 3s
var optionList = ['text', 'phone', 'any number', 'airthmatic number', 'email', 'dropdown', 'radio button', 'checkbox', 'date'];
var checkList = [{
        name: 'Unique',
        priority: ['high', 'medium', 'low']
    }];
var numberList = [];
var dateList = ['relative', 'normal'];
var customTypes = ['dropdown', 'radio button', 'checkbox'];
var time = "";
var img = '';
var teamMateList = {};

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

$('body').on('focus', ".calendar_cls", function () {
    $(this).datepicker();
});

function drawUserTable(user_data) {
    var usersArr = [];
    var userDetails = '';
    user_data.forEach(function (val, index) {

        userDetails += `<tr data-toggle="modal" data-target="#edit_user" onclick = "getUserDetails('` + index + `')" >
        <td></td>`

        for (var k in val) {
            userDetails += `<td>` + val[k] + `</td>`
        }

        userDetails += `</tr>`

        usersArr.push(userDetails);
        userDetails = '';

    });
    return usersArr;
}

function showFilterInputText(ths, val, tableId) {
    $(".filterinput" + val).hide();
    dataid = $(ths).attr('dataid');
    if (dataid != "has_any_value" && dataid != 'is_unknown') {
        $(ths).parent().find("input:text").show();
        $(ths).parent().find("input.date-filter-input").show();
        $(ths).parent().find("select").show();
        if (dataid == 'between')
            $('.table-between').attr('style', 'display:block');
    }
}

function showDiv(id) {
    $("#" + id).toggleClass('hide');
    $("#" + id).find("input:text").hide();
    $("#" + id).find("input.date-filter-input").hide();
    $(ths).parent().find("select").hide();
}

var globaltimeout = null;

function saveTab() {
    $('#saveTabModel').modal('show');
    $('#replacetabName').html("'"+activeTab+"'")
    var tabName = $("#saveAsInput").val();
}

function makeFilterJsonData(tableId, type,column_name, div_open) {
    var filterChecked = [];
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
    if($("#delete_filter_"+div_open+"_"+column_name).length){
        var radio_type = $("#delete_filter_"+div_open+"_"+column_name+" input[type='radio']:checked");
        var radioname = radio_type.attr('dataid');
        var coltype = radio_type.attr('datacoltype');
        if (radioname == 'between')
        {
            var between = {};
            between['before'] = $('#'+column_name+'_filter_val_'+radioname+'_before-'+div_open).val();
            between['after'] = $('#'+column_name+'_filter_val_'+radioname+'_after-'+div_open).val();
            var radioButtonValue = between;
        }
        else
        {
            var radioButtonValue = $('#'+column_name+'_filter_val_'+radioname+'-'+div_open).val();
        }
        
        var dataid = column_name;

        if (radioname == "has_any_value" || radioname == 'is_unknown') {
            radioButtonValue = "1";
        }

        var subDoc = {};
        subDoc[radioname] = radioButtonValue;
        var subjsonObject = {};
        subjsonObject[dataid] = subDoc;
        jsonObject[div_open] = subjsonObject;
        var subcoltypeObject = {};
        subcoltypeObject[dataid] = coltype;
        coltypeObject[div_open] = subcoltypeObject;
    }
    var condition = $('#filter_condition').val();
    
    if (type == "returnData") {
        var returnData = [];
        returnData.push(coltypeObject);
        returnData.push(jsonObject);
        return returnData;
    }
    
    applyFilterData(jsonObject, tableId, coltypeObject, condition);
}

function changeFilterJsonData(tableId, type) {
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
    
    if (type == "returnData") {
        return jsonObject;
    }
    applyFilterData(jsonObject, tableId, coltypeObject, condition);
}

function applyFilterData(jsonObject, tableId, coltypeObject, condition) {
    id = $("#eId").val();
    clearInterval(myInterval);
    var obj;
    obj = jsonObject;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
        url: API_BASE_URL + "/filter", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.

        data: {'filter': obj, 'tab': 'All', 'tableId': tableId, 'coltype': coltypeObject, 'condition' : condition}, // Some data e.g. Valid JSON as a string
        beforeSend: function() {
            $('body').addClass('loader');
        },
        success: function (data) {
            $('#def_response').html(data);
            $('#show_count').html('<span class="sp_view_count">'+totalCount+' users match<span><span class="total_count">of '+allTabCount+'</span>');
        },
        complete: function() {
            $('body').removeClass('loader');
        }
    });
    if(view == 'graph')
    {
        loadGraph();
        createAllPieCharts();
    }
}

var inputTypeArr = ['text', 'text', 'tel', 'number', 'number', 'email', 'select', 'radio', 'checkbox', 'date', 'select', 'textarea'];

function getUserDetails(event, id, tableId, mod_head_edit = false) {
    if (event.target.className == "row-delete")
        return;
    if (id) {
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + '/table/' + tableId + "/user_data/" + id, // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            success: function (res) {
                var val = res.data;
                var COL_FIELD = res.colDetails;
                var authKey = res.authKey;
                var teammates = teamMateList;
                var editForm = '';
                var edituniqueForm = '';
                var form_text = '';
                var sec_editForm = '';
                if (val) {
                    $("#eId").val(val.id);
                    $('#tokenKey').val(authKey);

                    var idElem = {
                        column_name: "id",
                        column_type_id: 0,
                        unique: 1,
                        default_value: {options: [""]},
                        column_type: {id: 1, column_name: "id"}
                    }
                    COL_FIELD.id = idElem;

                    var i = 0;
                    for (var k in val) {
                        var currentField = COL_FIELD[k];
                        i++;
                        if (k === 'id') {
                            editForm += `<div hidden class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                            editForm += createHiddenElement(val[k], k);
                            editForm += '</div></div>';
                        } else {

                            if (currentField.column_type_id !== 6 && currentField.column_type_id !== 8 && currentField.column_type_id !== 10 && currentField.column_type_id !== 9 && currentField.unique !== 1) {
                                if (currentField.column_type_id === 3) {
                                    editForm += `<div class="form-group col-xs-6" maxlength="14" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                                    editForm += createInputElement(val[k], k, currentField, inputTypeArr[currentField.column_type_id]);
                                } else {
                                    if (currentField.column_type_id === 11) {
                                        form_text += `<div class="form-group col-xs-12" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                                        form_text += createInputElement(val[k], k, currentField, inputTypeArr[currentField.column_type_id]);
                                    } else {
                                        editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                                        editForm += createInputElement(val[k], k, currentField, inputTypeArr[currentField.column_type_id]);
                                    }
                                }
                            } else {
                                if (currentField.column_type_id == 9 && currentField.unique !== 1) { // if column type is date
                                    sec_editForm += `<div class="form-group col-xs-12" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                                    var currentVal = parseDate(val[k]);
                                    sec_editForm += createInputElement(currentVal, k, currentField, inputTypeArr[currentField.column_type_id]);
                                } else if ((currentField.column_type_id == 6 || currentField.column_type_id == 8) && currentField.unique !== 1) { // if column type is dropdown
                                    sec_editForm += `<div class="form-group col-xs-12" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:10px;color:#292929">` + k + `</label>`;
                                    sec_editForm += createSelectElement(currentField, val[k], k, inputTypeArr[currentField.column_type_id]);
                                } else if (currentField.column_type_id == 10 && currentField.unique !== 1) { // if column type is teammates
                                    sec_editForm += `<div class="form-group col-xs-12" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:10px;color:#292929">` + k + `</label>`;
                                    currentField['value_arr']['options'] = teammates;
                                    sec_editForm += createSelectElement(currentField, val[k], k, inputTypeArr[currentField.column_type_id]);
                                } else if (currentField.unique === 1) {
                                    edituniqueForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label style="font-weight:600;margin-bottom:5px;color:#292929">` + k + `</label>`;
                                    edituniqueForm += createInputElement(val[k], k, currentField, inputTypeArr[currentField.column_type_id]);
                                }
                            }
                            form_text += '</div>';
                            editForm += '</div>';
                            edituniqueForm += '</div>';
                            sec_editForm += '</div>';
                        }
                    }
                    // editForm += '<textarea class="form-group col-xs-12 custom-input" col="100" row="5"></textarea>';
                    editForm += form_text;
                    $("#modal_header_column").html(edituniqueForm+'<button type="button" class="close" data-dismiss="modal">×</button>');
                    $("#edit_users_body").html(editForm);
                    $("#sec_edit_users_body").html(sec_editForm);
                    $('#is_edit').val(1);
                    $('#follow_up_date').attr('type', 'date');
                    //$('#edit_user').modal('show');
                }
            }
        });
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + '/table/' + tableId + '/activity_data/' + id, // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            success: function (resData) {
                var logs = resData.data.data;
                var desc = '';
                $("#activity_log").html('');
                var logsLength = logs.length;
                function getImg(user_id, log,logTime , logData) {
                    $.get('https://picasaweb.google.com/data/entry/api/user/' + user_id + '?alt=json', function (result) {
                        img = result.entry.gphoto$thumbnail.$t;
                        var name = result.entry.gphoto$nickname.$t;
                        if (!name) {
                            name = user_id;
                        }
                        // desc = '<h3 style="font-weight:700;margin-left:25px">' + name + '('+user_id+')'+'</h3><img style="height:30px;width:30px;border-radius:25em;float:left;margin-left:-18px;margin-right:10px" src="' + img + '"><p style="margin-left:25px;width:450px">' + log + '</p><span>' + logTime + '</span><br><br>';

                        desc = '<h3 style="font-weight:700;margin-left:25px">' + name + '('+user_id+')'+'</h3><img style="height:30px;width:30px;border-radius:25em;float:left;margin-left:-18px;margin-right:10px" src="' + img + '"><p style="margin-left:25px;width:450px">' + log + '<br /><br /><span>' + logTime + '</span></p><br><br>';

                        $("#activity_log").append(desc);
                    }).fail(function () {
                        desc = '<h3 style="font-weight:700;margin-left:25px">' + logData.userName + '</h3><img style="height:30px;width:30px;border-radius:25em;float:left;margin-left:-18px;margin-right:10px" src="'+API_BASE_URL+'/img/user_img.jpg"><p style="margin-left:25px;width:450px">' + log + '<br /><br /><span>' + logTime + '</span></p><br><br>';
                        $("#activity_log").append(desc);
                    });
                }
                if (logsLength > 0) {
                    for(var i in logs){
//                        if (logs[i].action == 'Update') {
//                            var logTime = logs[i].updated_at;
//                        } else {
//                            var logTime = logs[i].created_at;
//                        }
                        // console.log(logs[i]);
                        var logTime = logs[i].activityDate;
                        if(logs[i].user_id != '')
                        {
                            getImg(logs[i].user_id,logs[i].log, logTime , logs[i]);
                        }
                        else
                        {
                            desc = '<h3 style="font-weight:700;margin-left:25px"> '+logs[i].user_agent+' </h3><img style="height:30px;width:30px;border-radius:25em;float:left;margin-left:-18px;margin-right:10px" src="'+API_BASE_URL+'/img/api.svg"><p style="margin-left:25px;width:450px">' + logs[i].log + '</p><span>' + logTime + '</span><br><br>';
                            $("#activity_log").append(desc);
                        }
                    }
                }
            }
        });
    } else {
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + '/tables/structure/' + tableId, // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            success: function (res) {
                var COL_FIELD = res.colDetails;
                var tableData = res.tableData;
                var teammates = teamMateList
                var authKey = tableData['auth'];

                $('#tokenKey').val(authKey);
                var editForm = '';

                for (var k in COL_FIELD) {
                    var currentField = COL_FIELD[k];
                    var label = k;
                    if (currentField.unique === 1) {
                        label = label + '*';
                    }
                    editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label>` + label + `</label>`;
                    if (currentField.column_type_id == 9) { // if column type is date
                        editForm += createInputElement('', k, currentField, inputTypeArr[currentField.column_type_id]);
                    } else if (currentField.column_type_id == 6) { // if column type is dropdown
                        editForm += createSelectElement(currentField, '', k, inputTypeArr[currentField.column_type_id]);
                    } else if (currentField.column_type_id == 10) { // if column type is teammates
                        currentField['value_arr']['options'] = teammates;
                        editForm += createSelectElement(currentField, '', k, inputTypeArr[currentField.column_type_id]);
                    } else if (currentField.column_type_id == 8) {
                        editForm += createSelectElement(currentField, '', k, inputTypeArr[currentField.column_type_id]);
                    } else {
                        editForm += createInputElement('', k, currentField, inputTypeArr[currentField.column_type_id]);
                    }
                    editForm += '</div></div>';
                }
                $("#edit_users_body").html(editForm);
                $('#is_edit').val(0);
                $('#follow_up_date').attr('type', 'date');
                return false;
            }
        });
    }
    
    if(mod_head_edit == 'Add')
        $('#mod-head_edit').html('Add Column Value');
}

function parseDate(unixDateTime) {
    if (unixDateTime == 0 || unixDateTime == null)
        return "";
    else
        var selectedDate = new Date(unixDateTime * 1000);
    var date = selectedDate.getUTCDate();
    var month = selectedDate.getUTCMonth() + 1;
    var year = selectedDate.getUTCFullYear();
    var hours = selectedDate.getUTCHours();
    var minutes = selectedDate.getUTCMinutes();
    var seconds = selectedDate.getUTCSeconds();
    if (hours < 10)
        hours = "0" + hours;
    if (minutes < 10)
        minutes = "0" + minutes;
    if (seconds < 10)
        seconds = "0" + seconds;
    if (date < 10)
        date = "0" + date;
    if (month < 10)
        month = "0" + month;
    time = hours + ":" + minutes + ":" + seconds;
    var currentVal = year + "-" + month + "-" + date;
    return currentVal
}

function editUserData(type) {
    clearInterval(myInterval);
    id = $("#eId").val();
    var authKey = $("#tokenKey").val();
    var obj;
    var jsonDoc = {};
    var fieldChange = false;
    var is_valid = true;
    var is_edit = $('#is_edit').val();
    if (type == 'edit') {
        var userDetailsForm = $("#editUserDetails .form-control")
        jsonDoc['edit_url_callback'] = true;
    } else {
        var userDetailsForm = $("#addUserDetails .form-control")
    }
    jsonDoc['data_source'] = 'manual';

    userDetailsForm.each(function () {
        fieldChange = $(this).attr('data-change');
        //if (fieldChange) {
        dataid = $(this).attr('dataid');
        required = $(this).attr('required');
        val = $(this).val();
        type = $(this).attr('type');
        if (required && val === "") {
            errMsg = '<div class="invalid_msg col-xs-12">Required ' + dataid + '</div>';
            $('#add_users_body').append(errMsg);
            is_valid = false;
        }

        if (type === "date") {
            val = val;
        }
        jsonDoc[dataid] = val;
    });
    
    if (is_valid) {
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
                
                if (!data.teamData.data) {
                    alert("something went wrong in updating data");
                } else {
                    if (is_edit == 1) {
                        $.each(data.teamData.data, function (idx, val) {
                            if ($("#tr_" + id + " ." + idx).attr('id') === 'dt_9') {
                                var date = new Date(val * 1000);
                                var localDate = date.toLocaleDateString();
                                $("#tr_" + id + " ." + idx).text(localDate);
                            } else {
                                $("#tr_" + id + " ." + idx).text(val);
                            }
                        });
                    }else{
                        // alert("Entry added successfully.");
                        $.toast({
                            heading: 'Success',
                            text: 'Entry added successfully.',
                            showHideTransition: 'slide',
                            icon: 'success',
                            afterHidden: function() {
                        location.reload();
                    }
                        });
                }
                }
            },
        });
    }
}

function watchOnchange(ele) {
    $(ele).attr("data-change", "true");
}

function startInterval() {
    myInterval = setInterval(function () {
    }, interval);
}

//startInterval();

function hideHeader() {
    var scrollPos = $('.scroll-x').scrollTop();
    if (scrollPos > 0) {
        $('.main-header').css('margin-top', '-52px');
    } else {
        $('.main-header').css('margin-top', '0');
    }
}

function addSearchBar(val) {
    return val = `<form class="search-form pull-right" action="" name="queryForm" class="navbar-form navbar-left" onSubmit="searchKeyword(event, query.value)">
        <label for="searchInput"><i class="fa fa-search"></i></label>
        <input type="text" name="query" class="form-control" placeholder="Search for..." aria-label="Search for..." id="searchInput">
        </form>`
}

function searchKeyword(event, query) {
    event.preventDefault();
    clearInterval(myInterval);
    var q = query || '';
    if (!q) {
        $("#def_response").show();
        $('#response').hide();
    } else {
        $.get(API_BASE_URL + "/search/" + tableId + "/" + q, function (response) {
            $("#def_response").hide();
            $('#response').html(response);
        });
    }

}

function getOptionList() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: API_BASE_URL + "/getOptionList",
        success: function (response) {
            optionList = response;
            setTimeout(function () {
                addRow(true);
                addMoreRow(true);
            }, 300);
        }
    });
}

function getTeamMates() {
    if (tableId) {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: API_BASE_URL + "/getTeamMateList/" + tableId,
            success: function (response) {
                teamMateList = response.data;
            }
        });
    }
}

// add new field row
function addRow(check) {

    var obj = {
        name: '',
        type: '',
        unique: '',
        value: '',
    }
    var lists = '';
    var tableData = [];
    lists += '<option value="">Select Field Type</option>';
    for (i = 0; i <= optionList.length - 1; i++) {
        lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
    }

    var formGrp = `<div class="row" id="column_"` + i + `>
            <div class="form-group col-xs-2">
                <input type="text" placeholder="Enter Field Name" class="form-control name" name="fieldName" value="">
            </div>
            <div class="form-group col-xs-2">
                <select class="form-control type" >` + lists + ` </select>
            </div>
            <div class="form-group col-xs-2">
                <select class="form-control display"><option value="1">Show</option><option value="0">Hide</option></select>
            </div>
            <div class="form-group col-xs-1">
                <input type="text" class="form-control order order-input" name="fieldOrder" placeholder="">
            </div>
            <div class="form-group col-xs-3">
                <textarea type="text" name="" placeholder="Default value" class="value form-control"></textarea>
            </div>
            <div class="form-group col-xs-1">
                <label><input type="radio" name="uniqe" class="unique"> Uniqe</label>
            </div>
            <div class="form-group col-xs-1">
                <a href="javascript:void(0)" class="remove-row"><i class="glyphicon glyphicon-trash"></i></a>
            </div>
        </div>`;
    formGrp += '';
    tableData.push(obj);
    return $('#tableField').append(formGrp);

}
;

function addMoreRow(check) {

    var obj1 = {
        name: '',
        type: '',
        unique: '',
        value: '',
    }
    var lists = '';
    var tableData = [];
    var tableData1 = [];
    lists += '<option value="">Select Field Type</option>';
    for (i = 0; i <= optionList.length - 1; i++) {
        lists += '<option value="' + optionList[i].id + '">' + optionList[i].column_name + '</option>'
    }

    var formGrp = `<div class="row" id="column_"` + i + `>
            <div class="form-group col-xs-2">
                <input type="text" placeholder="Enter Field Name" class="form-control name" name="fieldName" value="">
            </div>
            <div class="form-group col-xs-2">
                <select class="form-control type" >` + lists + ` </select>
            </div>
            <div class="form-group col-xs-2">
                <select class="form-control display"><option value="1">Show</option><option value="0">Hide</option></select>
            </div>
            <div class="form-group col-xs-1">
                <input type="text" class="form-control order order-input" name="fieldOrder" placeholder="">
            </div>
            <div class="form-group col-xs-3">
                <textarea type="text" name="" placeholder="Default value" class="value form-control"></textarea>
            </div>
            <div class="form-group col-xs-1">
                <label><input type="radio" name="uniqe" class="unique"> Uniqe</label>
            </div>
            <div class="form-group col-xs-1">
                <a href="javascript:void(0)" class="remove-row"><i class="glyphicon glyphicon-trash"></i></a>
            </div>
        </div>`;
    formGrp += '';
    tableData1.push(obj1);
    return $('#tableFieldRow').append(formGrp);
}
;


// on select field type
function onSelectOption(val) {
    $('#right_panel').show();
    switch (val) {
        case 'text':
            $('#additional_option').html(onTypeText());
            break;
        case 'number':
            $('#additional_option').html(createSelectElement(numberList));
            break;
        case 'email':
            $('#right_panel').hide();
            break;
        case 'date':
            $('#additional_option').html(createSelectElement(dateList));
            break;
        case 'fixed':
            $('#additional_option').html(createCustomOption());
            break;
        default:
            $('#right_panel').hide();
    }
}


// on type text select
function onTypeText() {
    $('.title').text('Choose One Option');
    var html = '';
    $.each(checkList, function (idx, val) {
        html += `<div class="checkbox">
            <label>
            <input type="checkbox" class="" onclick="showDiv('` + val.name + `')">` + val.name + `</label>
        </div>
        <div id="` + val.name + `" class="hide more-option">`;
        $.each(val.priority, function (indx) {
            html += `<div class="form-check">
                <label class="form-check-label radio-label">
                    <input class="" name="priority" type="radio"> ` + val.priority[indx] + `
                </label>
            </div>`;
        })
        html += `</div>`
    });
    return html;
}

//  create select option
function createSelectElement(arr, selected, k) {
    var arrList = arr['value_arr']['options'];
    $('.title').text('Choose One Option');

    var lists = '';
    lists += `<option value="">select</option>`;
    for (i = 0; i <= arrList.length - 1; i++) {
        if (arr.column_type_id != 10) {
            if (arrList[i] == selected) {
                lists += `<option value="` + arrList[i] + `" selected>` + arrList[i] + `</option>`
            } else {
                lists += `<option value="` + arrList[i] + `">` + arrList[i] + `</option>`
            }
        } else {
            if (arrList[i]['email'] == selected) {
                lists += `<option value="` + arrList[i]['email'] + `" selected>` + arrList[i]['name'] + `</option>`
            } else {
                lists += `<option value="` + arrList[i]['email'] + `">` + arrList[i]['name'] + `</option>`
            }
        }

    }
    if (arr.column_type_id == 8) {
        var formGrp = `<select multiple="multiple" class="form-control" id="` + k + `" dataid="` + k + `" name="` + k + `">` + lists + ` </select>`;
    } else {
        if (arr.column_type_id == 6)
            lists += `<option value="Other">Other</option>`;

        var formGrp = `<select class="form-control" id="` + k + `" dataid="` + k + `" name="` + k + `" onchange="addOtherOption(this)">` + lists + ` </select>`;
    }
    return formGrp;
}
;

//  create select option
function addOtherOption(element) {
    if (element.value == "Other")
    {
        $("#" + $(element).parent().attr("id")).after("<div class=\"form-group col-xs-12\" id=\"label_other_" + element.id + "\" name=\"label_other_" + element.id + "\"><label style=\"font-weight:600;margin-bottom:10px;color:#292929\">Other " + element.id + "</label><input type='text' id=\"other_" + element.id + "\" name=\"other_" + element.id + "\" class=\"form-control\" style=\"width: 160px;float: left;\"/><a id=\"add_other_" + element.id + "\" class=\"btn btn-success\" style=\"float: right;\" onclick=\"addDropDown('" + element.id + "');\">Add</a></div>");
    } else
    {
        $("#label_other_" + element.id).remove();
    }
}
function addDropDown(id) {
    var elementId = "other_" + id;
    var newValue = $('#' + elementId).val();
    $('#' + id).append('<option value="' + newValue + '">' + newValue + '</option>');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: API_BASE_URL + "/addDropDown",
        data: {'name': id, 'value': newValue, 'tableId': tableId}, // Some data e.g. Valid JSON as a string
        // headers: { 'token': tokenKey },
        success: function (data) {
            if (data == 1) {
                $("#" + id + " option[value='" + newValue + "']").attr("selected", "selected");
                $("#label_other_" + id).remove();
            }
        }
    });
}

// radioType behavior on checkbox
function radioBehavior() {
    $(".chkbx").change(function () {
        $(".chkbx").prop('checked', false);
        $(this).prop('checked', true);
    });
}


// create custom select option
function createCustomOption() {
    $('.title').text('Add Option per line');
    var html = `<textarea class="form-control"></textarea>`;
    html += '<h4 style="margin:20px 0;font-size:18px;">How to Show?</h4>'
    for (i = 0; i <= customTypes.length - 1; i++) {
        html += `<div class="checkbox"><label>`;
        html += `<input type="checkbox" class="chkbx" onChange="radioBehavior()" />` + customTypes[i];
        html += `</label></div>`;
    }
    return html;
}

function showDiv(id) {
    $("#" + id).toggleClass('hide');
}

$(document).ready(function () {
    getOptionList();
    getTeamMates();
    $('#right_panel').hide();
    var title = $('#right_panel .title');
    $(".option_box").addClass('hide');
});
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
        placement: 'top',
        trigger: 'hover'
    });

    $('#edit_user').on('hidden.bs.modal', function () {
        $("#edit_users_body").html('');
        $("#sec_edit_users_body").html('');
        $("#activity_log").html('');
        $("#mod-head").text('');
    })
});

function sendData(type, JsonData, formData, tableId, condition, coltype, test = false, activeTab) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
        url: API_BASE_URL + "/sendEmailSMS", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
        data: {'filter': JsonData, 'type': type, 'formData': formData, 'tableId': tableId, 'condition' : condition, 'coltype' : coltype, 'activeTab' : activeTab}, // Some data e.g. Valid JSON as a string
        // headers: { 'token': tokenKey },
        success: function (data) {
            if(data.status == 'error')
            {
                $.toast({
                    heading: 'Error',
                    text: data.message,
                    showHideTransition: 'slide',
                    icon: 'error'
                });
            }
            else
            {
                $.toast({
                    heading: 'Success',
                    text: data.message,
                    showHideTransition: 'slide',
                    icon: 'success'
                });
            }
            if (test === false)
                $('#send_popup').modal('hide');
        }
    });
}
