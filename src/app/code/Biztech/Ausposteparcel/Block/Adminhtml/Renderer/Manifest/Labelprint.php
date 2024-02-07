<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest;

use Magento\Framework\App\Action\Action;

class Labelprint extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $dir;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->_storeManager = $storeManager;
        $this->dir = $dir;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $label = $row->getData($this->getColumn()->getIndex());
        if ($label) {
            $labelLink = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'biztech/' . $label;
            $html = '<a href=' . $labelLink . ' target="_blank" >View</a>';
        } else {
            $html = '&nbsp;';
        }
        return $html;
    }
}
