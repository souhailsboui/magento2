<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Product\Sorting;

use Amasty\VisualMerch\Model\Product\Sorting\SortInterface;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $className
     * @param array $data
     * @return SortInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $sortInstance = $this->objectManager->create('\Amasty\VisualMerch\Model\Product\Sorting\\'.$className, $data);

        if (!$sortInstance instanceof SortInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t implement SortInterface', $className)
            );
        }
        return $sortInstance;
    }
}
