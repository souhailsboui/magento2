<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Newarticle extends Action
{
    protected $_consignment;
    protected $order;
    protected $article;
    protected $info;
    protected $scopeconfiginterface;
    protected $consignmentmodelFactory;
    protected $manifestmodelFactory;
    protected $datahelper;
    protected $apimodel;

    public function __construct(
        Context $context,
        Consignment $consignment,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Helper\Article $article,
        \Biztech\Ausposteparcel\Helper\Info $info,
        ScopeConfigInterface $scopeconfiginterface,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentmodelFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Manifest\CollectionFactory $manifestmodelFactory,
        \Biztech\Ausposteparcel\Helper\Data $datahelper,
        \Biztech\Ausposteparcel\Model\Api $apimodel
    ) {
        parent::__construct($context);
        $this->_consignment = $consignment;
        $this->scopeconfiginterface = $scopeconfiginterface;
        $this->order = $order;
        $this->info = $info;
        $this->article = $article;
        $this->consignmentmodelFactory = $consignmentmodelFactory;
        $this->manifestmodelFactory = $manifestmodelFactory;
        $this->datahelper = $datahelper;
        $this->apimodel = $apimodel;
        $this->messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $order_id = $data['order_id'];
        $order = $this->order->load($order_id);
        $consignmentNumber = $data['consignment_number'];

        $orderWeight = $this->info->getOrderWeight($order);
        $articleWeight = $data['article']['weight'];
        $consignmentData = $this->info->getConsignment($order_id, $consignmentNumber);
        $data['consignment_id'] = $consignmentData['consignment_id'];
        // $consignmentTotalWeight = $consignmentData['weight'] + $articleWeight;

        $articleData = $this->info->getArticles($order_id, $consignmentNumber);
        $consignmentTotalWeight = 0;
        foreach ($articleData as $key => $value) {
            $consignmentTotalWeight += $value['actual_weight'];
        }
        $consignmentTotalWeight = $consignmentTotalWeight + $articleWeight;
        $consignmentTotalWeight = number_format($consignmentTotalWeight, 2); 
        $orderWeight = number_format($orderWeight, 2);
        
        if ($consignmentTotalWeight > $orderWeight && (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useTotalOrderWeight') && $data['articles_type'] == 'Custom') {
            $error = (__('Combined consignment weight is more than the total order weight.'));
            $this->messageManager->addError($error);

            if ($data['source'] == 'grid') {
                $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
            } else {
                $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
            }
        } else {
            try {
                $articleData = $this->article->prepareAddArticleData($data, $order, $consignmentNumber);
                $content = $articleData['content'];
                $chargeCode = $articleData['charge_code'];
                $total_weight = $articleData['total_weight'];

                if ($total_weight > $orderWeight) {
                    $error = __('Combined consignment weight is more than the total order weight.');
                    $this->messageManager->addError($error);
                    if ($data['source'] == 'grid') {
                        $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
                    } else {
                        $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
                    }
                } else {
                    if ($consignmentNumber) {
                        $this->datahelper->updateConsignment($order_id, $consignmentNumber, $data, $consignmentData['manifest_number'], $chargeCode, $total_weight);

                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $table = $resource->getTableName('biztech_ausposteParcel_article');
                        $query = "DELETE FROM {$table} WHERE consignment_number='{$consignmentNumber}'";
                        $connection->query($query);

                        $xml = simplexml_load_string($content);
                        if ($xml) {
                            $j = 0;
                            $latestArticleNumber = '';
                            $latestArticlePrefix = '';
                            foreach ($xml->articles->article as $article) {
                                if ($article->articleNumber) {
                                    $latestArticleNumber = substr($article->articleNumber, -4);
                                    $latestArticlePrefix = substr($article->articleNumber, 0, -4);
                                }
                            }
                            $newArticleId = (intval($latestArticleNumber) + 1);
                            $newArticleNumber = $latestArticlePrefix . str_pad($newArticleId, 4, "0", STR_PAD_LEFT);
                            foreach ($xml->articles->article as $article) {
                                if (!$article->articleNumber) {
                                    $article->articleNumber = $newArticleNumber;
                                }
                                $this->datahelper->addArticle($order_id, $consignmentNumber, $article);
                            }
                        }

                        if ($data['print_labels']) {
                        }
                        if ($data['print_return_labels']) {
                        }

                        $success = (__('The article has been added successfully.'));
                        $this->messageManager->addSuccess($success);
                        if ($data['source'] == 'grid') {
                            $this->_redirect('sales/order/view/', array('order_id' => $order_id, 'source' => 'grid', 'consignment_number' => $consignmentNumber));
                        } else {
                            $this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id, 'active_tab' => 'auspost_eparcel'));
                        }
                    }
                }
            } catch (\Exception $e) {
                $error = __('Failed to add article: ') . $e->getMessage();
                $this->messageManager->addError($error);

                if ($data['source'] == 'grid') {
                    $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'source' => 'grid'));
                } else {
                    $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id, 'consignment_number' => $this->getRequest()->getParam('consignment_number')));
                }
            }
        }
    }
}
