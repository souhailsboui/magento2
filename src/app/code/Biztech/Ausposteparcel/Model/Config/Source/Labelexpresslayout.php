<?php

namespace Biztech\Ausposteparcel\Model\Config\Source;

class Labelexpresslayout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\CollectionFactory
     */
    protected $auspostlabelCollectionFactory;

    public function __construct(
        \Biztech\Ausposteparcel\Model\Cresource\Auspostlabel\CollectionFactory $auspostlabelCollectionFactory
    ) {
        $this->auspostlabelCollectionFactory = $auspostlabelCollectionFactory;
    }

    public function getAllOptions()
    {
        if (!$this->_options) {
            $auspostLabel = $this->auspostlabelCollectionFactory->create()->addFieldToFilter('label_id', 2)->getFirstItem()->getData();

            if (sizeof($auspostLabel)) {
                $this->_options = array(
                    array('value' => '', 'label' => __('Select Label Layout for ' . $auspostLabel['label_group'])),
                    array('value' => 1, 'label' => __('A4-3pp')),
                    array('value' => 2, 'label' => __('A4-1pp')),
                    array('value' => 3, 'label' => __('THERMAL-LABEL-A6-1PP'))
                );
            } else {
                $this->_options = array(
                    array('value' => '0', 'label' => __('Select Label Layout'))
                );
            }
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
