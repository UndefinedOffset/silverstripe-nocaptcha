<?php
class NocaptchaProtector implements SpamProtector {
    /**
     * Return the Field that we will use in this protector
     * @return string
     */
    public function getFormField($name="Recaptcha2Field", $title='Captcha', $value=null) {
        return new NocaptchaField($name, $title);
    }
    
    /**
     * Not used by Nocaptcha
     */
    public function setFieldMapping($fieldMapping) {}
}
?>