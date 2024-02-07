<?php
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

namespace MageMe\WebFormsZoho\Helper;

use Exception;
use InvalidArgumentException;
use MageMe\WebFormsZoho\Helper\Zoho\Api;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ZohoHelper
{
    const CONFIG_DOMAIN = 'webforms/zoho/domain';
    const CONFIG_CLIENT_ID = 'webforms/zoho/client_id';
    const CONFIG_CLIENT_SECRET = 'webforms/zoho/client_secret';
    const CONFIG_CODE = 'webforms/zoho/code';
    const CONFIG_REFRESH_TOKEN = 'webforms/zoho/refresh_token';
    const CONFIG_ACCESS_TOKEN = 'webforms/zoho/access_token';
    const CONFIG_ACCESS_TOKEN_TIMESTAMP = 'webforms/zoho/access_token_timestamp';
    const CONFIG_API_DOMAIN = 'webforms/zoho/api_domain';

    /**
     * @var Api
     */
    private $api;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ConfigCollectionFactory
     */
    private $configCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Api $api
     * @param ConfigCollectionFactory $configCollectionFactory
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Api        $api,
        ConfigCollectionFactory $configCollectionFactory,
        Config                  $config,
        ScopeConfigInterface    $scopeConfig
    ) {
        $this->scopeConfig             = $scopeConfig;
        $this->config                  = $config;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->api                     = $api
            ->setAuthDomain($this->getConfigDomain())
            ->setClientId($this->getConfigClientId())
            ->setClientSecret($this->getConfigClientSecret())
            ->setRefreshToken($this->getConfigRefreshToken());
    }

    /**
     * @return string|null
     */
    protected function getConfigDomain(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_DOMAIN);
    }

    /**
     * @return string|null
     */
    protected function getConfigClientId(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_CLIENT_ID);
    }

    /**
     * @return string|null
     */
    protected function getConfigClientSecret(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_CLIENT_SECRET);
    }

    /**
     * @return string|null
     */
    public function getConfigCode(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_CODE);
    }

    /**
     * @return string|null
     */
    protected function getConfigRefreshToken(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_REFRESH_TOKEN);
    }

    /**
     * @return string|null
     */
    protected function getConfigAccessToken(): ?string
    {
        return $this->getRawValue(self::CONFIG_ACCESS_TOKEN);
    }

    /**
     * @param string|null $accessToken
     * @return ZohoHelper
     */
    protected function setConfigAccessToken(?string $accessToken): ZohoHelper
    {
        $this->config->saveConfig(self::CONFIG_ACCESS_TOKEN, $accessToken);
        return $this;
    }

    /**
     * @return string|null
     */
    protected function getConfigAccessTokenTimestamp(): ?string
    {
        return $this->getRawValue(self::CONFIG_ACCESS_TOKEN_TIMESTAMP);
    }

    /**
     * @param int|null $accessTokenTimestamp
     * @return ZohoHelper
     */
    protected function setConfigAccessTokenTimestamp(?int $accessTokenTimestamp): ZohoHelper
    {
        $this->config->saveConfig(self::CONFIG_ACCESS_TOKEN_TIMESTAMP, $accessTokenTimestamp);
        return $this;
    }

    /**
     * @return string|null
     */
    protected function getConfigApiDomain(): ?string
    {
        return $this->getRawValue(self::CONFIG_API_DOMAIN);
    }

    /**
     * @param string|null $apiDomain
     * @return ZohoHelper
     */
    protected function setConfigApiDomain(?string $apiDomain): ZohoHelper
    {
        $this->config->saveConfig(self::CONFIG_API_DOMAIN, $apiDomain);
        return $this;
    }

    /**
     * @return Api
     * @throws Exception
     */
    public function getApi($validateConfig = true): ?Api
    {
        if ($validateConfig) {
            $this->validateConfig();
        }
        $accessToken          = $this->getConfigAccessToken();
        $accessTokenTimestamp = (int)$this->getConfigAccessTokenTimestamp();
        $apiDomain            = $this->getConfigApiDomain();
        if (empty($accessToken) || empty($apiDomain) || time() - $accessTokenTimestamp > 3000) {
            $this->api->refreshAccessToken();
            $accessToken          = $this->api->getAccessToken();
            $accessTokenTimestamp = $this->api->getAccessTokenTimestamp();
            $apiDomain            = $this->api->getApiDomain();
            $this->setConfigAccessToken($accessToken);
            $this->setConfigAccessTokenTimestamp($accessTokenTimestamp);
            $this->setConfigApiDomain($apiDomain);
        }
        $this->api->setAccessToken($accessToken);
        $this->api->setAccessTokenTimestamp($accessTokenTimestamp);
        $this->api->setApiDomain($apiDomain);
        return $this->api;
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateConfig()
    {
        if (empty($this->getConfigDomain())) {
            throw new InvalidArgumentException(__('Zoho domain not configured.'));
        }
        if (empty($this->getConfigClientId())) {
            throw new InvalidArgumentException(__('Zoho client id not configured.'));
        }
        if (empty($this->getConfigClientSecret())) {
            throw new InvalidArgumentException(__('Zoho client secret not configured.'));
        }
        if (empty($this->getConfigRefreshToken())) {
            throw new InvalidArgumentException(__('Zoho token not configured.'));
        }
    }

    /**
     * @param string $configPath
     * @return mixed|string
     */
    protected function getRawValue(string $configPath)
    {
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => $configPath]);
        return $collection->count() ? $collection->getFirstItem()->getData()['value'] : '';
    }
}