<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

// phpcs:ignoreFile
namespace Amasty\Reports\Model\Grid\Export\Sales\Overview;

use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as OriginalConvertToCsv;

class ConvertToCsv extends OriginalConvertToCsv
{
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 200
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }
}
