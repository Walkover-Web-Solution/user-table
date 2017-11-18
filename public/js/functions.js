var ALL_USERS;
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

        // console.log(userDetails);
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
    applyFilterData(jsonObject,tableId);
}

function applyFilterData(jsonObject,tableId) {
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
        data: { 'filter': obj, 'tab': 'All' ,'tableId' : tableId}, // Some data e.g. Valid JSON as a string
        // headers: { 'token': tokenKey },
        success: function(data) {
            $('#response').html(data);
        }
    });
}

function getUserDetails(id) {
    //console.log(results[index]);
    if (id) {
        //selectedRow = index;
        $.ajax({
            type: 'GET', // Use GET
            url: API_BASE_URL + "/user_data/" + id, // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
            //   headers: { 'token': tokenKey },
            success: function(res) {
                var val = res.data;
                var COL_FIELD = res.colDetails;
                var editForm = '';
                if (val) {
                    $("#eId").val(val.username);

                    var i = 0;
                    for (var k in val) {
                        //console.log(k);
                        if (k === 'id') { continue; }
                        var currentField = COL_FIELD[k];
                        i++;
                        if (!currentField) {
                            i = i - 1;
                        } else {
                            var cls = '';
                            if (currentField.type == 'timestamp')
                                cls = 'calendar_cls';
                            if (i % 2) {
                                editForm += `<div class="row">`;
                                editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label>` + k + `</label>`;
                                if (currentField && currentField.type !== 'enum') {
                                    editForm += createInputElement(val[k], k, cls);
                                } else {
                                    editForm += createSelectElement(currentField, val[k], k);
                                }
                                editForm += '</div>';
                            } else {
                                editForm += `<div class="form-group col-xs-6" id="label_` + k + `"  name="label_` + k + `"  ><label>` + k + `</label>`;
                                if (currentField && currentField.type !== 'enum') {
                                    editForm += createInputElement(val[k], k, cls);
                                } else {
                                    editForm += createSelectElement(currentField, val[k], k);
                                }
                                editForm += '</div></div>';
                            }
                        }
                    }
                    $("#edit_users_body").html(editForm);
                    $('#follow_up_date').attr('type', 'date');
                    $('#label_username').removeClass('col-xs-6').addClass('col-xs-12');
                }
            }
        });
    }
}

function editUserData() {
    clearInterval(myInterval);
    // console.log('stop interval')
    id = $("#eId").val();
    var obj;
    var jsonDoc = {};
    var fieldChange = false;
    //console.log('in edit user details')
    var editUserDetailsForm = $("#editUserDetails .form-control")
    jsonDoc['username'] = id;
    editUserDetailsForm.each(function() {
            fieldChange = $(this).attr('data-change');
            if (fieldChange) {
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
                // console.log(dataid,val)
                jsonDoc[dataid] = val;
            }
        })
        //console.log(jsonDoc);
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    obj = jsonDoc;
    $.ajax({
        type: 'POST', // Use POST with X-HTTP-Method-Override or a straight PUT if appropriate.
        dataType: 'json', // Set datatype - affects Accept header
        url: API_BASE_URL + "/add_update", // A valid URL // headers: {"X-HTTP-Method-Override": "PUT"}, // X-HTTP-Method-Override set to PUT.
        data: { _token: CSRF_TOKEN, userdata: obj }, // Some data e.g. Valid JSON as a string
        success: function(data) {
            //console.log("success");
            ALL_USERS[selectedRow] = data.data;
            console.log(data)

            //startInterval();
            console.log('start interval');
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
       // console.log(activeTab)
        //getTabDetails(activeTab)
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
    $.get(API_BASE_URL + "/search/" + activeTab + "/" + q, function(response) {
        $('#response').html(response);

    })

}

// add new field row
function addRow(check) {
    
    var obj = {        
        name: '',
        type:'',
        unique:'',
        value: '',
    }
    var lists = '';
    var tableData = [];
    lists += '<option value="">Select Field Type</option>';
    for (i = 0; i <= optionList.length - 1; i++) {
        lists += '<option value="' + optionList[i] + '">' + optionList[i] + '</option>'
    }

    var formGrp = `<div class="row" id="column_"`+i+`>
            <div class="form-group col-xs-3">
                <input type="text" placeholder="Enter Field Name" class="form-control name" name="fieldName" value="">
            </div>
            <div class="form-group col-xs-3">
                <select class="form-control type" >` + lists + ` </select>
            </div>
            <div class="form-group col-xs-3">
                <label><input type="radio" name="uniqe" class="unique"> Uniqe</label>
            </div>
            <div class="form-group col-xs-3">
                <textarea type="text" name="" placeholder="Default value" class="value"></textarea>
            </div>
        </div>`;
    formGrp += '';
    tableData.push(obj);
    return $('#tableField').append(formGrp);
    
};
function addMoreRow(check) {
    
    var obj1 = {        
        name: '',
        type:'',
        unique:'',
        value: '',
    }
    var lists = '';
    var tableData = [];
    var tableData1 = [];
    lists += '<option value="">Select Field Type</option>';
    for (i = 0; i <= optionList.length - 1; i++) {
        lists += '<option value="' + optionList[i] + '">' + optionList[i] + '</option>'
    }

    var formGrp = `<div class="row" id="column_"`+i+`>
            <div class="form-group col-xs-3">
                <input type="text" placeholder="Enter Field Name" class="form-control name" name="fieldName" value="">
            </div>
            <div class="form-group col-xs-3">
                <select class="form-control type" >` + lists + ` </select>
            </div>
            <div class="form-group col-xs-3">
                <textarea type="text" name="" placeholder="Default value" class="value"></textarea>
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
function createSelectElement(arr) {
    var arrList = arr;
    $('.title').text('Choose One Option');
    
    var lists = '';
    for (i = 0; i <= arrList.length - 1; i++) {
        lists += `<option value="">` + arrList[i] + `</option>`
    }
    var formGrp = `<select class="form-control">` + lists + ` </select>`;
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
    addRow(true);
    addMoreRow(true);
    $('#right_panel').hide();
    var title = $('#right_panel .title');
});

