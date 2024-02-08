<?php /** @noinspection PhpMissingFieldTypeInspection */

/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Helper;

use MageMe\WebForms\Config\Options\Captcha\Type;
use MageMe\WebForms\Helper\Captcha\AbstractCaptcha;
use MageMe\WebForms\Helper\Captcha\Hcaptcha;
use MageMe\WebForms\Helper\Captcha\Recaptcha;
use MageMe\WebForms\Helper\Captcha\Turnstile;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;


class CaptchaHelper
{
    const PATH = 'webforms/captcha';
    const TYPE = self::PATH . '/type';

    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $captchaType;
    /**
     * @var AbstractCaptcha
     */
    private $captcha;
    /**
     * @var Random
     */
    private $random;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Random $random
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface   $scopeConfig,
        Random                 $random
    ) {
        $this->random        = $random;
        $this->scopeConfig   = $scopeConfig;
        $this->objectManager = $objectManager;
        switch ($this->getCaptchaType()) {
            case Type::HCAPTCHA:
            {
                $this->captcha = $this->objectManager->create(Hcaptcha::class);
                break;
            }
            case Type::TURNSTILE:
            {
                $this->captcha = $this->objectManager->create(Turnstile::class);
                break;
            }
            default:
            {
                $this->captcha = $this->objectManager->create(Recaptcha::class);
            }
        }
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getId(): string
    {
        if (!$this->id) {
            $this->id = $this->random->getRandomString(6);
        }
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCaptchaType(): string
    {
        if (!$this->captchaType) {
            $this->captchaType = (string)$this->scopeConfig->getValue(self::TYPE, ScopeInterface::SCOPE_STORE);
        }
        return $this->captchaType;
    }

    /**
     * @return AbstractCaptcha|Hcaptcha|Recaptcha|Turnstile|mixed
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->getCaptcha()->isConfigured();
    }

    /**
     * @param string $response
     * @return bool
     */
    public function verify(string $response): bool
    {
        return $this->getCaptcha()->verify($response);
    }
}
