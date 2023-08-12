var defaultTimeOut = 100;

// var dateFormat = "yy-mm-dd";
// var timeFormat = "hh:mm tt";
var dateFormat = "YYYY-MM-DD"
var dateTimeFormat = "YYYY-MM-DD HH:mm:ss"

function ok(obj) {
    try {
        if (obj === 0) return true;
        return (Boolean(obj) && 'undefined' !== typeof obj && obj !== null);
    }
    catch (e) {
        return false;
    }
}

function c(obj) {
    console.log(obj);
}

function cc(string, obj) {
    console.log(string, obj);
}

function f(obj) {
    return parseFloat(obj);
}

function calculate(item) {
    item.discount_rate = item.discount_rate ?? 0;
    item.discount = roundToTwo(item.mrp * (item.discount_rate / 100))
    item.net_price = item.mrp - item.discount;
    switch (item.tax_method) {
        case 'INC':
            item.total = roundToTwo(item.net_price * item.quantity)
            item.tax = roundToTwo(item.total - (item.total * (100 / (100 + f(item.tax_rate)))))
            item.taxable = roundToTwo(item.total - item.tax)
            break;
        case 'EXC':
        default:
            item.taxable = roundToTwo(item.net_price * item.quantity)
            item.tax = roundToTwo(f(item.tax_rate) * item.taxable * 0.01)
            item.total = roundToTwo(f(item.taxable) + f(item.tax))
            break;
    }
    return item;
}

function calculateTotals(items) {
    let quantity = 0;
    let tax = 0;
    let total = 0;
    for (item of items) {
        quantity += parseFloat(item.quantity);
        tax += parseFloat(item.tax);
        total += parseFloat(item.total);
    }
    return {
        quantity: quantity.toFixed(2),
        tax: tax.toFixed(2),
        total: total.toFixed(2),
    };
}

function roundToTwo(num) {
    return +(Math.round(num + "e+2") + "e-2");
}

function round(num) {
    return Math.round(num);
}

function isString(obj) {
    return (typeof obj === 'string' || obj instanceof String);
}

function snake(string) {
    return string.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g).map(x => x.toLowerCase()).join('_');
}

function goto(url, delay = 1000) {
    setTimeout(function () { window.location.replace(url); }, delay);
}

function navigate(url, delay = 1000) {
    setTimeout(function () { window.location = url; }, delay);
}

function populateOptions(selector, dataset, selected) {
    let el = $(selector);
    el.empty();

    for (option of dataset) {
        let optionSelected = Array.isArray(selected) ? selected.includes(option.key) : (selected == option.key);
        let optionElement = $('<option/>').attr({ value: option.key }).prop('selected', optionSelected).text(option.text);
        el.append(optionElement);
    }
}

function getGenericResponseMessage(response) {
    let message = '';
    switch (response.status) {
        case 200:
        case 201:
        case 204:
            message += 'Your action was successful.'
            break;
        case 400:
            message += 'Could not complete your action because your request was rejected.'
            break;
        case 401:
            message += 'You do not have authentication to perform your action.'
            break;
        case 403:
            message += 'You do not have authorization to perform your action.'
            break;
        case 404:
            message += 'Request failed because could not find required data.'
            break;
        default:
            message += 'Something went wrong. Please notify your administrator.'
    }
    return message;
}

function toastResponse(response) {
    let isSuccess = (response.status >= 200 && response.status < 300);
    let message = getGenericResponseMessage(response);
    if (isSuccess) {
        toastSuccess(message);
    }
    else {
        toastError(message);
    }
}

function toastSuccess(message) {
    Toast.fire({
        icon: 'success',
        titleText: message
    })
}

function toastError(message) {
    Toast.fire({
        icon: 'error',
        titleText: message
    })
}

function toastInfo(message) {
    Toast.fire({
        icon: 'info',
        titleText: message
    })
}

function toastWarning(message) {
    Toast.fire({
        icon: 'warning',
        titleText: message
    })
}

function toastQuestion(message) {
    Toast.fire({
        icon: 'question',
        titleText: message
    })
}

function swal(title, message, type) {
    Swal.fire(title, message, type);
}

function swalConfirm(message, confirmCallback = null, denyCallback = null) {
    Swal.fire({
        title: `Are you sure?`,
        html: message,
        icon: "warning",
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: 'Yes',
        denyButtonText: 'No',
        customClass: {
            actions: 'rounded-0',
            confirmButton: 'btn btn-success rounded-0',
            denyButton: 'btn btn-default rounded-0',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (confirmCallback) confirmCallback();
        }
        else if (result.isDenied) {
            if (denyCallback) denyCallback()
        }
    });
}