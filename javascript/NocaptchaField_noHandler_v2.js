var _noCaptchaFields=_noCaptchaFields || [];

// form isn't necessary for v2, but we have it here to keep the function signature consistent.
function nocaptcha_handleCaptcha(form, callback) {
    if (callback) {
        callback();
    }
};

function noCaptchaFieldRender() {
    var render = function(field) {
        var options={
            'sitekey': field.getAttribute('data-sitekey'),
            'theme': field.getAttribute('data-theme'),
            'type': field.getAttribute('data-type'),
            'size': field.getAttribute('data-size'),
            'badge': field.getAttribute('data-badge'),
        };

        var widget_id = grecaptcha.render(field, options);
        field.setAttribute("data-widgetid", widget_id);
    }

    for(var i=0;i<_noCaptchaFields.length;i++) {
        render(document.getElementById('Nocaptcha-'+_noCaptchaFields[i]));
    }
}
