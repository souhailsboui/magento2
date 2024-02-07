<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Helper;

use Exception;
use Magento\Config\Model\ResourceModel\Config as ModelConfig;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\ZohoCRM\Model\Source\DomainUsers;
use Laminas\Http\Request;

/**
 * Class Data
 * @package Mageplaza\ZohoCRM\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH  = 'zohoCRM';
    const ACCOUNT_ZOHO_URL_US = 'https://accounts.zoho.com/';
    const ACCOUNT_ZOHO_URL_EU = 'https://accounts.zoho.eu/';
    const ACCOUNT_ZOHO_URL_CN = 'https://accounts.zoho.cn/';
    const ACCOUNT_ZOHO_URL_IN = 'https://accounts.zoho.in/';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var ModelConfig
     */
    protected $_modelConfig;

    /**
     * @var ConfigCollectionFactory
     */
    protected $configCollectionFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMeta;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param CurlFactory $curlFactory
     * @param ModelConfig $modelConfig
     * @param ConfigCollectionFactory $collectionFactory
     * @param ProductMetadataInterface $productMeta
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        CurlFactory $curlFactory,
        ModelConfig $modelConfig,
        ConfigCollectionFactory $collectionFactory,
        ProductMetadataInterface $productMeta
    ) {
        $this->encryptor               = $encryptor;
        $this->curlFactory             = $curlFactory;
        $this->_modelConfig            = $modelConfig;
        $this->configCollectionFactory = $collectionFactory;
        $this->productMeta             = $productMeta;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param string $path
     * @param string $value
     */
    public function saveConfig($path, $value)
    {
        $this->_modelConfig->saveConfig($path, $value);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return parent::isEnabled($storeId) && $this->getAccessToken();
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function decrypt($value)
    {
        return $this->encryptor->decrypt($value);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        $clientId = $this->getConfigGeneral('client_id');

        return $this->decrypt($clientId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        $clientSecret = $this->getConfigGeneral('client_secret');

        return $this->decrypt($clientSecret);
    }

    /**
     * @return string
     */
    public function getRedirectURIs()
    {
        return $this->getConfigGeneral('redirect_URIs');
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        $config = $this->configCollectionFactory->create()
            ->addFieldToFilter('path', $this->getZohoGeneralPathByKey('access_token'))
            ->getFirstItem();
        if ($config->getValue()) {
            return $this->decrypt($config->getValue());
        }

        return '';
    }

    /**
     * Load config without cache
     *
     * @return string
     */
    public function getLastRequestToken()
    {
        $config = $this->configCollectionFactory->create()
            ->addFieldToFilter('path', $this->getZohoGeneralPathByKey('last_request_token'))
            ->getFirstItem();
        if ($config->getId()) {
            return $config->getValue();
        }

        return '';
    }

    /**
     * Load config without cache
     *
     * @return string
     */
    public function getSchedule()
    {
        return $this->getConfigSchedule('schedule');
    }

    /**
     * Load config without cache
     *
     * @return string
     */
    public function getLastSchedule()
    {
        $config = $this->configCollectionFactory->create()
            ->addFieldToFilter('path', self::CONFIG_MODULE_PATH . '/queue_schedule/last_schedule')
            ->getFirstItem();
        if ($config->getId()) {
            return $config->getValue();
        }

        return '';
    }

    /**
     * @return ModelConfig
     */
    public function saveLastSchedule()
    {
        return $this->saveConfig(self::CONFIG_MODULE_PATH . '/queue_schedule/last_schedule', time());
    }

    /**
     * @return array|mixed
     */
    public function getAccessData()
    {
        $accessData = $this->getConfigGeneral('access_data');

        if ($accessData) {
            return self::jsonDecode($this->decrypt($accessData));
        }

        return [];
    }

    /**
     * @return mixed|string
     */
    public function getApiDomain()
    {
        $accessData = $this->getAccessData();
        if (isset($accessData['api_domain'])) {
            return $accessData['api_domain'] . '/crm/v2/';
        }

        return '';
    }

    /**
     * @return mixed|string
     */
    public function getRefreshToken()
    {
        $accessData = $this->getAccessData();
        if (isset($accessData['refresh_token'])) {
            return $accessData['refresh_token'];
        }

        return '';
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigSchedule($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/queue_schedule' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getDeveloperMode($storeId = null)
    {
        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/developer/mode', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getLimitObjectSend($storeId = null)
    {
        return $this->getConfigSchedule('number_of_obj_per_time', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDeleteAfter($storeId = null)
    {
        return $this->getConfigSchedule('delete_after', $storeId);
    }

    /**
     * @return string
     */
    public function getTokenURL()
    {
        return $this->getAccountZohoUrl() . 'oauth/v2/token';
    }

    /**
     * @return string
     */
    public function getAPIProductURL()
    {
        return $this->getApiDomain() . 'products';
    }

    /**
     * @return string
     */
    public function getAPICampaignURL()
    {
        return $this->getApiDomain() . 'campaigns';
    }

    /**
     * @return string
     */
    public function getAPILeadURL()
    {
        return $this->getApiDomain() . 'leads';
    }

    /**
     * @return string
     */
    public function getAPIContactURL()
    {
        return $this->getApiDomain() . 'contacts';
    }

    /**
     * @return string
     */
    public function getAPIAccountURL()
    {
        return $this->getApiDomain() . 'accounts';
    }

    /**
     * @return string
     */
    public function getAPIOrderURL()
    {
        return $this->getApiDomain() . 'purchase_orders';
    }

    /**
     * @return string
     */
    public function getAPIInvoiceURL()
    {
        return $this->getApiDomain() . 'invoices';
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getZohoGeneralPathByKey($field)
    {
        return self::CONFIG_MODULE_PATH . '/general/' . $field;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorizedRedirectURIs()
    {
        $storeId = $this->getScopeUrl();
        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);

        return $this->_getUrl('mpzoho/index/token', [
            '_nosid'  => true,
            '_scope'  => $storeId,
            '_secure' => $store->isUrlSecure()
        ]);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getScopeUrl()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = $this->storeManager->getWebsite($website)->getDefaultStore()->getId();
        }

        return $scope;
    }

    /**
     * Check and refresh access token after one hour
     *
     * @throws Exception
     */
    public function checkRefreshAccessToken()
    {
        $lastRequestToken = (int) $this->getLastRequestToken();

        if ($lastRequestToken + 3600 < time()) {
            $refreshData = [
                'refresh_token' => $this->getRefreshToken(),
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'grant_type'    => 'refresh_token'
            ];

            $resp = $this->requestData(
                $this->getAccountZohoUrl() . 'oauth/v2/token',
                Request::METHOD_POST,
                http_build_query($refreshData),
                true
            );

            if (isset($resp['access_token'])) {
                $this->saveAPIData($resp, true);
            } else {
                throw new LocalizedException(__('Cannot refresh access token.'));
            }
        }
    }

    /**
     * @param array $resp
     * @param bool $isRefreshToken
     */
    public function saveAPIData($resp, $isRefreshToken = false)
    {
        if ($isRefreshToken) {
            $resp['refresh_token'] = $this->getRefreshToken();
        }

        $accessToken = $this->encryptor->encrypt($resp['access_token']);
        $accessData  = $this->encryptor->encrypt(self::jsonEncode($resp));
        $this->saveConfig($this->getZohoGeneralPathByKey('access_token'), $accessToken);
        $this->saveConfig($this->getZohoGeneralPathByKey('access_data'), $accessData);
        $this->saveConfig($this->getZohoGeneralPathByKey('last_request_token'), time());
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $params
     * @param bool $isAccessToken
     *
     * @return mixed|string
     * @throws Exception
     */
    public function sendRequest($url, $method, $params = '', $isAccessToken = false)
    {
        $this->checkRefreshAccessToken();

        return $this->requestData($url, $method, $params, $isAccessToken);
    }

    /**
     * @param string $url
     * @param string $method
     * @param string|array $params
     * @param bool $isAccessToken
     *
     * @return mixed|string
     */
    public function requestData($url, $method = Request::METHOD_POST, $params = '', $isAccessToken = false)
    {
        $httpAdapter = $this->curlFactory->create();
        if (($method === Request::METHOD_POST && !$isAccessToken) || $method === Request::METHOD_PUT) {
            $params = self::jsonEncode($params);
        }

        $headers = [];
        if (!$isAccessToken) {
            $headers = [
                'Content-Type:application/json',
                'Authorization: Zoho-oauthtoken ' . $this->getAccessToken()
            ];
        }

        if ($method === Request::METHOD_DELETE) {
            $httpAdapter->setOptions([CURLOPT_CUSTOMREQUEST => 'DELETE']);
        }
        $httpAdapter->write($method, $url, '1.1', $headers, $params);
        $result   = $httpAdapter->read();
        $response = $this->extractBody($result);
        $response = self::jsonDecode($response);
        $httpAdapter->close();

        return $response;
    }

    /**
     * @return bool
     */
    public function isEnableReportModule()
    {
        if ($this->isModuleOutputEnabled('Mageplaza_Reports')) {
            $mpReportModule = $this->objectManager->create(\Mageplaza\Reports\Helper\Data::class);

            return $mpReportModule->isEnabled();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEnterprise()
    {
        return $this->productMeta->getEdition() === 'Enterprise';
    }

    /**
     * @return mixed
     */
    public function getDomainUsers()
    {
        return $this->getConfigGeneral('domain_users');
    }

    /**
     * @return string
     */
    public function getAccountZohoUrl()
    {
        $domainUsers    = $this->getDomainUsers();
        $accountZohoUrl = self::ACCOUNT_ZOHO_URL_US;

        switch ($domainUsers) {
            case DomainUsers::EU:
                $accountZohoUrl = self::ACCOUNT_ZOHO_URL_EU;
                break;
            case DomainUsers::CN:
                $accountZohoUrl = self::ACCOUNT_ZOHO_URL_CN;
                break;
            case DomainUsers::IN:
                $accountZohoUrl = self::ACCOUNT_ZOHO_URL_IN;
                break;
        }

        return $accountZohoUrl;
    }

    /**
     * Extract the body from a response string
     *
     * @param string $response_str
     * @return string
     */
    public static function extractBody($response_str)
    {
        $parts = preg_split('|(?:\r\n){2}|m', $response_str, 2);
        if (isset($parts[1])) {
            return $parts[1];
        }
        return '';
    }
}
