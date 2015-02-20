<?php
class NocaptchaField extends FormField {
    /**
     * Recaptcha Site Key
     * @config NocaptchaField.site_key
     */
    private static $site_key;
    
    /**
     * Recaptcha Secret Key
     * @config NocaptchaField.secret_key
     */
    private static $secret_key;
    
    /**
     * CURL Proxy Server location
     * @config NocaptchaField.proxy_server
     */
    private static $proxy_server;
    
    /**
     * CURL Proxy authentication
     * @config NocaptchaField.proxy_auth
     */
    private static $proxy_auth;
    
    /**
     * Verify SSL Certificates
     * @config NocaptchaField.verify_ssl
     * @default true
     */
    private static $verify_ssl=true;
    
    /**
     * Captcha theme, currently options are light and dark
     * @var string
     * @default light
     */
    private static $default_theme='light';
    
    /**
     * Captcha type, currently options are audio and image
     * @var string
     * @default image
     */
    private static $default_type='image';
    
    /**
     * Captcha theme, currently options are light and dark
     * @var string
     */
    private $_captchaTheme;
    
    /**
     * Captcha type, currently options are audio and image
     * @var string
     */
    private $_captchaType;
    
    /**
     * Creates a new Recaptcha 2 field.
     * @param {string} $name The internal field name, passed to forms.
     * @param {string} $title The human-readable field label.
     * @param {mixed} $value The value of the field (unused)
     */
    public function __construct($name, $title=null, $value=null) {
        parent::__construct($name, $title, $value);
        
        $this->_captchaTheme=self::config()->default_theme;
        $this->_captchaType=self::config()->default_type;
    }
    
    /**
     * Adds in the requirements for the field
     * @param {array} $properties Array of properties for the form element (not used)
     * @return {string} Rendered field template
     */
    public function Field($properties=array()) {
        $siteKey=self::config()->site_key;
        $secretKey=self::config()->secret_key;
        
        if(empty($siteKey) || empty($secretKey)) {
            user_error('You must configure Nocaptcha.site_key and Nocaptcha.secret_key, you can retrieve these at https://google.com/recaptcha', E_USER_ERROR);
        }
        
        Requirements::javascript(NOCAPTCHA_BASE.'/javascript/NocaptchaField.js');
        Requirements::customScript(
            "var _noCaptchaFields=_noCaptchaFields || [];_noCaptchaFields.push('".$this->ID()."');"
        );
        Requirements::customScript(
            "(function() {\n" .
            "    var gr = document.createElement('script'); gr.type = 'text/javascript'; gr.async = true;\n" .
            "    gr.src = ('https:' == document.location.protocol ? 'https://www' : 'http://www') + " .
            "'.google.com/recaptcha/api.js?render=explicit&hl=" .
            i18n::get_lang_from_locale(i18n::get_locale()) .
            "&onload=noCaptchaFieldRender';\n" .
            "    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(gr, s);\n" .
            "})();\n",
            'NocaptchaField-lib'
        );

        return parent::Field($properties);
    }
    
    /**
     * Validates the captcha against the Recaptcha2 API
     * @param {Validator} $validator Validator to send errors to
     * @return {bool} Returns boolean true if valid false if not
     */
    public function validate($validator) {
        if(!isset($_REQUEST['g-recaptcha-response'])) {
            $validator->validationError($this->name, _t('NocaptchaField.EMPTY', '_Please answer the captcha, if you do not see the captcha you must enable JavaScript'), 'validation');
            return false;
        }
        
        if(!function_exists('curl_init')) {
            user_error('You must enable php-curl to use this field', E_USER_ERROR);
            return false;
        }
        
        $url='https://www.google.com/recaptcha/api/siteverify?secret='.self::config()->secret_key.'&response='.rawurlencode($_REQUEST['g-recaptcha-response']).'&remoteip='.rawurlencode($_SERVER['REMOTE_ADDR']);
        $ch=curl_init($url);
        $proxy_server=self::config()->proxy_server;
        if(!empty($proxy_server)){
            curl_setopt($ch, CURLOPT_PROXY, $proxy_server);
            
            $proxy_auth=self::config()->proxy_auth;
            if(!empty($proxy_auth)){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
            }
        }
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::config()->verify_ssl);
        
        $lnm=singleton('LeftAndMain');
        curl_setopt($ch, CURLOPT_USERAGENT, str_replace(',', '/', 'SilverStripe '.$lnm->CMSVersion()));
        $response=json_decode(curl_exec($ch), true);
        
        if(is_array($response)) {
            if(array_key_exists('success', $response) && $response['success']==false) {
                $validator->validationError($this->name, _t('NocaptchaField.EMPTY', '_Please answer the captcha, if you do not see the captcha you must enable JavaScript'), 'validation');
                return false;
            }
        }else {
            $validator->validationError($this->name, _t('NocaptchaField.VALIDATE_ERROR', '_Captcha could not be validated'), 'validation');
            return false;
        }
        
        
        return true;
    }
    
    /**
     * Sets the theme for this captcha
     * @param {string} $value Theme to set it to, currently the api supports light and dark
     * @return {NocaptchaField}
     */
    public function setTheme($value) {
        $this->_captchaTheme=$value;
        
        return $this;
    }
    
    /**
     * Gets the theme for this captcha
     * @return {string}
     */
    public function getCaptchaTheme() {
        return $this->_captchaTheme;
    }
    
    /**
     * Sets the type for this captcha
     * @param {string} $value Type to set it to, currently the api supports audio and image
     * @return {NocaptchaField}
     */
    public function setCaptchaType($value) {
        $this->_captchaType=$value;
        
        return $this;
    }
    
    /**
     * Gets the type for this captcha
     * @return {string}
     */
    public function getCaptchaType() {
        return $this->_captchaType;
    }
    
    /**
     * Gets the site key configured via NocaptchaField.site_key this is used in the template
     * @return {string}
     */
    public function getSiteKey() {
        return self::config()->site_key;
    }
}
?>
