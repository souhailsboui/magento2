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
 * @package     Mageplaza_StoreCredit
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\StoreCredit\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\StoreCredit\Model\ActionFactory;

/**
 * Class Action
 * @package Mageplaza\StoreCredit\Model\Config\Source
 */
class Action implements OptionSourceInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * Action constructor.
     *
     * @param ActionFactory $actionFactory
     */
    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return $this->actionFactory->getOptionHash();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->actionFactory->toOptionArray();
    }
}
