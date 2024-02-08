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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttachment\Model\Attribute\Source;

class File extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * File Factory
     *
     * @var \Bss\ProductAttachment\Model\FileFactory
     */
    protected $_attachmentFactory;

    /**
     * ResourceConnection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Contructor
     *
     * @param \Bss\ProductAttachment\Model\FileFactory $attachmentFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Bss\ProductAttachment\Model\FileFactory $attachmentFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->request = $request;
        $this->_attachmentFactory = $attachmentFactory;
        $this->_resource = $resource;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {

        $storeView = $this->request->getParam('store');
        $storeView = isset($storeView)? $storeView : 0;

        $option = [];

        $data = [];

        $attachmentFactory = $this->_attachmentFactory->create();
        $collection = $attachmentFactory->getCollection();

        foreach ($collection as $item) {
            $stores = $item->getStoreId();

            if ($item->getStatus() &&
                $this->inStore($storeView, $stores)
            ) {
                $data[] =  $item->getData();
            }
        }

        foreach ($data as $key => $value) {
            $option[] =
                [
                    'value'=>$value['file_id'],
                    'label'=>$value['title']
                ];
        }

        return $option;
    }

    /**
     * Check in store
     *
     * @param string $storeId
     * @param string $listStore
     * @return bool
     */
    protected function inStore($storeId, $listStore)
    {
        if ($listStore !== null) {
            $stores = explode(",", $listStore);
        } else {
            $stores = [];
        }

        if (in_array($storeId, $stores) || in_array(0, $stores)) {
            return true;
        }

        return false;
    }
}
