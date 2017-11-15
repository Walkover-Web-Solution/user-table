function filterTmpl(data) {
    var html = '<form id="filterForm" >';
    $.each(data, function(key, val) {
        // console.log('key, value:', key, val);
        html += `<li class="active">
        <div class="form-check">
            <label class="form-check-label" >
            <input type="checkbox" class="filterConditionName" dataid="` + key + `"  onclick="showDiv('condition_` + key + `')" aria-label="...">` + key + `</label>
        </div>
        <div id="condition_` + key + `" class="hide filter-option">`;
        $.each(val, function(key1) {
            html += `<div class="form-check">
                <label class="form-check-label radio-label">
                    <input class="form-check-radio" name="` + key + `_filter" dataid="` + key1 + `" onclick="showFilterInputText(this,'` + key + `')" type="radio" aria-label="...">` + key1 + `
                    <input class="form-check-input filterinput` + key + ` form-control" name="` + key + `_filter_text" id="` + key + `_filter_text_` + key1 + `" type="text">
                </label>
            </div>`;
        })
        html += `</div>
        </li>`
    });

    html += '</form>';

    return html;
}

function createSelectElement(currentField, selected, key) {
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

function createInputElement(val, key, cls) {
    var inputElem = '';
    if (!val) {
        val = '';
    }
    if (key === "username") {
        inputElem += `<input type="text" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" class="form-control" disabled data-change="true">`;
    } else {
        inputElem += `<input type="text" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" class="form-control" onchange="watchOnchange(` + key + `)">`;
    }

    return inputElem;
}