var ajaxInProgress = false;
var Toast;

$(function () {
    initLoader();
    initCustomFileInput();
    initSelect2();
    initInputMask();
    initDate();
    initDateTime();
    initDateRange();
    initDateTimeRange();
    initSummernote();
    initSwalToast();
    initJqueryForm();
    initAjaxUrlHandler();
    initModal();
});

// inits loader on ajax request
function initLoader() {
    $(document).ajaxSend(function () {
        if (ajaxInProgress) {
            // c("Ajax Request rejected because a request is already in progress");
            return false;
        } else {
            ajaxInProgress = true;
            showLoader();
        }
    });
    $(document).ajaxComplete(function () {
        ajaxInProgress = false;
        hideLoader();
    });
}

function showLoader() {
    $('#loader').fadeIn(250);
}

function hideLoader() {
    $('#loader').fadeOut(250);
}

function initCustomFileInput() {
    bsCustomFileInput.init();
}

function initSelect2() {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });

    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: "Select...",
        allowClear: true
    });
}

function initInputMask() {
    $(".number").inputmask();
    $(".price").inputmask();
}

function initDate() {
    $('.date').datetimepicker({
        format: dateFormat,
        icons: {
            time: 'fas fa-clock',
            date: 'fas fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-arrow-circle-left',
            next: 'fas fa-arrow-circle-right',
            today: 'far fa-calendar-check-o',
            clear: 'fas fa-trash',
            close: 'far fa-times'
        }
    });
}

function initDateTime() {
    $('.datetime').datetimepicker({
        format: dateTimeFormat,
        icons: {
            time: 'fas fa-clock',
            date: 'fas fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-arrow-circle-left',
            next: 'fas fa-arrow-circle-right',
            today: 'far fa-calendar-check-o',
            clear: 'fas fa-trash',
            close: 'far fa-times'
        }
    });
}

function initDateRange() {
    let options = {
        autoUpdateInput: false,
        locale: {
            format: dateFormat
        }
    };

    $('.daterange').daterangepicker(options, function (start, end) {
        $('.daterange').val(`${start.format(dateFormat)} - ${end.format(dateFormat)}`);
    });
}

function initDateTimeRange() {
    let options = {
        autoUpdateInput: false,
        timePicker: true,
        timePickerIncrement: 30,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: dateTimeFormat
        }
    };

    $('.datetimerange').daterangepicker(options, function (start, end) {
        $('.datetimerange').val(`${start.format(dateTimeFormat)} - ${end.format(dateTimeFormat)}`);
    });
}

function initSummernote() {
    $('.summernote').summernote();
}

// inits SWAL2 toast
function initSwalToast() {
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

function showAlert(form, errors) {
    let allErrors = [];

    for (fieldKey in errors) {
        let fieldName = fieldKey;
        if (fieldName.includes('.')) {
            let f = fieldName.split(".");
            fieldName = f.map(function (field, i) { return i > 0 ? `[${field}]` : field; }).join('');
        }
        let field = form.find(`[name="${fieldName}"]`);
        field.prop('aria-invalid', true)
        field.addClass('is-invalid');

        let fieldErrors = errors[fieldKey];
        let errorSpan = form.find(`#${fieldName}-error`);
        errorSpan.html(fieldErrors.join("</br>"));

        allErrors = allErrors.concat(fieldErrors);
    }

    let formAlert = form.find('#form-alert');
    formAlert.show();
    formAlert.find('#errors').html(allErrors.join('</br>'));
}

function clearAlert(form) {

    form.find('.is-invalid').each(function () {
        let field = $(this);
        field.removeClass('is-invalid');
        field.prop('aria-invalid', false)

        let errorSpan = $(`#${field.attr('aria-describedby')}`);
        errorSpan.html('');
    })

    let formAlert = form.find('#form-alert');
    formAlert.hide();
    formAlert.find('#errors').html('');
}

// basic jquery form submit
function initJqueryForm(targetForm = '.ajax-form', successCallback = null, errorCallback = null, cancelCallback = null) {
    var form = $(targetForm);
    var options = {
        dataType: 'json',
        beforeSubmit: function (formData, formObject, options) {
            clearAlert(form);
            return true;
        },
        success: function (response, status) {
            if (ok(response.message)) toastSuccess(response.message);
            if (successCallback) successCallback(response, status);
            else if (ok(response.navigate)) navigate(response.navigate, ok(response.timeout) ? response.timeout : defaultTimeOut);
        },
        error: function (jqXHR) {
            let response = jqXHR.responseJSON;
            if (ok(response.message) && response.message) toastError(response.message);
            else toastError(`Your request failed. Server responded with code: ${jqXHR.status}`);
            let errors = response.errors;
            if (ok(errors)) {
                showAlert(form, errors);
            }
            if (errorCallback) errorCallback(response, jqXHR.status);
        }
    };

    // bind form using 'ajaxForm' 
    form.ajaxForm(options);
    cc(targetForm, 'Ajax Form initialized');

    form.find('#cancel-button').click(function () {
        if (cancelCallback) cancelCallback();
        else {
            let cancelUrl = $(this).data('url');
            if (cancelUrl) navigate(cancelUrl, 100);
        }
    })
}

// used for POST logout api requests
function initAjaxUrlHandler() {
    $('.ajax-url').click(function () {
        let url = $(this).attr('href');
        let method = $(this).data('method');
        let navigate = $(this).data('navigate');
        $.ajax({
            type: method,
            url: url,
            success: function () {
                goto(navigate);
            }
        });
        return false;
    });
}

function initModal(successCallback) {
    $('#modal').on('show.bs.modal', function (e) {

        let modal = $(this);
        let button = $(e.relatedTarget);
        let remoteUrl = button.data('remote');
        const url = new URL(remoteUrl);
        url.searchParams.append('view', 'modal');
        remoteUrl = url.toString();
        // load content from value of data-remote url
        modal.find('.modal-title').text(button.data("title"));
        modal.find('.modal-body').html('<div class="d-flex justify-content-center modal-placeholder"><h1><i class="fas fa-sync"></i>&nbsp;Loading...</h1></div>');
        if (remoteUrl) {
            modal.find('.modal-body').load(`${remoteUrl}`, function (response, status, xhr) {
                if (status == 'error') {
                    cc(status, xhr);
                    modal.find('.modal-body').html(response);
                }
                else {
                    initJqueryForm('.ajax-form', function (response, status) {
                        modal.modal('toggle');
                        if (successCallback) successCallback(response);
                    }, null, function () {
                        modal.modal('toggle');
                    });

                    // reinitialize any input plugins
                    initSelect2();
                    initInputMask();
                    initDateRange();
                    initSummernote();
                }
            });
        }

    });
}