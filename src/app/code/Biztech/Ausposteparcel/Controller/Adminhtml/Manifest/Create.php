<?php

namespace Biztech\Ausposteparcel\Controller\Adminhtml\Manifest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Create extends Action
{
    protected $manifestmodel;
    protected $_dir;

    public function __construct(
        Context $context,
        \Biztech\Ausposteparcel\Model\Manifest $manifestmodel,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->manifestmodel = $manifestmodel;
        $this->_dir = $dir;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    public function execute()
    {
        $getIds = $this->getRequest()->getParam('manifest_id');
        foreach ($getIds as $key => $value) {
            $data = $this->manifestmodel->load($value, 'manifest_id')->getData();
            $label = $data['label'];
            if (isset($label) && $label != '') {
                $downloadlabel_ids[] = $value;
                $allPDfFiles[] = $this->_dir->getPath('media') . $label;
            } else {
                $ignoreArray[] = $value;
            }
        }
        $zipName = 'OrderSummary.zip';
        $valid_files = array();

        if (is_array($allPDfFiles)) {
            foreach ($allPDfFiles as $file) {
                if (file_exists($file)) {
                    $valid_files[] = $file;
                } else {
                }
            }
        }
        if (count($valid_files)) {
            $zip = new ZipArchive();
            if ($zip->open($zipName, ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            foreach ($valid_files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            $contentType = 'application/zip';
            $response = $this->getResponse();
            $response->setHeader('HTTP/1.1 200 OK', '');
            $response->setHeader('Pragma', 'public', true);
            $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
            $response->setHeader('Content-Disposition', 'attachment; filename=' . basename($zipName));
            $response->setHeader('Content-Length', filesize($zipName));
            $response->setHeader('Content-type', $contentType);
            $this->getResponse()->clearBody();
            $this->getResponse()->sendHeaders();
            readfile($zipName);
            unlink($zipName);
        } else {
            $message = __("Nothing to download");
            $this->messageManager->addError($message);
            $this->_redirect('*/*/index');
        }
    }
}
