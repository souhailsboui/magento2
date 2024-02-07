<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bss\ProductAttachment\Helper\Data;
use \Magento\Store\Model\StoreManagerInterface;

class RemoveBlock implements ObserverInterface
{
    /**
     * Data
     * @var Data $helperData
     */
    protected $helperData;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Data $helperData
     * @param SessionFactory $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helperData,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Check tab information
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('attachment.tab');
        $storeCurrentId = $this->storeManager->getStore()->getId();
        if ($block) {
            $attachments = [];
            $customerGroupId = $this->customerSession->create()->getCustomerGroupId();
            $arrAttachmentString = $block->getProduct()->getData('bss_productattachment');
            if ($arrAttachmentString !== null) {
                $arrAttachmentIds = explode(',', $arrAttachmentString);
            } else {
                $arrAttachmentIds = [];
            }
            if (isset($arrAttachmentIds) && !empty($arrAttachmentIds)) {
                foreach ($arrAttachmentIds as $key => $value) {
                    if (!empty($this->helperData->getDataAttachmentById($value))) {
                        $attachments[] = $this->helperData->getDataAttachmentById($value);
                    }
                }
            }
            foreach ($attachments as $key => $value) {
                if (!empty($value)) {
                    if (!$this->checkCustomerGroup($customerGroupId, $value['customer_group']) ||
                        $value['status'] == 0 ||
                        !$this->checkStoreCurrent($storeCurrentId, $value['store_id'])
                    ) {
                        unset($attachments[$key]);
                    }
                }
            }
            foreach ($attachments as $key => $value) {
                if (!empty($value)) {
                    if ($value['limit_time'] == $value['downloaded_time']) {
                        unset($attachments[$key]);
                    }
                }
            }
            if (empty($attachments)) {
                $layout->unsetElement('attachment.tab');
                $layout->unsetElement('attachment.block');
            }

        }
    }

    /**
     * Check store current
     *
     * @param int $storeCurrentId
     * @param string $stringStoreIds
     * @return bool
     */
    protected function checkStoreCurrent($storeCurrentId, $stringStoreIds)
    {
        $storeIds = explode(',', $stringStoreIds);
        if (in_array('0', $storeIds)) {
            return true;
        }
        return in_array($storeCurrentId, $storeIds);
    }

    /**
     * Check customer group
     *
     * @param string $currentCustomerGroup
     * @param string $customerGroupString
     * @return bool
     */
    protected function checkCustomerGroup($currentCustomerGroup, $customerGroupString)
    {
        if ($customerGroupString !== null) {
            $arr = explode(',', $customerGroupString);
        } else {
            $arr = [];
        }

        return in_array($currentCustomerGroup, $arr);
    }
}
