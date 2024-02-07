<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace MageMe\WebForms\Helper\Captcha;

use MageMe\WebForms\Helper\CaptchaHelper;
use Magento\Store\Model\ScopeInterface;

class Hcaptcha extends AbstractCaptcha
{
    const PUBLIC_KEY = CaptchaHelper::PATH . '/hcaptcha/public_key';
    const PRIVATE_KEY = CaptchaHelper::PATH . '/hcaptcha/private_key';
    const THEME = CaptchaHelper::PATH . '/hcaptcha/theme';
    const SIZE = CaptchaHelper::PATH . '/hcaptcha/size';
    const SCORE_THRESHOLD = CaptchaHelper::PATH . '/hcaptcha/score_threshold';
    const VALIDATION_FAILURE_MESSAGE = CaptchaHelper::PATH . '/hcaptcha/validation_failure_message';
    const TECHNICAL_FAILURE_MESSAGE = CaptchaHelper::PATH . '/hcaptcha/technical_failure_message';
    /**
     * @var string
     */
    private $publicKey;
    /**
     * @var string
     */
    private $privateKey;
    /**
     * @var string
     */
    private $theme;
    /**
     * @var string
     */
    private $size;
    /**
     * @var float
     */
    private $scoreThreshold;

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        if (!$this->publicKey) {
            $this->publicKey = (string)$this->scopeConfig->getValue( self::PUBLIC_KEY,ScopeInterface::SCOPE_STORE);
        }
        return $this->publicKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPublicKey(string $value): Hcaptcha
    {
        $this->publicKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (!$this->privateKey) {
            $this->privateKey = (string)$this->scopeConfig->getValue( self::PRIVATE_KEY,ScopeInterface::SCOPE_STORE);
        }
        return $this->privateKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPrivateKey(string $value): Hcaptcha
    {
        $this->privateKey = $value;
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
        return $this->theme;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTheme(string $value): Hcaptcha
    {
        $this->theme = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        if (!$this->size) {
            $this->size = (string)$this->scopeConfig->getValue(self::SIZE, ScopeInterface::SCOPE_STORE);
        }
        return $this->size;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSize(string $value): Hcaptcha
    {
        $this->size = $value;
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
        return $this->scoreThreshold;
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
     * @inheritDoc
     */
    public function verify(string $response): bool
    {
        //Get user ip
        $ip = $this->remoteAddress->getRemoteAddress();

        //Build up the url
        $url      = 'https://api.hcaptcha.com/siteverify';
        $full_url = $url . '?secret=' . $this->getPrivateKey() . '&response=' . $response . '&remoteip=' . $ip;

        //Get the response back decode the json
        $data = json_decode($this->getCurlData($full_url));

        if (isset($data->score) && (float)$data->score > $this->getScoreThreshold())
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
        return 'h-captcha-response';
    }
}