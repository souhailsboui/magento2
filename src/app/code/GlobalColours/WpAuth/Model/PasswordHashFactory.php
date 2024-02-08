<?php

namespace GlobalColours\WpAuth\Model;

use GlobalColours\WpAuth\Model\PasswordHash;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Simplexml\Element;


class PasswordHashFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create config model
     *
     * @param int $iteration_count_log2
     * @param bool $portable_hashes
     * @return PasswordHash
     */
    public function create($iteration_count_log2 = 8, $portable_hashes = true): PasswordHash
    {
        return $this->_objectManager->create(PasswordHash::class, ["iteration_count_log2" => $iteration_count_log2, "portable_hashes" => $portable_hashes]);
    }
}
