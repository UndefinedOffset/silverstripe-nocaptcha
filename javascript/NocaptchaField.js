var _noCaptchaFields=_noCaptchaFields || [];
var _noCaptchaValidationExemptActions=_noCaptchaValidationExemptActions || [];

function noCaptchaFieldRender() {
    var submitListener=function(e) {
        // If the action is exempt from validation, skip any recaptcha checks
        if (e.submitter &&
            e.submitter.name &&
            _noCaptchaValidationExemptActions.indexOf(e.submitter.name.substring(7)) > -1) {
            return;
        }

        e.preventDefault();
        var widgetID = e.target.querySelectorAll('.g-recaptcha')[0].getAttribute('data-widgetid');
        grecaptcha.execute(widgetID);
    };

    var render = function(field) {
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'size': field.getAttribute('data-size'),
            'badge': field.getAttribute('data-badge'),
        };

        //For the invisible captcha we need to setup some callback listeners
        if(field.getAttribute('data-size')=='invisible' && field.getAttribute('data-callback')==null) {
            var form=document.getElementById(field.getAttribute('data-form'));
            var superHandler=false;

            if(typeof jQuery!='undefined' && typeof jQuery.fn.validate!='undefined') {
                var formValidator=jQuery(form).data('validator');
                if (!formValidator) {
                    formValidator=jQuery(form).validate();
                }
                var superHandler=formValidator.settings.submitHandler;
                formValidator.settings.submitHandler=function(form) {
                    var widgetID = form.querySelectorAll('.g-recaptcha')[0].getAttribute('data-widgetid');
                    grecaptcha.execute(widgetID);
                };
            }else {
                if(form && form.addEventListener) {
                    form.addEventListener('submit', submitListener);
                }else if(form && form.attachEvent) {
                    window.attachEvent('onsubmit', submitListener);
                }else if(console.error) {
                    console.error('Could not attach event to the form');
                }
            }

            window['Nocaptcha-'+_noCaptchaFields[i]]=function() {
                return new Promise(function(resolve, reject) {
                    if(typeof jQuery!='undefined' && typeof jQuery.fn.validate!='undefined' && superHandler) {
                        superHandler(form);
                    }else {
                        form.submit();
                    }

                    resolve();
                });
            };

            options.callback = 'Nocaptcha-'+_noCaptchaFields[i];
        } else if (field.getAttribute('data-callback')) {
            options.callback = field.getAttribute('data-callback');
        }

        var widget_id = grecaptcha.render(field, options);
        field.setAttribute("data-widgetid", widget_id);
    }

    for(var i=0;i<_noCaptchaFields.length;i++) {
        render(document.getElementById('Nocaptcha-'+_noCaptchaFields[i]));
    }
}
