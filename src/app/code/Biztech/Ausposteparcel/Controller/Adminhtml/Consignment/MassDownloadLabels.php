<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassDownloadLabels extends Action
{
    public $order;
    public $consignmentmodel;
    public $apimodel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Biztech\Ausposteparcel\Model\Consignment $consignmentmodel,
        \Biztech\Ausposteparcel\Model\Api $apimodel
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->order = $order;
        $this->consignmentmodel = $consignmentmodel;
        $this->apimodel = $apimodel;
    }

    public function execute()
    {
        $getParams = $this->getRequest()->getParams();
        $ids = $getParams['order_consignment'];
        if (!isset($ids)) {
            $this->messageManager->addError(__('Please select item(s)'));
            $this->_redirect('*/*/index');
            return;
        } else {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            foreach ($ids as $key => $value) {
                $order_ids[] = explode('_', $value);
            }
            $downloadlabel_ids = [];
            
            foreach ($order_ids as $key1 => $value1) {
                $orderId = $value1[0];
                $consignment = $this->consignmentmodel->load($orderId, 'order_id')->getData();
                if (empty($consignment)) {
                    $this->messageManager->addError(__('Please create and print label before the download'));
                    $this->_redirect('*/*/index');
                    return;
                }
                if ($consignment['is_label_printed'] == 1 && $consignment['is_label_created'] == 1) {
                    $downloadlabel_ids[] = $value1;
                } else {
                    $ignoreArray[] = $value1;
                }
            }
            if (empty($downloadlabel_ids)) {
                $this->messageManager->addError(__('Please create and print label before the download'));
                $this->_redirect('*/*/index');
                return;
            }

            $getResponse = $this->apimodel->downloadBulkLabels($downloadlabel_ids);
            $fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
                    $filePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $dir = $filePath . 'biztech'."/";
            if(isset($getResponse['error'])) {
                $this->messageManager->addError(__($getResponse['message']));
                $this->_redirect('*/*/index');
                return;
            }
            foreach ($getResponse as $orderId => $pdfurl) {
                $consignment = $this->consignmentmodel->load($orderId, 'order_id')->getData();
                $filename = $dir.$consignment['eparcel_consignment_id'] . '.pdf';
                $content = file_get_contents($pdfurl);
                $fh = fopen($filename, 'w');
                fwrite($fh, $content);
                $allPDfFiles[] = $filename;
            }

            $zipName = 'eParcelPdf.zip';
            $valid_files = [];
            if (is_array($allPDfFiles)) {
                foreach ($allPDfFiles as $file) {
                    if (file_exists($file)) {
                        $valid_files[] = $file;
                    }
                }
            }
            if (count($valid_files)) {
                $zip = new \ZipArchive();

                if ($zip->open($dir.$zipName, \ZIPARCHIVE::CREATE) !== true) {
                    return false;
                }

                foreach ($valid_files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                if (is_array($allPDfFiles)) {
                    foreach ($allPDfFiles as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
                $zipName1 = $dir.$zipName;
                $contentType = 'application/zip';
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename=$zipName");
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile("$zipName1");
                unlink($zipName1);
            }
        }
    }
}