var _noCaptchaFields=_noCaptchaFields || [];

function noCaptchaFieldRender() {
    for(var i=0;i<_noCaptchaFields.length;i++) {
        var field=document.getElementById('Nocaptcha-'+_noCaptchaFields[i]);
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'callback': (field.getAttribute('data-callback') ? verifyCallback : undefined )
        };
        
        grecaptcha.render(field, options);
    }
}
