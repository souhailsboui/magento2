<?php

namespace Biztech\Ausposteparcel\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Locale\CurrencyInterface;
use Zend\Json\Json;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'carriers/ausposteParcel/active';
    const XML_API_KEY = 'carriers/ausposteParcel/auspost_api_key';
    const XML_PATH_DATA = 'ausposteParcel/activation/data';
    const XML_PATH_INSTALLED = 'ausposteParcel/activation/installed';
    const XML_PATH_WEBSITES = 'ausposteParcel/activation/websites';
    const XML_PATH_EN = 'ausposteParcel/activation/en';
    const XML_PATH_KEY = 'ausposteParcel/activation/key';

    protected $JsonFactory;
    protected $storeManager;
    protected $localeCurrency;
    protected $zend;
    protected $encryptor;
    protected $scopeConfig;
    protected $moduleDir;
    public $_objectManager;
    protected $messageManager;
    protected $consignment;
    protected $_dir;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $JsonFactory,
        CurrencyInterface $localeCurrency,
        Json $zend,
        EncryptorInterface $encryptor,
        StoreManager $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleDir,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Biztech\Ausposteparcel\Model\Consignment $consignment,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->JsonFactory = $JsonFactory;
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->zend = $zend;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleDir = $moduleDir;
        $this->_objectManager = $objectmanager;
        $this->messageManager = $messageManager;
        $this->consignment = $consignment;
        $this->_dir = $dir;
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }

    public function getDataInfo()
    {
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return json_decode(base64_decode($this->encryptor->decrypt($data)));
    }

    public function getFormatUrl($url)
    {
        $input = trim($url, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);
        if (isset($urlParts['path'])) {
            $domain = preg_replace('/^www\./', '', $urlParts['host'] . $urlParts['path']);
        } else {
            $domain = preg_replace('/^www\./', '', $urlParts['host']);
        }
        return $domain;
    }

    public function getAllWebsites()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INSTALLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$value) {
            return [];
        }
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $web = $this->scopeConfig->getValue(self::XML_PATH_WEBSITES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $websites = explode(',', str_replace($data, '', $this->encryptor->decrypt($web)));
        $websites = array_diff($websites, [""]);
        return $websites;
    }

    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllStoreDomains()
    {
        $domains = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $url = $store->getConfig('web/unsecure/base_url');
                    $domains[] = $this->getFormatUrl($url);
                    $url = $store->getConfig('web/secure/base_url');
                    $domains[] = $this->getFormatUrl($url);
                }
            }
        }
        return array_unique($domains);
    }
    

    public function isEnabled()
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            if ($websiteId) {
                $websites = $this->getAllWebsites();
                $key = $this->scopeConfig->getValue(self::XML_PATH_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($key == null || $key == '') {
                    return false;
                } else {
                    $enPath = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if ($isEnabled && $enPath && in_array($websiteId, $websites)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $enPath = $enPath = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($isEnabled && $enPath) {
                    return true;
                }
            }
        }
    }

    public function insertConsignment($order_id, $consignmentNumber, $data, $manifestNumber, $chargeCode, $total_weight)
    {
        $consignment = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Consignment');

        $timestamp = time();
        $date = date('Y-m-d H:i:s', $timestamp);
        $insertData = [
            'order_id' => $order_id,
            'consignment_number' => $consignmentNumber,
            'add_date' => $date,
            'delivery_signature_allowed' => $data['delivery_signature_allowed'],
            'print_return_labels' => $data['print_return_labels'],
            'contains_dangerous_goods' => $data['contains_dangerous_goods'],
            'partial_delivery_allowed' => $data['partial_delivery_allowed'],
            'cash_to_collect' => (isset($data['cash_to_collect']) ? $data['cash_to_collect'] : ''),
            'email_notification' => $data['email_notification'],
            'notify_customers' => $data['notify_customers'],
            'general_ausposteParcel_shipping_chargecode' => $chargeCode,
            'weight' => $total_weight,
            'delivery_instructions' => $data['delivery_instructions']
        ];

        $insertData['manifest_number'] = '';
        $insertData['is_next_manifest'] = 0;
        
        $consignment->setData($insertData);
        try {
            $consignment->save()->getConsignmentId();
        } catch (\Exception $e) {
            $error = $this->__('Cannot create consignment, Error: ') . $e->getMessage();
            $this->messageManager->addError($error);
        }
    }

    public function updateArticles($order_id, $consignmentNumber, $articles, $data, $content)
    {
        if (array_key_exists("article_number", $data)) {
            $article_number = $data['article_number'];
        } else {
            $article_number = 0;
        }
        
        if ($data['articles_type'] == "Custom") {
            if (array_key_exists("number_of_articles", $data)) {
                $order_id = $data['order_id'];
                $orderData = $this->getSaleOrderDetails($order_id, $data);
            } else {
                $orderData['oldWeight'] = $data['article']['weight'];
                $orderData['oldHeight'] = $data['article']['height'];
                $orderData['oldWidth'] = $data['article']['width'];
                $orderData['oldLength'] = $data['article']['length'];
                $orderData['unit_value'] = $data['article']['unit_value'];
            }
        } else {
            $articleId = $data['articles_type'];
            $orderData = $this->getArticleDetails($order_id, $articleId);
        }
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('biztech_ausposteParcel_article');

        if (count($articles) == 1) {
            $query = "DELETE FROM {$tableName} WHERE consignment_number='{$consignmentNumber}'";
            $connection->query($query);
        }

        try {
            $articleNumbers = $articles;
            $xml = simplexml_load_string($content);

            if ($xml) {
                $j = 0;
                foreach ($xml->articles->article as $article) {
                    $articleNumber = (is_array($articleNumbers) ? $articleNumbers[$j++] : $articleNumbers);
                    $actualWeight = $article->actualWeight;
                    $articleDescription = $article->articleDescription;
                    $cubicWeight = $article->cubicWeight;
                    $isTransitCoverRequired = $article->isTransitCoverRequired;
                    $height = $article->height;

                    $length = $article->length;
                    $width = $article->width;
                }
                $weight = $orderData['oldWeight'];
                $height = $orderData['oldHeight'];
                $width = $orderData['oldWidth'];
                $length = $orderData['oldLength'];
                // $unitValue = $orderData['unit_value'];
                $transitCoverAmount = $article->transitCoverAmount;
                $unitValue = $article->unitValue;

                if ($unitValue == "" || $unitValue == 0) {
                    if ($unitValue == 0) {
                        $unitValue = 1;
                    }
                }
                if (count($articles) == 1) {
                    $query = "INSERT {$tableName} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  
                        actual_weight='{$actualWeight}', article_description='{$articleDescription}', article_number='{$articleNumber}', 
                        cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', 
                        length='{$length}', transit_cover_amount='{$transitCoverAmount}', unit_value='{$unitValue}';";
                    $connection->query($query);
                } else {
                    $sql = "UPDATE MyGuests SET lastname='Doe' WHERE id=2";
                    $query = "UPDATE {$tableName} SET cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}',length='{$length}' WHERE article_number='{$article_number}'";
                    // echo $query;
                    $connection->query($query);
                }
            }
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function getSaleOrderDetails($order_id, $data)
    {
        $number_of_articles = "article" . $data['number_of_articles'];
        if (array_key_exists($number_of_articles, $data)) {
            $oldWeight = $data[$number_of_articles]['weight'];
            $oldHeight = $data[$number_of_articles]['height'];
            $oldWidth = $data[$number_of_articles]['width'];
            $oldLength = $data[$number_of_articles]['length'];
        }
        $mergeData['oldWeight'] = $oldWeight;
        $mergeData['oldHeight'] = $oldHeight;
        $mergeData['oldWidth'] = $oldWidth;
        $mergeData['oldLength'] = $oldLength;
        return $mergeData;
    }

    public function getArticleDetails($order_id, $articleId)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $orderItems = $order->getAllVisibleItems();

        $oldHeight = 0;
        $oldWeight = 0;
        $oldWidth = 0;
        $oldLength = 0;

        $articletypeCollection = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Articletype')->load($articleId);
        $articleWeight = $articletypeCollection->getData('weight');
        $articleHeight = $articletypeCollection->getData('height');
        $articleWidth = $articletypeCollection->getData('width');
        $articleLength = $articletypeCollection->getData('length');

        $mergeData['oldWeight'] = $articleWeight;
        $mergeData['oldHeight'] = $articleHeight;
        $mergeData['oldWidth'] = $articleWidth;
        $mergeData['oldLength'] = $articleLength;
        return $mergeData;
    }

    public function insertManifest($manifestNumber, $numberOfArticles = 0, $numberOfConsignments = 0)
    {
        $manifestNumber = trim($manifestNumber);
        if (strtolower($manifestNumber) != 'unassinged') {
            $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest');
            $insertData = [
                'manifest_number' => $manifestNumber,
                'number_of_articles' => $numberOfArticles,
                'number_of_consignments' => $numberOfConsignments
            ];

            $manifest->setData($insertData);
            try {
                $manifest->save()->getManifestId();
                // $this->scopeConfig->saveConfig('carriers/ausposteParcel/manifestSync', 1);
                // Mage::getModel('core/config')->saveConfig('carriers/ausposteParcel/manifestSync', 1);
            } catch (\Exception $e) {
                $error = $this->__('Cannot create Manifest, Error: ') . $e->getMessage();
                $this->messageManager->addError($error);
            }
        }
    }

    public function updateManifest($manifestNumber, $numberOfArticles, $numberOfConsignments)
    {
        $manifest = $this->_objectManager->create('Biztech\Ausposteparcel\Model\Manifest');
        $manifestId = $this->_objectManager->get('Biztech\Ausposteparcel\Helper\Info')->getManifest($manifestNumber);
        if ($manifestId) {
            $updateData = [
                'number_of_articles' => $numberOfArticles,
                'number_of_consignments' => $numberOfConsignments
            ];
            $manifest->load($manifestId)->addData($updateData);
            try {
                $manifest->setManifestId($manifestId)->save();
            } catch (\Exception $e) {
                $error = $this->__('Cannot update Manifest, Error: ') . $e->getMessage();
                $this->messageManager->addError($error);
                $this->logger->log(null, $error);
            }
        } else {
            $insertData = [
                'manifest_number' => $manifestNumber,
                'number_of_articles' => $numberOfArticles,
                'number_of_consignments' => $numberOfConsignments
            ];

            $manifest->setData($insertData);
            try {
                $manifest->save()->getManifestId();
            } catch (\Exception $e) {
                $error = $this->__('Cannot create Manifest, Error: ') . $e->getMessage();
                $this->messageManager->addError($error);
                $this->logger->log(null, $error);
            }
        }
    }

    public function deleteConsignment($order_id, $consignmentNumber)
    {
        $consignment = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Consignment');
        $article = $this->_objectManager->get('Biztech\Ausposteparcel\Model\Articletype');

        $consignmentData = $this->_objectManager->get('Biztech\Ausposteparcel\Helper\Info')->getConsignment($order_id, $consignmentNumber);
        try {
            $consignment->setId($consignmentData['consignment_id'])->delete();
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $table = $resource->getTableName('biztech_ausposteParcel_article');

            $query = "DELETE FROM {$table} WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}'";
            // $connection->query($query);
        } catch (\Exception $e) {
            $error = $this->__('Cannot delete consignment, Error: ') . $e->getMessage();
            $this->messageManager->addError($error);
        }
    }

    public function deleteManifest2($manifestNumber)
    {
        if (!empty($manifestNumber)) {
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $table = $resource->getTableName('biztech_ausposteParcel_consignment');

            $query = "SELECT * FROM {$table} WHERE manifest_number = '$manifestNumber'";
            $results = $connection->fetchAll($query);
            if (count($results) == 0) {
                $table = $resource->getTableName('biztech_ausposteParcel_manifest');
                $query = "DELETE FROM {$table} WHERE manifest_number = '$manifestNumber'";
                // $connection->query($query);
            }
        }
    }

    public function updateConsignment($order_id, $consignmentNumber, $data, $manifestNumber, $chargeCode, $total_weight)
    {
        $consignment = $this->consignment;
        $timestamp = time();
        $date = date('Y-m-d H:i:s', $timestamp);

        $updateData = [
            'modify_date' => $date,
            'delivery_signature_allowed' => $data['delivery_signature_allowed'],
            'print_return_labels' => $data['print_return_labels'],
            'contains_dangerous_goods' => $data['contains_dangerous_goods'],
            'partial_delivery_allowed' => $data['partial_delivery_allowed'],
            'cash_to_collect' => (isset($data['cash_to_collect']) ? $data['cash_to_collect'] : ''),
            'email_notification' => $data['email_notification'],
            'notify_customers' => $data['notify_customers'],
            'general_ausposteParcel_shipping_chargecode' => $chargeCode,
            'label' => '',
            'is_label_printed' => 0,
            'is_label_created' => 0,
            'weight' => $total_weight
        ];
        if (isset($data['delivery_instructions'])) {
            $updateData['delivery_instructions'] = $data['delivery_instructions'];
        }

        $updateData['manifest_number'] = '';
        $updateData['is_next_manifest'] = 0;
        
        $consignment->load($data['consignment_id'])->addData($updateData);
        try {
            $consignment->setConsignmentId($data['consignment_id'])->save();

            $filename = $consignmentNumber . '.pdf';
            $filepath = $this->_dir->getPath('media') . DIRECTORY_SEPARATOR . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'consignment' . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $filepath = $this->_dir->getPath('media') . DIRECTORY_SEPARATOR . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'returnlabels' . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        } catch (\Exception $e) {
            $error = $this->__('Cannot update consignment, Error: ') . $e->getMessage();
            $this->messageManager->addError($error);
        }
    }

    public function addArticle($order_id, $consignmentNumber, $articleData)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('biztech_ausposteParcel_article');

        $articleNumber = $articleData->articleNumber;
        $actualWeight = $articleData->actualWeight;
        $articleDescription = $articleData->articleDescription;
        $cubicWeight = $articleData->cubicWeight;
        $height = $articleData->height;
        $isTransitCoverRequired = $articleData->isTransitCoverRequired;
        $length = $articleData->length;
        $width = $articleData->width;
        $transitCoverAmount = $articleData->transitCoverAmount;
        $unitValue = $articleData->unitValue;

        $query = "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  actual_weight='{$actualWeight}', article_description='" . $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->xmlData($articleDescription) . "', article_number='{$articleNumber}', cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}', unit_value='{$unitValue}';";
        $connection->query($query);
    }

    public function deleteArticle($order_id, $consignmentNumber, $articleNumber)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('biztech_ausposteParcel_article');

        $deleteToBeArticle = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getArticle($order_id, $consignmentNumber, $articleNumber);

        $query = "DELETE FROM {$table} WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}' AND article_number='{$articleNumber}'";
        $connection->query($query);
        return $deleteToBeArticle;
    }
}
