<?php

namespace Biztech\Ausposteparcel\Observer;

use Biztech\Ausposteparcel\Helper\Data;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Zend\Json\Json;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;

class CheckKey implements ObserverInterface
{
    const XML_PATH_ACTIVATIONKEY = 'ausposteParcel/activation/key';
    const XML_PATH_DATA = 'ausposteParcel/activation/data';

    protected $scopeConfig;
    protected $encryptor;
    protected $configFactory;
    protected $helper;
    protected $objectManager;
    protected $request;
    protected $resourceConfig;
    protected $configModel;
    protected $configValueFactory;
    protected $zend;
    protected $_cacheFrontendPool;
    protected $_cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param Factory $configFactory
     * @param Data $helper
     * @param ObjectManagerInterface $objectmanager
     * @param RequestInterface $request
     * @param Json $zend
     * @param ResourceConfig $resourceConfig
     * @param ValueFactory $configValueFactory
     * @param Config $configModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Factory $configFactory,
        Data $helper,
        ObjectManagerInterface $objectmanager,
        RequestInterface $request,
        Json $zend,
        ResourceConfig $resourceConfig,
        ValueFactory $configValueFactory,
        Config $configModel,
        Pool $cacheFrontendPool,
        TypeListInterface $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->configFactory = $configFactory;
        $this->helper = $helper;
        $this->objectManager = $objectmanager;
        $this->request = $request;
        $this->zend = $zend;
        $this->resourceConfig = $resourceConfig;
        $this->configModel = $configModel;
        $this->configValueFactory = $configValueFactory;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $this->request->getParam('groups');
        
        $k = $params['activation']['fields']['key']['value'];
        $s = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.appjetty.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->helper->getAllStoreDomains())) . '&sec=magento2-auspost-eparcel');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $content = curl_exec($ch);
        $res1 = $this->zend->decode($content);
        $res = (array)$res1;
        $moduleStatus = $this->resourceConfig;
        if (empty($res)) {
            $moduleStatus->saveConfig('ausposteParcel/activation/key', "");
            $moduleStatus->saveConfig('ausposteParcel/enableextension/enabled', 0);
            $data = $this->scopeConfig('ausposteParcel/activation/data');
            $this->resourceConfig->saveConfig('ausposteParcel/activation/data', $data, 'default', 0);
            $this->resourceConfig->saveConfig('ausposteParcel/activation/websites', '', 'default', 0);
            $this->resourceConfig->saveConfig('ausposteParcel/activation/store', '', 'default', 0);
            return;
        }
        $data = '';
        $web = '';
        $en = '';
        if (isset($res['dom']) && intval($res['c']) > 0 && intval($res['suc']) == 1) {
            $data = $this->encryptor->encrypt(base64_encode($this->zend->encode($res1)));
            if (!$s) {
                if (isset($params['activation']['fields']['store']['value'])) {
                    $s = $params['activation']['fields']['store']['value'];
                }
            }
            $en = $res['suc'];
            if (isset($s) && $s != null) {
                $web = $this->encryptor->encrypt($data . implode(',', $s) . $data);
            } else {
                $web = $this->encryptor->encrypt($data . $data);
            }
        } else {
            $moduleStatus->saveConfig('ausposteParcel/activation/key', "", 'default', 0);
            $moduleStatus->saveConfig('ausposteParcel/enableextension/enabled', 0, 'default', 0);
            $this->resourceConfig->saveConfig('ausposteParcel/activation/store', '', 'default', 0);
        }

        $this->resourceConfig->saveConfig('ausposteParcel/activation/data', $data, 'default', 0);
        $this->resourceConfig->saveConfig('ausposteParcel/activation/websites', $web, 'default', 0);
        $this->resourceConfig->saveConfig('ausposteParcel/activation/en', $en, 'default', 0);
        $this->resourceConfig->saveConfig('ausposteParcel/activation/installed', 1, 'default', 0);

        //refresh config cache after save
        $types = ['config', 'full_page'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
