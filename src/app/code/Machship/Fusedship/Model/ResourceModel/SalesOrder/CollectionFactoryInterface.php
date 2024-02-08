<?php

namespace Machship\Fusedship\Model\ResourceModel\SalesOrder;

/**
 * @api
 * @since 100.0.2
 */
interface CollectionFactoryInterface
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Machship\Fusedship\Model\ResourceModel\SalesOrder\Collection
     */
    public function create(array $data = []);
}
