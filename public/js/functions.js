var ALL_USERS = [];
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
var customTypes = ['dropdown', 'radio button', 'checkbox']

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

//var API_BASE_URL = "https://contacts-apis.herokuapp.com";
//var API_BASE_URL = "http://localhost:8000";
//var tokenKey = localStorage.getItem('token');
//var tokenKey = getUrlParameter('token');
$('body').on('focus', ".calendar_cls", function() {
    $(this).datepicker();
});

function drawUserTable(user_data) {
    var usersArr = [];
    var userDetails = '';

    user_data.forEach(function(val, index) {

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

function showFilterInputText(ths, val) {
    $(".filterinput" + val).hide();
    $(ths).parent().find("input:text").show();
}

function showDiv(id) {
    // console.log(id);
    $("#" + id).toggleClass('hide');
    $("#" + id).find("input:text").hide();
}

var globaltimeout = null;

function saveTab() {

    $('#saveTabModel').modal('show');
    var tabName = $("#saveAsInput").val();
}

function makeFilterJsonData(tableId) {
    var filterChecked = [];
    var jsonObject = {};
    var filterCheckedElement = $(".filterConditionName:checked");
    filterCheckedElement.each(function() {
        dataid = $(this).attr('dataid');
        filterChecked.push($(this).attr('dataid'));
        var radioButton = $("#condition_" + dataid + " input:checked");
        var radioname = radioButton.attr('dataid')
        var radioButtonValue = $("#" + dataid + "_filter_text_" + radioname).val();
        //console.log(dataid, radioname, radioButtonValue)
        var subDoc = {};
        subDoc[radioname] = radioButtonValue
        jsonObject[dataid] = subDoc;
    })
    applyFilterData(jsonObject, tableId);
}

function applyFilterData(jsonObject, tableId) {
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
        data: { 'filter': obj, 'tab': 'All', 'tableId': tableId }, // Some data e.g. Valid JSON as a string
        // headers: { 'token': tokenKey },
        success: function(data) {
            $('#response').html(data);
        }
    });
}

function getUserDetails(id, tableId) {
    if (id) {
        //selectedRow = index;
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + '/table/' + tableId + "/user_data/" + id, // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            //   headers: { 'token': tokenKey },
            success: function(res) {
                var val = res.data;
                var COL_FIELD = res.colDetails;
                var authKey = res.authKey;
                var teammates = res.teammates;
                var editForm = '';
                if (val) {
                    $("#eId").val(val.id);
                    $('#tokenKey').val(authKey);

                    var i = 0;
                    for (var k in val) {
                        if (k === 'id') {
                            continue;
                        }
                        var currentField = COL_FIELD[k];
                        i++;
                        if (!currentField) {
                            i = i - 1;
                        } else {
                            var cls = '';
                            if (currentField.type == 'timestamp')
                                cls = 'calendar_cls';
                            if (i % 2) {
                                // editForm += `<div class="row">`;
                                editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label>` + k + `</label>`;

                                if (currentField && currentField.column_type_id !== 6 && currentField.column_type_id !== 10) {
                                    editForm += createInputElement(val[k], k, cls, currentField);
                                } else if (currentField.column_type_id == 6) {
                                    editForm += createSelectElement(currentField, val[k], k);
                                } else if (currentField.column_type_id == 10) {
                                    currentField['value_arr']['options'] = teammates;
                                    editForm += createSelectElement(currentField, val[k], k);
                                }
                                editForm += '</div>';
                            } else {
                                editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label>` + k + `</label>`;
                                if (currentField && currentField.column_type_id !== 6 && currentField.column_type_id !== 10) {
                                    editForm += createInputElement(val[k], k, cls, currentField);
                                } else if (currentField.column_type_id == 6) {
                                    editForm += createSelectElement(currentField, val[k], k);
                                } else if (currentField.column_type_id == 10) {
                                    currentField['value_arr']['options'] = teammates;
                                    editForm += createSelectElement(currentField, val[k], k);
                                }
                                editForm += '</div></div>';
                            }
                        }
                    }
                    $("#edit_users_body").html(editForm);
                    $('#follow_up_date').attr('type', 'date');
                    // $('#label_username').removeClass('col-xs-6').addClass('col-xs-12');
                }
            }
        });
    }
    else{
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + '/tables/structure/' + tableId , // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            success: function (res) {
                
                tableStructure = res.structure;
                tableData = res.tableData;
                var authKey = tableData['auth'];
                
                $('#tokenKey').val(authKey);
                var editForm = '';
                
                for (var k in tableStructure){
                    field = tableStructure[k]
                    column_type = tableStructure[k]['column_type'];
                    
                    var cls = '';
                            
                    if (column_type.column_name == 'timestamp'){
                        cls = 'calendar_cls';
                    }
                                
                            
                                // editForm += `<div class="row">`;
                                editForm += `<div class="form-group col-xs-6" id="label_` + tableStructure[k]['column_name'] + `"  name="label_` + tableStructure[k]['column_name'] + `"  ><label>` + tableStructure[k]['column_name'] + `</label>`;

                                if (column_type.id !== 6) {
                                    editForm += createInputElement('', tableStructure[k]['column_name'], cls, field);
                                } else {
                                    editForm += createSelectElement('', tableStructure[k]['column_name'], k);
                                }
                                editForm += '</div>';
                }
                $("#add_users_body").html(editForm);
                $('#follow_up_date').attr('type', 'date');
                $('#label_username').removeClass('col-xs-6').addClass('col-xs-12');
                return false;
                
            }
        });
    }
}

function editUserData(type) {
    clearInterval(myInterval);
    id = $("#eId").val();
    var authKey = $("#tokenKey").val();
    var obj;
    var jsonDoc = {};
    var fieldChange = false;
    if(type == 'edit'){
        var editUserDetailsForm = $("#editUserDetails .form-control")
        jsonDoc['edit_url_callback'] = true;
    }
    else{
    var editUserDetailsForm = $("#addUserDetails .form-control")
    }
    jsonDoc['socket_data_source'] = '';
    editUserDetailsForm.each(function() {
        fieldChange = $(this).attr('data-change');
        //if (fieldChange) {
        dataid = $(this).attr('dataid');
        val = $(this).val();
        if (dataid == 'follow_up_date') {
            if (!val) {
                var date = new Date();
                val = date.toLocaleDateString();
                val = val.split('/');
                val = val[2] + "/" + val[1] + "/" + val[0];
                //console.log(val);
            }
        }
        jsonDoc[dataid] = val;
        //}
    });
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    jsonDoc['_token'] = CSRF_TOKEN;
    obj = jsonDoc;
    $.ajax({
        type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
        dataType: 'json', // Set datatype - affects Accept header
        url: API_BASE_URL + "/add_update", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
        data: jsonDoc, // Some data e.g. Valid JSON as a string

        beforeSend: function(xhr) {
            xhr.setRequestHeader('Auth-Key', authKey);
        },
        success: function(data) {
            ALL_USERS[selectedRow] = data.data;
            console.log(data)

            //startInterval();
        },
        // contentType: "application/json"
    });
}

function initFilterSlider() {
    //open the lateral panel
    $('.cd-btn').on('click', function(event) {
        event.preventDefault();
        $('.cd-panel').addClass('is-visible');
    });
    //clode the lateral panel
    $('.cd-panel').on('click', function(event) {
        if ($(event.target).is('.cd-panel') || $(event.target).is('.cd-panel-close')) {
            $('.cd-panel').removeClass('is-visible');
            event.preventDefault();
        }
    });
}

function watchOnchange(ele) {
    $(ele).attr("data-change", "true");
}

function startInterval() {
    myInterval = setInterval(function() {
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
        return false;
    }
    $.get(API_BASE_URL + "/search/" + tableId + "/" + q, function(response) {
        $('#response').html(response);

    });
}

function getOptionList() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: API_BASE_URL + "/getOptionList",
        success: function(response) {
            optionList = response;
            setTimeout(function() {
                addRow(true);
                addMoreRow(true);
            }, 300);
        }
    });
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

};

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
};


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
    $.each(checkList, function(idx, val) {
        html += `<div class="checkbox">
            <label>
            <input type="checkbox" class="" onclick="showDiv('` + val.name + `')">` + val.name + `</label>
        </div>
        <div id="` + val.name + `" class="hide more-option">`;
        $.each(val.priority, function(indx) {
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
    for (i = 0; i <= arrList.length - 1; i++) {
        if(arr.column_type_id != 10) {
            if (arrList[i] == selected) {
                lists += `<option value="` + arrList[i] + `" selected>` + arrList[i] + `</option>`
            } else {
                lists += `<option value="` + arrList[i] + `">` + arrList[i] + `</option>`
            }
        }else  {
            if (arrList[i]['email'] == selected) {
                lists += `<option value="` + arrList[i]['email'] + `" selected>` + arrList[i]['name'] + `</option>`
            } else {
                lists += `<option value="` + arrList[i]['email'] + `">` + arrList[i]['name'] + `</option>`
            }
        }

    }
    var formGrp = `<select class="form-control" id="` + k + `" dataid="` + k + `" name="` + k + `">` + lists + ` </select>`;
    return formGrp;
};


// radioType behavior on checkbox
function radioBehavior() {
    $(".chkbx").change(function() {
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

$(document).ready(function() {
    getOptionList();
    $('#right_panel').hide();
    var title = $('#right_panel .title');
});
