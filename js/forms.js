$(document).ready(function ($) {

    var validation_rules = {
        name: { required: true },
        email: { required: true, email: true },
        subject: { required: true },
        message: { required: true },
        agreement: { required: true }
    };

    var validation_messages = {
        agreement: {
            required: "You must agree with that you are not spamming or marketing"
        }
    };



    $('#gmailApiForm').validate({
        errorElement: 'small',
        errorClass: 'gapierror',
        errorPlacement: function (error, element) {
            if (element.attr("type") == "checkbox") {
                error.appendTo(element.parent('div'));
            } else {
                error.insertAfter($(element));
            }
        },

        rules: validation_rules,
        messages: validation_messages,
        submitHandler: function (form) {
            var formData = new FormData(form);
            $.ajax({
                url: './contact.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        $("#formResponse").html('<div class="alert alert-success" role="alert">' + response.message + '</div>');
                    } else {
                        $("#errorResponse").html('<div class="alert alert-danger" role="alert">' + response.message + '</div>');
                    }
                },
                error: function (xhr, status, error) {
                    $("#errorResponse").html('<div class="alert alert-danger" role="alert">An error occurred while sending your message. Please try again later.</div>');
                }
            });

        }

    });

});





