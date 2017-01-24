var _noCaptchaFields=_noCaptchaFields || [];

function noCaptchaFieldRender() {
    for(var i=0;i<_noCaptchaFields.length;i++) {
        var field=document.getElementById('Nocaptcha-'+_noCaptchaFields[i]);
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'size': field.getAttribute('data-size'),
            'callback': (field.getAttribute('data-callback') ? verifyCallback : undefined )
        };
        
        var widget_id = grecaptcha.render(field, options);
        field.setAttribute("data-widgetid", widget_id);
    }
}
