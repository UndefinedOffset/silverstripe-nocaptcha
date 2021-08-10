<?php
namespace UndefinedOffset\NoCaptcha\Forms;

use SilverStripe\SpamProtection\SpamProtector;


class NocaptchaProtector implements SpamProtector {
    /**
     * Return the Field that we will use in this protector
     * @return NocaptchaField
     */
    public function getFormField($name="Recaptcha2Field", $title='Captcha', $value=null) {
        return NocaptchaField::create($name, $title);
    }
    
    /**
     * Not used by Nocaptcha
     */
    public function setFieldMapping($fieldMapping) {}
}
