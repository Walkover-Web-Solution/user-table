function filterTmpl(data) {
    console.log(data);
    var html = '<form id="filterForm" >';
    $.each(data, function (key, val) {
        html += `<li class="active">
        <div class="form-check">
            <label class="form-check-label" >
            <input type="checkbox" class="filterConditionName" dataid="` + key + `"  onclick="showDiv('condition_` + key + `')" aria-label="...">` + key + `</label>
        </div>
        <div id="condition_` + key + `" class="hide filter-option">`;
        $.each(val, function (key1) {
            html += `<div class="form-check">
                <label class="form-check-label radio-label">
                    <input class="form-check-radio" name="` + key + `_filter" dataid="` + key1 + `" onclick="showFilterInputText(this,'` + key + `')" type="radio" aria-label="...">` + key1 + `
                    <input class="form-check-input filterinput` + key + ` form-control" name="` + key + `_filter_text" id="` + key + `_filter_text_` + key1 + `" type="text">
                </label>
            </div>`;
        });
        html += `</div>
        </li>`
    });

    html += '</form>';

    return html;
}

function createSelectElement(currentField, selected, key, inputType) {
    var selectElem = '';
    // currentField['options'].unshift('None Selected');
    selectElem += `<select class="form-control custom-input" style="margin-top:5px" name="` + key + `" dataid="` + key + `" onchange="watchOnchange(` + key + `)">
                   <option>select</option>`;

    for (var val of currentField['options']) {
        if (val == selected) {
            selectElem += `<option class="custom-input" style="margin-top:5px" value="` + val + `" selected>` + val + `</option>`;
        } else {
            selectElem += `<option class="custom-input" style="margin-top:5px" value="` + val + `">` + val + `</option>`;
        }
    }
    selectElem += `</select>`;
    return selectElem;
}

function createInputElement(val, key, field, inputType) {
    var inputElem = '';
    var is_disable = '';
    if (!val) val = '';
    else is_disable = "disabled";

    if (field.unique === 1) {
        // $("#mod-head").text(val);
        // inputElem += `<input type="` + inputType + `" class="form-control custom-input" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" ` + is_disable + ` data-change="true" required>`;
    } else {
        if(inputType === "radio"){
            inputElem += `<input type="` + inputType + `" class="custom-input" id="` + key + `" style="display: block"  name="` + key + `" dataid="` + key + `" value="` + val + `" onchange="watchOnchange(` + key + `)">`;            
        }else if(inputType === "tel"){
            inputElem += `<input type="` + inputType + `" class="form-control custom-input" maxlength="14" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" onchange="watchOnchange(` + key + `)">`;
        }else if(field.column_type_id == 11) {
            inputElem += `<textarea id="` + key + `" class="form-control custom-input" rows="4" cols="70" name="` + key + `" dataid="` + key + `" placeholder="` + key + `" onchange="watchOnchange(` + key + `)">` + val + `</textarea>`;
        }else{
            inputElem += `<input type="` + inputType + `" class="form-control custom-input" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" onchange="watchOnchange(` + key + `)">`;
        }
    }
    return inputElem;
}

function createHiddenElement(val, key) {
    var inputElem = '';
    inputElem += `<input type="hidden" class="form-control custom-input" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" >`;
    return inputElem;
}


function createFilterDropdown(obj,type) {
    console.log(obj,type);
    
}