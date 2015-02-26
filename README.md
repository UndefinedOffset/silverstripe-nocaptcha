Nocaptcha
=================
A spam protector and form field using the new Google's reCAPTCHA 2 aka [No Captcha](http://googleonlinesecurity.blogspot.ca/2014/12/are-you-robot-introducing-no-captcha.html).

## Requirements
* SilverStripe 3.1.x
* [SilverStripe Spam Protection 1.2.x](https://github.com/silverstripe/silverstripe-spamprotection/)
* PHP CURL

## Installation
* Download the module from [here](https://github.com/UndefinedOffset/silverstripe-nocaptcha/archive/master.zip)
* Extract the downloaded archive into your site root so that the destination folder is called nocaptcha, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest

If you prefer you may also install using composer:
```
composer require undefinedoffset/silverstripe-nocaptcha
```

After installing the module via composer or manual install you must set the spam protector to NocaptchaProtector, this needs to be set in your site's config file normally this is mysite/_config/config.yml.
```yml
FormSpamProtectionExtension:
    default_spam_protector: NocaptchaProtector
```


## Configuration
There are multiple configuration options for the field, you must set the site_key and the secret_key which you can get from the [reCAPTCHA page](https://www.google.com/recaptcha). These configuration options must be added to your site's yaml config typically this is mysite/_config/config.yml.
```yml
NocaptchaField:
    site_key: "YOUR_SITE_KEY" #Your site key (required)
    secret_key: "YOUR_SECRET_KEY" #Your secret key (required)
    verify_ssl: true #Allows you to disable php-curl's SSL peer verification by setting this to false (optional, defaults to true)
    default_theme: "light" #Default theme color (optional, light or dark, defaults to light)
    default_type: "image" #Default captcha type (optional, image or audio, defaults to image)
    proxy_server: "" #Your proxy server address (optional)
    proxy_auth: "" #Your proxy server authentication information (optional)
```

## Adding field labels
If you want to add a field label or help text to the Captcha field you can do so like this:
```php
$form->enableSpamProtection()
	->fields()->fieldByName('Captcha')
	->setTitle("Spam protection")
	->setDescription("Please tick the box to prove you're a human and help us stop spam.");
```

## Reporting an issue
When you're reporting an issue please ensure you specify what version of SilverStripe you are using i.e. 3.1.3, 3.2beta, master etc. Also be sure to include any JavaScript or PHP errors you receive, for PHP errors please ensure you include the full stack trace. Also please include how you produced the issue. You may also be asked to provide some of the classes to aid in re-producing the issue. Stick with the issue, remember that you seen the issue not the maintainer of the module so it may take allot of questions to arrive at a fix or answer.
