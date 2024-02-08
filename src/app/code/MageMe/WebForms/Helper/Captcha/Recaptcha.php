<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace MageMe\WebForms\Helper\Captcha;

use MageMe\WebForms\Helper\CaptchaHelper;
use Magento\Store\Model\ScopeInterface;

class Recaptcha extends AbstractCaptcha
{
    const V2_PUBLIC_KEY = CaptchaHelper::PATH . '/recaptcha/public_key';
    const V2_PRIVATE_KEY = CaptchaHelper::PATH . '/recaptcha/private_key';
    const V3_PUBLIC_KEY = CaptchaHelper::PATH . '/recaptcha/public_key3';
    const V3_PRIVATE_KEY = CaptchaHelper::PATH . '/recaptcha/private_key3';
    const RECAPTCHA_VERSION = CaptchaHelper::PATH . '/recaptcha/recaptcha_version';
    const POSITION = CaptchaHelper::PATH . '/recaptcha/position';
    const THEME = CaptchaHelper::PATH . '/recaptcha/theme';
    const SCORE_THRESHOLD = CaptchaHelper::PATH . '/recaptcha/score_threshold';
    const VALIDATION_FAILURE_MESSAGE = CaptchaHelper::PATH . '/recaptcha/validation_failure_message';
    const TECHNICAL_FAILURE_MESSAGE = CaptchaHelper::PATH . '/recaptcha/technical_failure_message';

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var float
     */
    private $scoreThreshold;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $theme;

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        if (!$this->publicKey) {
            $this->publicKey =
                (string)$this->scopeConfig->getValue(
                    $this->getVersion() == '3' ? self::V3_PUBLIC_KEY : self::V2_PUBLIC_KEY,
                    ScopeInterface::SCOPE_STORE);
        }
        return $this->publicKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPublicKey(string $value): Recaptcha
    {
        $this->publicKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        if (!$this->version) {
            $this->version = (string)$this->scopeConfig->getValue(self::RECAPTCHA_VERSION, ScopeInterface::SCOPE_STORE);
        }
        return $this->version;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): Recaptcha
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (!$this->privateKey) {
            $this->privateKey =
                (string)$this->scopeConfig->getValue(
                    $this->getVersion() == '3' ? self::V3_PRIVATE_KEY : self::V2_PRIVATE_KEY,
                    ScopeInterface::SCOPE_STORE);
        }
        return $this->privateKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPrivateKey(string $value): Recaptcha
    {
        $this->privateKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        if (!$this->position) {
            $this->position = (string)$this->scopeConfig->getValue(self::POSITION, ScopeInterface::SCOPE_STORE);
        }
        if ($this->getVersion() == 2) return 'inline';
        return $this->position ?? 'inline';
    }

    /**
     * @param string $position
     * @return $this
     */
    public function setPosition(string $position): Recaptcha
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        if (!$this->theme) {
            $this->theme = (string)$this->scopeConfig->getValue(self::THEME, ScopeInterface::SCOPE_STORE);
        }
        return $this->theme ?? 'standard';
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTheme(string $value): Recaptcha
    {
        $this->theme = $value;
        return $this;
    }

    /**
     * @return float
     */
    public function getScoreThreshold(): float
    {
        if (!$this->scoreThreshold) {
            $this->scoreThreshold = (float)$this->scopeConfig->getValue(self::SCORE_THRESHOLD, ScopeInterface::SCOPE_STORE);
        }
        if ($this->getVersion() == 2) return 0.5;
        return $this->scoreThreshold ?? 0.5;
    }

    /**
     * @inheritDoc
     */
    public function getValidationFailureMessage(): string
    {
        return (string)$this->scopeConfig->getValue(self::VALIDATION_FAILURE_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritDoc
     */
    public function getTechnicalFailureMessage(): string
    {
        return (string)$this->scopeConfig->getValue(self::TECHNICAL_FAILURE_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $response
     * @return bool
     */
    public function verify(string $response): bool
    {
        //Get user ip
        $ip = $this->remoteAddress->getRemoteAddress();

        //Build up the url
        $url      = 'https://www.google.com/recaptcha/api/siteverify';
        $full_url = $url . '?secret=' . $this->getPrivateKey() . '&response=' . $response . '&remoteip=' . $ip;

        //Get the response back decode the json
        $data = json_decode($this->getCurlData($full_url));

        if (isset($data->score) && (float)$data->score <= $this->getScoreThreshold())
            return false;

        //Return true or false, based on users input
        if (isset($data->success) && $data->success) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isConfigured(): bool
    {
        return $this->getPublicKey() && $this->getPrivateKey();
    }

    /**
     * @inheritDoc
     */
    public function getResponseName(): string
    {
        return 'g-recaptcha-response';
    }
}