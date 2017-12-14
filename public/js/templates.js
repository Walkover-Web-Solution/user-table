function filterTmpl(data) {
    var html = '<form id="filterForm" >';
    $.each(data, function(key, val) {
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

function createInputElement(val, key, cls, field) {
    var inputElem = '';
    if (!val) {
        val = '';
    }
    if (field.unique === 1) {
        inputElem += `<input type="text" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" class="form-control" disabled data-change="true">`;
    } else {
        inputElem += `<input type="text" class="form-control" id="` + key + `"  name="` + key + `" dataid="` + key + `" value="` + val + `" placeholder="` + key + `" class="form-control" onchange="watchOnchange(` + key + `)">`;
    }

    return inputElem;
}