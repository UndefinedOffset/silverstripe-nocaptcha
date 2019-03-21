Nocaptcha
=================

Adds a "spam protection" field to SilverStripe userforms using Google's [reCAPTCHA 2](https://www.google.com/recaptcha) service.

**Note, this implements reCAPTCHA 2, not 3.**

## Requirements
* SilverStripe 4.x
* [SilverStripe Spam Protection 3.x](https://github.com/silverstripe/silverstripe-spamprotection/)
* PHP CURL

## Installation
```
composer require undefinedoffset/silverstripe-nocaptcha
```

After installing the module via composer or manual install you must set the spam protector to NocaptchaProtector, this needs to be set in your site's config file normally this is mysite/\_config/config.yml.
```yml
SilverStripe\SpamProtection\Extension\FormSpamProtectionExtension:
    default_spam_protector: UndefinedOffset\NoCaptcha\Forms\NocaptchaProtector
```

Finally, add the "spam protection" field to your form fields.

## Configuration
There are multiple configuration options for the field, you must set the site_key and the secret_key which you can get from the [reCAPTCHA page](https://www.google.com/recaptcha). These configuration options must be added to your site's yaml config typically this is mysite/\_config/config.yml.
```yml
UndefinedOffset\NoCaptcha\Forms\NocaptchaField:
    site_key: "YOUR_SITE_KEY" #Your site key (required)
    secret_key: "YOUR_SECRET_KEY" #Your secret key (required)
    verify_ssl: true #Allows you to disable php-curl's SSL peer verification by setting this to false (optional, defaults to true)
    default_theme: "light" #Default theme color (optional, light or dark, defaults to light)
    default_type: "image" #Default captcha type (optional, image or audio, defaults to image)
    default_size: "normal" #Default size (optional, normal, compact or invisible, defaults to normal)
    default_badge: "bottomright" #Default badge position (bottomright, bottomleft or inline, defaults to bottomright)
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
If you want to add a field label or help text to the Captcha field you can do so like this:
```php
$form->enableSpamProtection()
	->fields()->fieldByName('Captcha')
	->setTitle("Spam protection")
	->setDescription("Please tick the box to prove you're a human and help us stop spam.");
```

### Commenting Module
When your using the [silverstripe/comments](https://github.com/silverstripe/silverstripe-comments) module you must add the following (per their documentation) to your \_config.php in order to use nocaptcha/spamprotection on comment forms.

```php
CommentingController::add_extension('CommentSpamProtection');
```


## Reporting an issue
When you're reporting an issue please ensure you specify what version of SilverStripe you are using i.e. 3.1.3, 3.2beta, master etc. Also be sure to include any JavaScript or PHP errors you receive, for PHP errors please ensure you include the full stack trace. Also please include how you produced the issue. You may also be asked to provide some of the classes to aid in re-producing the issue. Stick with the issue, remember that you seen the issue not the maintainer of the module so it may take allot of questions to arrive at a fix or answer.
