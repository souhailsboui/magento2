<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

class Manifestconsignments extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $messageManager;
    protected $urlinterface;
    protected $ausposteParcelInfoHelper;
    protected $datetime;
    protected $request;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Biztech\Ausposteparcel\Helper\Info $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\UrlInterface $urlinterface,
        array $data = []
    ) {
        $this->urlinterface = $urlinterface;
        $this->request = $http;
        $this->datetime = $datetime;
        $this->ausposteParcelInfoHelper = $helper;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->_controller = 'adminhtml_manifestconsignments';
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        
        $manifest_number = $this->getRequest()->getParam('manifest');
        $text = __('Manifest Consignments - %1', $manifest_number);

        $manifest = $this->ausposteParcelInfoHelper->getManifestData($manifest_number);

        if ($manifest && $manifest['despatch_date'] != '') {
            $dateTimestamp = $this->datetime->timestamp(strtotime($manifest['despatch_date']));
            $text .= ', despatched at ' . date('m/d/Y H:i:s', $dateTimestamp);
        }

        $this->_headerText = $text;
        /*if ($manifest['despatch_date'] == '' || $manifest['despatch_date'] == null) {
            $this->addButton('btnAdd', array(
                'label' => __('Add More Consignments'),
                'style' => 'background-color: #eb5202; color: #ffffff; height:45px; width:215px; font-size:16px; padding:0.5rem 0rem 0.6rem 0rem;',
                'onclick' => "setLocation('" . $this->urlinterface->getUrl('biztech_ausposteparcel/consignment/getPendingConsignments', array('manifest_number' => $manifest_number)) . "')",
                'class' => 'add'
            ));
        }*/

        parent::_construct();

        //Remove original add button
        $this->removeButton('add');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
