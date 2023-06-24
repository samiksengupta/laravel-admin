$(function(){

    new ClipboardJS('.copy-btn');
    bindEvents();
    loadtoken();

});

var pathParam = {};
var defaultApiUrl = '';

function bindEvents() {

    const defaultApiUrl = $("#api-url").data('url-template');

    $("input.path-param").each(function(){
        let el = $(this);
        pathParam[el.data('placeholder')] = el.data('placeholder');
    });

    $("input.path-param").keyup(function(){
        let apiUrl = defaultApiUrl;

        let el = $(this);
        let elVal = el.val();
        
        pathParam[el.data('placeholder')] = (elVal === "") ? el.data('placeholder') : elVal;
        updateApiUrl();
    });
    
    $('#json-fill').click(function(){
        let string = $('#json-string').val();
        fillWithJSON(string);
    });

    $('#test-api-form').submit(function(){
        return formSubmit();
    });

    $('#form-params').text('');
    $('#form-response').text('');
}

function updateApiUrl() {
    let replaced = $("#api-url").data('url-template');
    for(key in pathParam) {
        replaced = replaced.replace(key, pathParam[key]);
    }
    $("#api-url").val(replaced);
}

// ---------------------------------------

function formSubmit() {
    let form = $('#test-api-form');
    let apiUrlInput = $('#api-url');
    let apiRefreshUrl = apiUrlInput.data('refresh-url');
    $.ajax({
        type: apiUrlInput.data('method'),
        url: apiUrlInput.val(),
        data: form.serialize(), 
        dataType: 'json', 
        complete: function(xhr) { 
            // Refresh token if expired
            if(xhr.status === 401) {
                $.post(apiRefreshUrl, {token: localStorage.getItem("api-test-token")}).done(function(response) {
                    storeToken(response.access_token ?? false);
                    loadtoken();
                    formSubmit();
                    toastSuccess("Token was refreshed successfully!");
                }).fail(function(xhr, status, error) {
                    toastError(xhr.responseJSON.message);
                });
            }
            else {
                storeToken(xhr.responseJSON.access_token ?? false);
                displayResponse(xhr);
                scrollTo('#response-box');
            }
        },
    });
    return false;
}

function displayResponse(xhr) {
    
    let text = JSON.stringify(xhr.responseJSON, null, 4)
    $('#response-data').text(text);
    $('#response-status').text(xhr.status);
    
    $('#response-data').removeClass('response-good');
    $('#response-data').removeClass('response-bad');
    $('#response-data').removeClass('response-critical');

    if(xhr.status >= 200 && xhr.status < 300) {
        $('#response-data').addClass('response-good');
    }
    else if(xhr.status >= 500) {
        $('#response-data').addClass('response-critical');
    }
    else {
        $('#response-data').addClass('response-bad');
    }
    
    displayRequestParams();

    $('#response-box').show();
}

function displayRequestParams() {
    
    const form = $('#test-api-form');
    const formArray = form.serializeArray();
    const indexedArray = {};

    $.map(formArray, function(n, i){
        indexedArray[n['name']] = n['value'];
    });

    // for(i in formArray) if(formArray[i].value.length > 97) formArray[i].value = (formArray[i].value.substring(0,97)) + '...';
    $('#request-params').text(JSON.stringify(indexedArray, null, 4));
}

function scrollTo(selector) {
    $('html, body').animate({
        scrollTop: $(selector).offset().top
    }, 1000);
}

function clearJSON() {
    $('#json-string').val("");
}

function fillWithJSON(jsonString) {
    try {
        let jsonObject = JSON.parse(jsonString);
        console.log(jsonObject);
        for(i in jsonObject) {
            $('input[name=' + i + ']').val(jsonObject[i]);
            $('select[name=' + i + ']').val(jsonObject[i]).trigger('change');
        }
    }
    catch(e) {
        alert("Could not read JSON format");
    }
    finally {

    }
}

function loadtoken() {
    if($('#field-token').length > 0) {
        $('#field-token').val(localStorage.getItem("api-test-token") ?? '');
    }
}

function storeToken(token) {
    if(token) localStorage.setItem("api-test-token", token);
}