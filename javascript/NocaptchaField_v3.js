var _noCaptchaForms = _noCaptchaForms || [];

function noCaptchaFormRender() {
    var render = function (form) {
        //For the invisible captcha we need to setup some callback listeners
        var superHandler = false;

        if (typeof jQuery != 'undefined' && typeof jQuery.fn.validate != 'undefined') {
            var formValidator = jQuery(form).data('validator');
            if (!formValidator) {
                formValidator = jQuery(form).validate();
            }

            var superHandler = formValidator.settings.submitHandler;
            formValidator.settings.submitHandler = function (form) {
                formValidator.cancelSubmit = true;

                var button = form.querySelectorAll('.nocaptcha-v3')[0];
                grecaptcha.execute(button.getAttribute('data-sitekey'), {action: 'submit'}).then(function (token) {
                    document.getElementById('Nocaptcha-' + form.getAttribute('id')).value = token;

                    if (superHandler) {
                        superHandler(form);
                    } else {
                        form.unbund('submit').submit();
                    }
                });
            };
        } else {
            var submitListener = function(e) {
                e.preventDefault();

                var button = e.currentTarget.querySelectorAll('.nocaptcha-v3')[0];
                grecaptcha.execute(button.getAttribute('data-sitekey'), {action: 'submit'}).then(function (token) {
                    if (form && form.addEventListener) {
                        form.removeEventListener('submit', submitListener);
                    } else if (form && form.attachEvent) {
                        window.detatchEvent('onsubmit', submitListener);
                    }

                    document.getElementById('Nocaptcha-' + form.getAttribute('id')).value = token;

                    form.submit();
                });
            };

            if (form && form.addEventListener) {
                form.addEventListener('submit', submitListener);
            } else if (form && form.attachEvent) {
                window.attachEvent('onsubmit', submitListener);
            } else if (console.error) {
                console.error('Could not attach event to the form');
            }
        }
    }

    for (var i = 0; i < _noCaptchaForms.length; i++) {
        render(document.getElementById(_noCaptchaForms[i]));
    }
}
