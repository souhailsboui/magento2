<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\ZohoCRM\Model\QueueFactory;

/**
 * Class MassDelete
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Queue
 */
class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_ZohoCRM::queues';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        QueueFactory $queueFactory
    ) {
        $this->filter       = $filter;
        $this->queueFactory = $queueFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->queueFactory->create()->getCollection());
        $deleted    = 0;
        foreach ($collection->getItems() as $item) {
            $item->delete();
            $deleted++;
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $deleted));

        return $this->_redirect('*/*/');
    }
}
