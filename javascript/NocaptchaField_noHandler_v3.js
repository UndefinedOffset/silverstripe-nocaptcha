function nocaptcha_handleCaptcha(form, callback) {
  var input = document.getElementById('Nocaptcha-' + form.getAttribute('id'));
  grecaptcha.execute(input.getAttribute('data-sitekey'), {action: 'submit'}).then(function (token) {
      input.value = token;
      if (callback) {
        callback();
      }
  });
};
