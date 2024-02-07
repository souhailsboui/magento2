<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Visual Merchandiser for Magento 2
 */

namespace Amasty\VisualMerch\Model;

use Amasty\Base\Model\Serializer;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * @var Serializer|null
     */
    protected $serializer;

    protected function _construct()
    {
        $amastySerializer = $this->getData('amastySerializer');
        if ($amastySerializer) {
            $this->serializer = $amastySerializer;
        }

        parent::_construct();
    }
}
