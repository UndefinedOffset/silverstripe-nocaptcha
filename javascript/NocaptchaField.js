var _noCaptchaFields=_noCaptchaFields || [];

function noCaptchaFieldRender() {
    var submitListener=function(e) {
        e.preventDefault();
        
        grecaptcha.execute();
    };
    
    for(var i=0;i<_noCaptchaFields.length;i++) {
        var field=document.getElementById('Nocaptcha-'+_noCaptchaFields[i]);
        
        
        //For the invisible captcha we need to setup some callback listeners
        if(field.getAttribute('data-size')=='invisible' && field.getAttribute('data-callback')==null) {
            var form=document.getElementById(field.getAttribute('data-form'));
            
            if(form && form.addEventListener) {
                form.addEventListener('submit', submitListener);
            }else if(form && form.attachEvent) {
                window.attachEvent('onsubmit', submitListener);
            }else if(console.error) {
                console.error('Could not attach event to the form');
            }
            
            window['Nocaptcha-'+_noCaptchaFields[i]]=function() {
                form.submit();
            };
        }
        
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'size': field.getAttribute('data-size'),
            'callback': (field.getAttribute('data-callback') ? field.getAttribute('data-callback') : 'Nocaptcha-'+_noCaptchaFields[i])
        };
        
        var widget_id = grecaptcha.render(field, options);
        field.setAttribute("data-widgetid", widget_id);
    }
}
