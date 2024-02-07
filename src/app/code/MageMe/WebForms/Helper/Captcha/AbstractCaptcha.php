<?php

namespace MageMe\WebForms\Helper\Captcha;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

abstract class AbstractCaptcha
{
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RemoteAddress $remoteAddress
     * @param Curl $curl
     */
    public function __construct(ScopeConfigInterface $scopeConfig, RemoteAddress $remoteAddress, Curl $curl)
    {
        $this->scopeConfig   = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
        $this->curl          = $curl;
    }

    /**
     * @return bool
     */
    public abstract function isConfigured(): bool;

    /**
     * @param string $response
     * @return bool
     */
    public abstract function verify(string $response): bool;

    /**
     * @return string
     */
    public abstract function getResponseName(): string;

    /**
     * @return string
     */
    public function getValidationFailureMessage(): string
    {
        return 'Captcha verification failed.';
    }

    /**
     * @return string
     */
    public function getTechnicalFailureMessage(): string
    {
        return 'Something went wrong with captcha. Please contact the store owner.';
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getCurlData(string $url)
    {
        $this->curl->setOption(CURLOPT_URL, $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curl->setOption(CURLOPT_TIMEOUT, 10);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $this->curl->get($url);
        return $this->curl->getBody();
    }

    /**
     * @param string $url
     * @param string|array $params
     * @return string
     */
    protected function postCurlData(string $url, $params)
    {
        $this->curl->setOption(CURLOPT_URL, $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curl->setOption(CURLOPT_TIMEOUT, 10);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $this->curl->post($url, $params);
        return $this->curl->getBody();
    }
}