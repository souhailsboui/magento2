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
namespace Mageplaza\ZohoCRM\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\LayoutFactory;
use Mageplaza\ZohoCRM\Block\Adminhtml\Sync\Edit\Tab\Condition as BlockCondition;
use Mageplaza\ZohoCRM\Model\SyncFactory;

/**
 * Class Condition
 * @package Mageplaza\ZohoCRM\Controller\Adminhtml\Sync
 */
class Condition extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var InlineInterface
     */
    protected $translateInline;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var SyncFactory
     */
    protected $syncFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param InlineInterface $translateInline
     * @param RawFactory $resultRawFactory
     * @param SyncFactory $syncFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        InlineInterface $translateInline,
        RawFactory $resultRawFactory,
        SyncFactory $syncFactory,
        Registry $registry
    ) {
        $this->layoutFactory    = $layoutFactory;
        $this->translateInline  = $translateInline;
        $this->resultRawFactory = $resultRawFactory;
        $this->syncFactory      = $syncFactory;
        $this->registry         = $registry;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Raw|ResultInterface
     */
    public function execute()
    {
        $syncId    = $this->getRequest()->getParam('id');
        $syncModel = $this->syncFactory->create();
        if ($syncId) {
            $syncModel->load($syncId);
        }

        $this->registry->register('sync_rule', $syncModel);

        $layout = $this->layoutFactory->create();
        $html   = $layout->createBlock(BlockCondition::class)
            ->toHtml();
        $this->translateInline->processResponseBody($html);

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($html);

        return $resultRaw;
    }
}
