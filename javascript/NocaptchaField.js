var _noCaptchaFields=_noCaptchaFields || [];

function noCaptchaFieldRender() {
    var submitListener=function(e) {
        e.preventDefault();
        
        grecaptcha.execute();
    };
    
    for(var i=0;i<_noCaptchaFields.length;i++) {
        var field=document.getElementById('Nocaptcha-'+_noCaptchaFields[i]);
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'size': field.getAttribute('data-size'),
            'badge': field.getAttribute('data-badge')
        };
        
        //For the invisible captcha we need to setup some callback listeners
        if(field.getAttribute('data-size')=='invisible' && field.getAttribute('data-callback')==null) {
            var form=document.getElementById(field.getAttribute('data-form'));
            var superHandler=false;
            
            if(typeof jQuery!='undefined' && typeof jQuery.fn.validate!='undefined') {
                var formValidator=jQuery(form).data('validator');
                var superHandler=formValidator.settings.submitHandler;
                formValidator.settings.submitHandler=function(form) {
                    grecaptcha.execute();
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
                if(typeof jQuery!='undefined' && typeof jQuery.fn.validate!='undefined' && superHandler) {
                    superHandler(form);
                }else {
                    form.submit();
                }
            };
            options.callback = 'Nocaptcha-'+_noCaptchaFields[i];
        } else if (field.getAttribute('data-callback')) {
            options.callback = field.getAttribute('data-callback');
        }
        
        var widget_id = grecaptcha.render(field, options);
        field.setAttribute("data-widgetid", widget_id);
    }
}
