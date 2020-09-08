<?php
namespace UndefinedOffset\NoCaptcha\Forms;

use Locale;
use SilverStripe\i18n\i18n;
use Psr\Log\LoggerInterface;
use SilverStripe\Forms\FormField;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;


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
     * Recaptcha version (2|3)
     * @config NocaptchaField.recaptcha_version
     */
    private static $recaptcha_version = 2;

    /**
     * Reject spam under this score
     *
     * @config NocaptchaField.minimum_score
     */
    private static $minimum_score = 0.4;

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
     * CURL Proxy port
     * @config NocaptchaField.proxy_port
     */
    private static $proxy_port;

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
     * Captcha size, currently options are normal, compact and invisible
     * @var string
     * @default normal
     */
    private static $default_size='normal';

    /**
     * Recaptcha Site Key
     * Configurable via Injector config
     */
    protected $_siteKey;

    /**
     * Recaptcha Site Key
     * Configurable via Injector config
     */
    protected $_secretKey;

    /**
     * CURL Proxy Server location
     * Configurable via Injector config
     */
    protected $_proxyServer;

    /**
     * CURL Proxy authentication
     * Configurable via Injector config
     */
    protected $_proxyAuth;

    /**
     * CURL Proxy port
     * Configurable via Injector config
     */
    protected $_proxyPort;

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
     * Captcha size, currently options are normal and compact
     * @var string
     */
    private $_captchaSize;

    /**
     * Captcha badge, currently options are bottomright, bottomleft and inline
     * @var string
     */
    private $_captchaBadge;

    /**
     * The verification response
     *
     * @var array
     */
    protected $verifyResponse;

    /**
     * Minimum score for this instance (0.0 = spam, 1.0 = good)
     *
     * @var float
     */
    protected $minimumScore;

    /**
     * Creates a new Recaptcha 2 field.
     * @param string $name The internal field name, passed to forms.
     * @param string $title The human-readable field label.
     * @param mixed $value The value of the field (unused)
     */
    public function __construct($name, $title=null, $value=null) {
        parent::__construct($name, $title, $value);

        $this->title=$title;

        $this->_captchaTheme=self::config()->default_theme;
        $this->_captchaType=self::config()->default_type;
        $this->_captchaSize=self::config()->default_size;
        $this->_captchaBadge=self::config()->default_badge;
    }

    /**
     * Adds in the requirements for the field
     * @param array $properties Array of properties for the form element (not used)
     * @return string Rendered field template
     */
    public function Field($properties=array()) {
        $siteKey=$this->getSiteKey();
        $secretKey=$this->_secretKey ? $this->_secretKey : self::config()->secret_key;

        if(empty($siteKey) || empty($secretKey)) {
            user_error('You must configure Nocaptcha.site_key and Nocaptcha.secret_key, you can retrieve these at https://google.com/recaptcha', E_USER_ERROR);
        }

        if ($this->config()->get('recaptcha_version') == 2) {
            Requirements::javascript('undefinedoffset/silverstripe-nocaptcha:javascript/NocaptchaField.js');
            Requirements::customScript(
                "var _noCaptchaFields=_noCaptchaFields || [];_noCaptchaFields.push('".$this->ID()."');",
                "NocaptchaField-" . $this->ID()
            );
            Requirements::customScript(
                "(function() {\n" .
                    "var gr = document.createElement('script'); gr.type = 'text/javascript'; gr.async = true;\n" .
                    "gr.src = ('https:' == document.location.protocol ? 'https://www' : 'http://www') + " .
                    "'.google.com/recaptcha/api.js?render=explicit&hl=" .
                    Locale::getPrimaryLanguage(i18n::get_locale()) .
                    "&onload=noCaptchaFieldRender';\n" .
                    "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(gr, s);\n" .
                "})();\n",
                'NocaptchaField-lib'
            );
        } else {
            Requirements::javascript('https://www.google.com/recaptcha/api.js');
            Requirements::customCSS('.nocaptcha { display: none !important; }', self::class);

            $form = $this->getForm();
            $helper = $form->getTemplateHelper();
            $id = $helper->generateFormID($form);

            Requirements::customScript("
                function nocaptchaCallback(token) {
                    document.getElementById('". $id ."').submit();
                }",
                'NocaptchaField-submit'
            );

            $action = $form->defaultAction();

            if ($action) {
                $action->addExtraClass('g-recaptcha');
                $action->setAttribute('data-sitekey', $siteKey);
                $action->setAttribute('data-callback', 'nocaptchaCallback');
                $action->setAttribute('data-action', 'submit');
            }
        }

        return parent::Field($properties);
    }

    /**
     * Validates the captcha against the Recaptcha API
     *
     * @param Validator $validator Validator to send errors to
     * @return bool Returns boolean true if valid false if not
     */
    public function validate($validator) {

        $recaptchaResponse = Controller::curr()->getRequest()->requestVar('g-recaptcha-response');

        if(!isset($recaptchaResponse)) {
            $validator->validationError($this->name, _t('UndefinedOffset\\NoCaptcha\\Forms\\NocaptchaField.EMPTY', '_Please answer the captcha, if you do not see the captcha you must enable JavaScript'), 'validation');
            return false;
        }

        if(!function_exists('curl_init')) {
            user_error('You must enable php-curl to use this field', E_USER_ERROR);
            return false;
        }

        $secret_key=$this->_secretKey ?: self::config()->secret_key;
        $url='https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.rawurlencode($recaptchaResponse).'&remoteip='.rawurlencode($_SERVER['REMOTE_ADDR']);
        $ch=curl_init($url);
        $proxy_server=$this->_proxyServer ?: self::config()->proxy_server;
        if(!empty($proxy_server)){
            curl_setopt($ch, CURLOPT_PROXY, $proxy_server);

            $proxy_auth=$this->_proxyAuth ?: self::config()->proxy_auth;
            if(!empty($proxy_auth)){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
            }

            $proxy_port=$this->_proxyPort ?: self::config()->proxy_port;
            if(!empty($proxy_port)){
                curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
            }
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::config()->verify_ssl);
        curl_setopt($ch, CURLOPT_USERAGENT, str_replace(',', '/', 'SilverStripe'));
        $response=json_decode(curl_exec($ch), true);

        if(is_array($response)) {
            $this->verifyResponse = $response;

            if(array_key_exists('success', $response) && $response['success']==false) {
                $validator->validationError($this->name, _t('UndefinedOffset\\NoCaptcha\\Forms\\NocaptchaField.EMPTY', '_Please answer the captcha, if you do not see the captcha you must enable JavaScript'), 'validation');
                return false;
            }

            if ($this->config()->get('recaptcha_version') == 3) {
                $minimum = $this->getMinimumScore();

                if (array_key_exists('score', $response) && $response['score'] <= $minimum) {
                    $validator->validationError($this->name, _t('UndefinedOffset\\NoCaptcha\\Forms\\NocaptchaField.SPAM', 'Your submission has been marked as spam'), 'validation');

                    return false;
                }
            }
        } else {
            $validator->validationError($this->name, _t('UndefinedOffset\\NoCaptcha\\Forms\\NocaptchaField.VALIDATE_ERROR', '_Captcha could not be validated'), 'validation');
            $logger = Injector::inst()->get(LoggerInterface::class);
            $logger->error(
                'Captcha validation failed as request was not successful.'
            );
            return false;
        }


        return true;
    }

    /**
     * Sets the theme for this captcha
     * @param string $value Theme to set it to, currently the api supports light and dark
     * @return NocaptchaField
     */
    public function setTheme($value) {
        $this->_captchaTheme=$value;

        return $this;
    }

    /**
     * Gets the theme for this captcha
     * @return string
     */
    public function getCaptchaTheme() {
        return $this->_captchaTheme;
    }

    /**
     * Sets the type for this captcha
     * @param string $value Type to set it to, currently the api supports audio and image
     * @return NocaptchaField
     */
    public function setCaptchaType($value) {
        $this->_captchaType=$value;

        return $this;
    }

    /**
     * Gets the type for this captcha
     * @return string
     */
    public function getCaptchaType() {
        return $this->_captchaType;
    }


    /**
     * Sets the size for this captcha
     * @param string $value Size to set it to, currently the api supports normal, compact and invisible
     * @return NocaptchaField
     */
    public function setCaptchaSize($value) {
        $this->_captchaSize=$value;

        return $this;
    }

    /**
     * Gets the size for this captcha
     * @return string
     */
    public function getCaptchaSize() {
        return $this->_captchaSize;
    }

    /**
     * Sets the badge position for this captcha
     * @param string $value Badge to set it to, currently the api supports bottomright, bottomleft or inline
     * @return NocaptchaField
     */
    public function setCaptchaBadge($value) {
        $this->_captchaBadge=$value;

        return $this;
    }

    /**
     * Gets the Badge position for this captcha
     * @return string
     */
    public function getCaptchaBadge() {
        return $this->_captchaBadge;
    }

    /**
     * Gets the site key configured via NocaptchaField.site_key this is used in the template
     * @return string
     */
    public function getSiteKey() {
        return $this->_sitekey ? $this->_sitekey : self::config()->site_key;
    }

    /**
     * Setter for _siteKey to allow injector config to override the value
     */
    public function setSiteKey($key) {
        $this->_sitekey=$key;
    }

    /**
     * Setter for _secretKey to allow injector config to override the value
     */
    public function setSecretKey($key) {
        $this->_secretKey=$key;
    }

    /**
     * Setter for _proxyServer to allow injector config to override the value
     */
    public function setProxyServer($server) {
        $this->_proxyServer=$server;
    }

    /**
     * Setter for _proxyAuth to allow injector config to override the value
     */
    public function setProxyAuth($auth) {
        $this->_proxyAuth=$auth;
    }

    /**
     * Setter for _proxyPort to allow injector config to override the value
     */
    public function setProxyPort($port) {
        $this->_proxyPort=$port;
    }

    /**
     * Gets the form's id
     * @return string
     */
    public function getFormID() {
        return ($this->form ? $this->getTemplateHelper()->generateFormID($this->form):null);
    }

    /**
     * @return array
     */
    public function getVerifyResponse()
    {
        return $this->verifyResponse;
    }

    /**
     * @param float $minimumScore
     *
     * @return self
     */
    public function setMinimumScore($minimumScore)
    {
        $this->minimumScore = $minimumScore;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinimumScore()
    {
        if ($this->minimumScore) {
            return $this->minimumScore;
        }

        return $this->config()->get('minimum_score');
    }
}
