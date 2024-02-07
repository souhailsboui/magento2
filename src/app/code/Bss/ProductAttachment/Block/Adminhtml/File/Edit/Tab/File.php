<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Block\Adminhtml\File\Edit\Tab;

use Bss\ProductAttachment\Helper\Data;
use Magento\Store\Model\System\Store;

class File extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var \Bss\ProductAttachment\Model\Source\Status
     */
    protected $_statusOptions;

    /**
     * Yesno options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_booleanOptions;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_customerGroupOptions;

    /**
     * Attachment type options
     *
     * @var \Bss\ProductAttachment\Model\Source\AttachmentType
     */
    protected $_attachType;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\ProductAttachment\Helper\Data $helper
     * @param \Bss\ProductAttachment\Model\Source\Status $statusOptions
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Bss\ProductAttachment\Model\Source\AttachmentType $attachmentType
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\ProductAttachment\Helper\Data $helper,
        \Bss\ProductAttachment\Model\Source\Status $statusOptions,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Store\Model\System\Store $systemStore,
        \Bss\ProductAttachment\Model\Source\AttachmentType $attachmentType,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupOptions,
        $data = []
    ) {
        $this->_booleanOptions = $booleanOptions;
        $this->_statusOptions = $statusOptions;
        $this->_helper = $helper;
        $this->_systemStore = $systemStore;
        $this->_customerGroupOptions = $customerGroupOptions;
        $this->_attachType = $attachmentType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     *
     * @return File|\Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        $storeView = $this->getRequest()->getParam('store');
        $storeView = isset($storeView)? $storeView : 0;
        /** @var \Bss\ProductAttachment\Model\File $attachment */
        $attachment = $this->_coreRegistry->registry('bss_productattachment_file');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('file_');
        $form->setFieldNameSuffix('file');

        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($attachment->getId()) {
            $fieldset->addField(
                'file_id',
                'hidden',
                ['name' => 'file_id']
            );
        }

        $fieldset->addField(
            'store_view',
            'hidden',
            ['name' => 'store_view']
        )->setValue($storeView);

        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'description',
            'text',
            [
                'name'  => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->_statusOptions->toOptionArray(),
                'required' => true,
            ]
        )->setValue('1');

        $fieldset->addField(
            'type',
            'select',
            [
                'name'  => 'type',
                'label' => __('Upload Type'),
                'title' => __('Upload Type'),
                'values' => $this->_attachType->toOptionArray(),
                'required' => true,
            ]
        )->setValue('1');

        $fieldset->addField(
            'file',
            'file',
            [
                'label' => __('Upload File'),
                'title' => __('Upload File'),
                'display' => 'none',
                'required' => $this->_helper->isRequireFileUpload($attachment),
                'note' => $this->_helper->getFileNameAttachment($attachment).
                            "Extension: jpg, jpeg, gif, png, zip, doc, docx, pdf, xls, xlsx, ppt, pptx, mp3, avi,mp4"
            ]
        );

        $fieldset->addField(
            'uploaded_file',
            'text',
            [
                'name'  => 'link_file',
                'label' => __('URL'),
                'title' => __('URL'),
                'display' => 'none',
                'required' => true,
                'class' => 'validate-url'
            ]
        );

        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name'     => 'store_id',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'style' => 'height:10em; max-height: 20em',
                'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'customer_group',
            'multiselect',
            [
                'name'     => 'customer_group',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'style' => 'height:10em;',
                'values'   => $this->_customerGroupOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'show_footer',
            'select',
            [
                'name'     => 'show_footer',
                'label'    => __('Show in Footer'),
                'title'    => __('Show in Footer'),
                'required' => true,
                'values'   => $this->_booleanOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name'  => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'limit_time',
            'text',
            [
                'name'  => 'limit_time',
                'label' => __('Limit Number of Downloads'),
                'title' => __('Limit Number of Downloads'),
                'class' => 'validate-zero-or-greater',
                'note' => __("Fill “0” or don’t fill anything in this field to not limit the number of downloads.")
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}type",
                'type'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}file",
                'file'
            )->addFieldMap(
                "{$htmlIdPrefix}uploaded_file",
                'uploaded_file'
            )
            ->addFieldDependence(
                'file',
                'type',
                '1'
            )->addFieldDependence(
                'uploaded_file',
                'type',
                '0'
            )
        );

        $fileData = $this->_session->getData('bss_productattachment_file_data', true);
        if ($fileData) {
            $attachment->addData($fileData);
        } else {
            if (!$attachment->getId()) {
                $attachment->addData($attachment->getDefaultValues());
            }
        }

        $form->addValues($attachment->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
