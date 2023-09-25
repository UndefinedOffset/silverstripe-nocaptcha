Nocaptcha
=================

Adds a "spam protection" field to SilverStripe userforms using Google's
[reCAPTCHA](https://www.google.com/recaptcha) service.

## Requirements
* SilverStripe ^4 | ^5
* [SilverStripe Spam Protection
  ^2 | ^3](https://github.com/silverstripe/silverstripe-spamprotection/)
* PHP CURL

## Installation
```
composer require undefinedoffset/silverstripe-nocaptcha
```

After installing the module via composer or manual install you must set the spam
protector to NocaptchaProtector, this needs to be set in your site's config file
normally this is mysite/\_config/config.yml.
```yml
SilverStripe\SpamProtection\Extension\FormSpamProtectionExtension:
    default_spam_protector: UndefinedOffset\NoCaptcha\Forms\NocaptchaProtector
```

Finally, add the "spam protection" field to your form by calling
``enableSpamProtection()`` on the form object.
```php
$form->enableSpamProtection();
```

## Configuration
There are multiple configuration options for the field, you must set the
site_key and the secret_key which you can get from the [reCAPTCHA
page](https://www.google.com/recaptcha). These configuration options must be
added to your site's yaml config typically this is app/\_config/config.yml.
```yml
UndefinedOffset\NoCaptcha\Forms\NocaptchaField:
    site_key: "YOUR_SITE_KEY" #Your site key (required)
    secret_key: "YOUR_SECRET_KEY" #Your secret key (required)
    recaptcha_version: 2 # 2 or 3
    minimum_score: 0.2 # minimum spam score to achieve. Any less is blocked
    verify_ssl: true #Allows you to disable php-curl's SSL peer verification by setting this to false (optional, defaults to true)
    default_theme: "light" #Default theme color (optional, light or dark, defaults to light)
    default_type: "image" #Default captcha type (optional, image or audio, defaults to image)
    default_size: "normal" #Default size (optional, normal, compact or invisible, defaults to normal)
    default_badge: "bottomright" #Default badge position (bottomright, bottomleft or inline, defaults to bottomright)
    default_handle_submit: true #Default setting for whether nocaptcha should handle form submission. See "Handling form submission" below.
    proxy_server: "" #Your proxy server address (optional)
    proxy_port: "" #Your proxy server address port (optional)
    proxy_auth: "" #Your proxy server authentication information (optional)

# The following options can also be specified through Environment variables with Injector config
SilverStripe\Core\Injector\Injector:
  UndefinedOffset\NoCaptcha\Forms\NocaptchaField:
    properties:
      SiteKey: '`SS_NOCAPTCHA_SITE_KEY`'
      SecretKey: '`SS_NOCAPTCHA_SECRET_KEY`'
      ProxyServer: '`SS_OUTBOUND_PROXY`'
      ProxyPort: '`SS_OUTBOUND_PROXY_PORT`'
      ProxyAuth: '`SS_OUTBOUND_PROXY_AUTH`'
```

## Adding field labels

If you want to add a field label or help text to the Captcha field you can do so
like this:

```php
$form->enableSpamProtection()
    ->fields()->fieldByName('Captcha')
    ->setTitle("Spam protection")
    ->setDescription("Please tick the box to prove you're a human and help us stop spam.");
```

### Commenting Module
When your using the
[silverstripe/comments](https://github.com/silverstripe/silverstripe-comments)
module you must add the following (per their documentation) to your \_config.php
in order to use nocaptcha/spamprotection on comment forms.

```php
CommentingController::add_extension('CommentSpamProtection');
```

## Retrieving the Verify Response

If you wish to manually retrieve the Site Verify response in you form action use
the `getVerifyResponse()` method

```php
function doSubmit($data, $form) {
    $captchaResponse = $form->Fields()->fieldByName('Captcha')->getVerifyResponse();

    // $captchaResponse = array (size=5) [
    //  'success' => boolean true
    //  'challenge_ts' => string '2020-09-08T20:48:34Z' (length=20)
    //  'hostname' => string 'localhost' (length=9)
    //  'score' => float 0.9
    //  'action' => string 'submit' (length=6)
    // ];
}
```

## ReCAPTCHA v3

ReCAPTCHA v3 is different to v2, users won't be presented with a "Are you a
robot?" checkbox, instead user actions are returned a spam score 0.0 to 1.0 when
they submit the form. Out of the box, this module will block any submission with
a spam score of <= 0.4 but this can be tailored either site-wide using the
Config API

```yml
UndefinedOffset\NoCaptcha\Forms\NocaptchaField:
  minimum_score: 0.2
```

Or on a per form basis:

```php
$captchaField = $form->Fields()->fieldByName('Captcha')-
$captchaField->setMinimumScore(0.2);
```

For more information about version 3, including how to implement custom actions
see https://developers.google.com/recaptcha/docs/v3

## Handling form submission
By default, the javascript included with this module will add a submit event handler to your form.

If you need to handle form submissions in a special way (for example to support front-end validation),
you can choose to handle form submit events yourself.

This can be configured site-wide using the Config API
```yml
UndefinedOffset\NoCaptcha\Forms\NocaptchaField:
    default_handle_submit: false
```

Or on a per form basis:
```php
$captchaField = $form->Fields()->fieldByName('Captcha');
$captchaField->setHandleSubmitEvents(false);
```

With this configuration no event handlers will be added by this module to your form. Instead, a
function will be provided called `nocaptcha_handleCaptcha` which you can call from your code
when you're ready to submit your form. It has the following signature:
```js
function nocaptcha_handleCaptcha(form, callback)
```
`form` must be the form element, and `callback` should be a function that finally submits the form,
though it is optional.

In the simplest case, you can use it like this:
```js
document.addEventListener("DOMContentLoaded", function(event) {
    // where formID is the element ID for your form
    const form = document.getElementById(formID);
    const submitListener = function(event) {
        event.preventDefault();
        let valid = true;
        /* Your validation logic here */
        if (valid) {
            nocaptcha_handleCaptcha(form, form.submit.bind(form));
        }
    };
    form.addEventListener('submit', submitListener);
});
```

## Reporting an issue

When you're reporting an issue please ensure you specify what version of
SilverStripe you are using i.e. 3.1.3, 3.2beta, master etc. Also be sure to
include any JavaScript or PHP errors you receive, for PHP errors please ensure
you include the full stack trace. Also please include how you produced the
issue. You may also be asked to provide some of the classes to aid in
re-producing the issue. Stick with the issue, remember that you seen the issue
not the maintainer of the module so it may take allot of questions to arrive at
a fix or answer.
