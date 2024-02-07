<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Amasty\Reports\Model;

class Flag extends \Magento\Framework\Flag
{
    public const REPORT_CUSTOMERS_CUSTOMERS_FLAG_CODE = 'amasty_reports_customers_customers';

    /**
     * @param $code
     * @return $this
     */
    public function setReportFlagCode($code)
    {
        $this->_flagCode = $code;
        return $this;
    }
}
