function filterTmpl(data) {
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
    selectElem += `<select class="form-control" name="` + key + `" dataid="` + key + `" onchange="watchOnchange(` + key + `)">
                   <option>select</option>`;

    for (var val of currentField['options']) {
        if (val == selected) {
            selectElem += `<option value="` + val + `" selected>` + val + `</option>`;
        } else {
            selectElem += `<option value="` + val + `">` + val + `</option>`;
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
        inputElem += `<input type="` + inputType + `" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" ` + is_disable + ` data-change="true" required>`;
    } else {
        inputElem += `<input type="` + inputType + `" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" onchange="watchOnchange(` + key + `)">`;
    }
    return inputElem;
}

function createHiddenElement(val, key) {
    var inputElem = '';
    inputElem += `<input type="hidden" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" >`;
    return inputElem;
}
