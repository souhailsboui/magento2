<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Controller\Adminhtml\Report\Sales\Overview;

use Amasty\Reports\Model\Grid\Export\Sales\Overview\ConvertToXml;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Controller\Adminhtml\Export\GridToXml;

class ExportGridToXml extends GridToXml
{
    public function __construct(
        Context $context,
        ConvertToXml $converter,
        FileFactory $fileFactory,
        $filter = null,
        $logger = null
    ) {
        parent::__construct($context, $converter, $fileFactory);
    }
}
