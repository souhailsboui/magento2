<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Block\Adminhtml\Report\Chart\Sales;

use Amasty\Reports\Block\Adminhtml\Report\Chart;

class Quote extends Chart
{
    public function getDefaultDisplayType(): string
    {
        return 'total';
    }
}
