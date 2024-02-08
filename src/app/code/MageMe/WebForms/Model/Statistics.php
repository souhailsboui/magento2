<?php

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\Data\StatisticsInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Statistics extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, StatisticsInterface
{
    /**
     * Store cache tag
     */
    const CACHE_TAG = 'webforms_statistics';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_statistics';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_statistics';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Statistics::class);
    }

    #region DB getters and setters
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): StatisticsInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType(): ?string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEntityType(string $entityType): StatisticsInterface
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): StatisticsInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue(?string $value): StatisticsInterface
    {
        return $this->setData(self::VALUE, $value);
    }
    #endregion
}