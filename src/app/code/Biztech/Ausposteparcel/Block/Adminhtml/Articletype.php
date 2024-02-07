<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Articletype extends Container
{
    protected $messageManager;
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Biztech\Ausposteparcel\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_articletype'; /* block grid.php directory */
        $this->_blockGroup = 'Biztech_Ausposteparcel';
        $this->_headerText = __('Articletype');
        parent::_construct();
        if (!empty($this->helper->getAllWebsites())) {
            $this->buttonList->add(
                'export_csv',
                [
                'label' => __('Add New Article Type'),
                'style' => 'background-color: #eb5202; color: #ffffff; height:45px; width:185px; font-size:16px; padding:0.5rem 0rem 0.6rem 0rem;',
                'onclick' => "setLocation('{$this->getUrl('*/*/new')}')"
                    ]
            );
            $this->removeButton('add');
            return $this;
        } else {
            $this->removeButton('add');
            $this->messageManager->addError(__('Extension- Australia Post Parcel Send is not enabled. Please enable it from Store > Configuration > Sales > Shipping Methods -> Appjetty Australia Post Parcel Send.'));
            return $this;
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
