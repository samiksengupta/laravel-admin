$(function(){
    // initLoginForm();
});

function initLoginForm() {

    let submitButton;
    var options = { 
        dataType: 'json',
        beforeSubmit: function(formData, formObject, options) {
            submitButton = formObject.find(':submit');
            submitButton.prop('disabled', true);
            return true;
        },
        success: function(response, status) {
            cc('success/resp', response);
            toastSuccess(response.message);
            if(ok(response.navigate)) navigate(response.navigate, 3000);
        },
        error: function(jqXHR) {
            let response = jqXHR.responseJSON;
            cc('error/resp', response);
            toastError(response.message);
            submitButton.prop('disabled', false);

            if(ok(response.validation) && ok(response.validation.errors)) {
                displayValidationErrors(response.validation);
                var myhtml = document.createElement("div");
                myhtml.innerHTML = response.validation.message;
                swal({
                    title: "Errors",
                    content: myhtml,
                    icon: "error",
                    dangerMode: true,
                });
            }
        }
    }; 
 
    // bind form using 'ajaxForm' 
    $('#login-form').ajaxForm(options); 
}