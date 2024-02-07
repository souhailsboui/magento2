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

namespace Mageplaza\StoreCredit\Plugin\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\StoreCredit\Model\Product\Type\StoreCredit;

/**
 * Class ProductDataProvider
 * @package Mageplaza\StoreCredit\Plugin\Catalog
 */
class ProductDataProvider
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
     * @param array $result
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetData(\Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject, $result)
    {
        if (isset($result['items'])) {
            foreach ($result['items'] as &$item) {
                if ($item['type_id'] == StoreCredit::TYPE_STORE_CREDIT) {
                    $product = $this->productRepository->getById($item['entity_id']);
                    if ($product && $price = $product->getCreditAmount()) {
                        $item['price'] = $price;
                    }
                }
            }
        }

        return $result;
    }
}
