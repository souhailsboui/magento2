<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Biztech\Ausposteparcel\Model\Consignment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Deletearticle extends Action
{
    protected $_consignment;
    protected $order;
    protected $info;
    protected $consignmentmodelFactory;
    protected $manifestmodelFactory;
    protected $datahelper;
    // protected $messageManager;
    protected $_dir;
    protected $article;

    public function __construct(
        Context $context,
        Consignment $consignment,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Helper\Info $info,
        ScopeConfigInterface $scopeconfiginterface,
        \Biztech\Ausposteparcel\Model\Cresource\Consignment\CollectionFactory $consignmentmodelFactory,
        \Biztech\Ausposteparcel\Model\Cresource\Manifest\CollectionFactory $manifestmodelFactory,
        \Biztech\Ausposteparcel\Helper\Data $datahelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Biztech\Ausposteparcel\Helper\Article $article
    ) {
        parent::__construct($context);
        $this->_consignment = $consignment;
        $this->order = $order;
        $this->info = $info;
        $this->consignmentmodelFactory = $consignmentmodelFactory;
        $this->manifestmodelFactory = $manifestmodelFactory;
        $this->datahelper = $datahelper;
        $this->messageManager = $context->getMessageManager();
        $this->_dir = $dir;
        $this->article = $article;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $order_id = $data['order_id'];
        $orderData = $this->order->load($order_id);
        $consignmentNumber = $data['consignment_number'];
        $articleNumber = $data['article_number'];

        try {
            $articles = $this->info->getArticles($order_id, $consignmentNumber);
            if ($articles && count($articles) > 1) {
                $deleteArticle = $this->datahelper->deleteArticle($order_id, $consignmentNumber, $articleNumber);
                $articleData = $this->article->prepareModifiedArticleData($orderData, $consignmentNumber);
                $content = $articleData['content'];
                $chargeCode = $articleData['charge_code'];
                $total_weight = $articleData['total_weight'];
                $consignment = $this->_consignment;
                $consignmentData = $this->info->getConsignment($order_id, $consignmentNumber);
                if ($consignmentData) {
                    $updateData = array('weight' => $total_weight);
                    $consignment->load($consignmentData['consignment_id'])->addData($updateData);
                    $consignment->setConsignmentId($consignmentData['consignment_id'])->save();

                    $success = (__('Article #' . $articleNumber . ' has been deleted from consignment #' . $consignmentNumber . ' successfully.'));
                    $this->messageManager->addSuccess($success);
                    if (array_key_exists("source", $data)) {
                        if ($data['source'] == 'grid') {
                            $this->_redirect("sales/order/view", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
                        }
                    } else {
                        $this->_redirect("sales/order/view", array('order_id' => $order_id, 'source' => 'grid', 'consignment_number' => $consignmentNumber));
                    }
                }
            } else {
                $this->deleteConsignmentArticle();
            }
        } catch (\Exception $e) {
            $error = (__('Could not delete article, Error: ') . $e->getMessage());
            $this->messageManager->addError($error);
            if ($data['source'] == 'grid') {
                $this->_redirect("adminhtml/consignment/create", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
            } else {
                $this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id, 'active_tab' => 'auspost_eparcel'));
            }
        }
    }

    public function deleteConsignmentArticle()
    {
        $data = $this->getRequest()->getParams();
        $order_id = $data['order_id'];
        $consignmentNumber = $data['consignment_number'];
        $consignment = $this->info->getConsignment($order_id, $consignmentNumber);
        try {
            $filename = $consignmentNumber . '.pdf';
            $filepath = $this->_dir->getPath('media') . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'consignment' . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $filename = $consignmentNumber . '.pdf';
            $filepath = $this->_dir->getPath('media') . 'ausposteParcel' . DIRECTORY_SEPARATOR . 'label' . DIRECTORY_SEPARATOR . 'returnlabels' . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $this->datahelper->deleteConsignment($order_id, $consignmentNumber);
            $this->datahelper->deleteManifest2($consignment['manifest_number']);

            $successConsignment = (__('Article has been deleted from consignment #' . $consignmentNumber . ' successfully.'));
            $successArticle = (__('Consignment #' . $consignmentNumber . ' has been deleted successfully.'));
            $this->messageManager->addSuccess($successConsignment);
            $this->messageManager->addSuccess($successArticle);
            $this->_redirect("sales/order/view", array('order_id' => $order_id, 'source' => 'grid', 'consignment_number' => $consignmentNumber));
        } catch (\Exception $e) {
            $error = (__('Could not delete consignment.') . $e->getMessage());
            $this->messageManager->addError($error);

            $this->datahelper->deleteManifest2($consignment['manifest_number']);
            if ($data['source'] == 'grid') {
                $this->_redirect("sales/order/view", array('order_id' => $order_id, 'consignment_number' => $consignmentNumber));
            } else {
                $this->_redirect("sales/order/view", array('order_id' => $order_id, 'active_tab' => 'auspost_eparcel'));
            }
        }
    }
}
