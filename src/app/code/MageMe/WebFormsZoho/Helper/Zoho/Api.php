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

namespace MageMe\WebFormsZoho\Helper\Zoho;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class Api
{
    const UNEXPECTED_ERROR = 'Unexpected error';

    private $clientId;
    private $clientSecret;
    private $authDomain;
    private $refreshToken;
    private $apiDomain;
    private $deskApiDomain;
    private $accessToken;
    private $accessTokenTimestamp;

    private $refreshRetries = 0;

    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CrmApi
     */
    private $crm;
    /**
     * @var DeskApi
     */
    private $desk;

    /**
     * @param DeskApi $deskApi
     * @param CrmApi $crmApi
     * @param LoggerInterface $logger
     * @param Curl $curl
     */
    public function __construct(
        DeskApi $deskApi,
        CrmApi $crmApi,
        LoggerInterface $logger,
        Curl            $curl
    ) {
        $this->curl   = $curl;
        $this->logger = $logger;
        $this->crm = $crmApi;
        $this->desk = $deskApi;
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }

    #region Getters\Setters

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string|null $clientId
     * @return Api
     */
    public function setClientId(?string $clientId): Api
    {
        $this->clientId = $clientId;
        return $this;

    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string|null $clientSecret
     * @return Api
     */
    public function setClientSecret(?string $clientSecret): Api
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthDomain(): string
    {
        return $this->authDomain;
    }

    /**
     * @param string|null $authDomain
     * @return Api
     */
    public function setAuthDomain(?string $authDomain): Api
    {
        $this->authDomain = $authDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     * @return Api
     */
    public function setRefreshToken(?string $refreshToken): Api
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     * @return Api
     */
    public function setAccessToken(?string $accessToken): Api
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return int
     */
    public function getAccessTokenTimestamp(): int
    {
        return $this->accessTokenTimestamp;
    }

    /**
     * @param int|null $accessTokenTimestamp
     * @return Api
     */
    public function setAccessTokenTimestamp(?int $accessTokenTimestamp): Api
    {
        $this->accessTokenTimestamp = $accessTokenTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiDomain(): string
    {
        return $this->apiDomain;
    }

    /**
     * @param string|null $apiDomain
     * @return Api
     */
    public function setApiDomain(?string $apiDomain): Api
    {
        $this->apiDomain = $apiDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeskApiDomain(): string
    {
        if (empty($this->deskApiDomain) && $this->authDomain) {
            $dc = str_replace('https://accounts.zoho', '', $this->authDomain);
            $this->deskApiDomain = "https://desk.zoho$dc/api";
        }
        return $this->deskApiDomain;
    }

    /**
     * @param string|null $deskApiDomain
     * @return Api
     */
    public function setDeskApiDomain(?string $deskApiDomain): Api
    {
        $this->deskApiDomain = $deskApiDomain;
        return $this;
    }
    #endregion

    /**
     * @return CrmApi
     */
    public function CRM(): CrmApi
    {
        $this->crm->setAccessToken($this->getAccessToken());
        $this->crm->setApiDomain($this->getApiDomain());
        return $this->crm;
    }

    /**
     * @return DeskApi
     */
    public function Desk(): DeskApi
    {
        $this->desk->setAccessToken($this->getAccessToken());
        $this->desk->setDeskApiDomain($this->getDeskApiDomain());
        return $this->desk;
    }

    /**
     * @param string $code
     * @return string
     * @throws Exception
     */
    function generateRefreshToken(string $code): string
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new Exception(__('Invalid configuration'));
        }
        if (!$code) {
            throw new Exception(__('Empty code'));
        }
        $postFields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => '',
            'code' => $code,
        ];
        $authUrl    = $this->authDomain . '/oauth/v2/token';
        $this->curl->post($authUrl, $postFields);
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(self::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(self::UNEXPECTED_ERROR));
        }
        if (isset($response['error'])) {
            $this->logger->error($response['error'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['error']));
        }
        $this->accessToken          = $response['access_token'];
        $this->accessTokenTimestamp = time();
        $this->refreshToken         = $response['refresh_token'];
        $this->apiDomain            = $response['api_domain'];
        return $this->refreshToken;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function refreshAccessToken(): Api
    {
        if (!$this->clientId || !$this->clientSecret || !$this->refreshToken) {
            throw new Exception(__('Invalid configuration'));
        }
        $postFields = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
        ];
        $authUrl    = $this->authDomain . '/oauth/v2/token';
        $this->curl->post($authUrl, $postFields);
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(self::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(self::UNEXPECTED_ERROR));
        }
        if (isset($response['error'])) {
            $this->logger->error($response['error'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['error']));
        }
        if (!isset($response['access_token'])) {
            if ($this->refreshRetries < 3) {
                $this->logger->warning('No access token: ' . $this->curl->getBody());
                $this->refreshRetries++;
                return $this->refreshAccessToken();
            } else {
                $this->logger->error(self::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
                throw new Exception(__(self::UNEXPECTED_ERROR));
            }
        }
        $this->refreshRetries       = 0;
        $this->accessToken          = $response['access_token'];
        $this->accessTokenTimestamp = time();
        $this->apiDomain            = $response['api_domain'];
        return $this;
    }
}