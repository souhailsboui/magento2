<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;

class Session extends \Magento\Backend\Model\Session
{
    /**
     * @var int|null
     */
    private $currentCategoryId;

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $method = $method . "CategoryId{$this->getCurrentCategoryId()}";

        return parent::__call($method, $args);
    }

    /**
     * @param int $categoryId
     */
    public function setCurrentCategoryId(int $categoryId)
    {
        $this->currentCategoryId = $categoryId;
    }

    /**
     * @return int
     */
    public function getCurrentCategoryId(): ?int
    {
        if ($this->currentCategoryId === null) {
            throw new NoSuchEntityException(__('Category ID not set.'));
        }

        return $this->currentCategoryId;
    }
}
