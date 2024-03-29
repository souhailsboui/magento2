<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Convert;

class FineDiffCopyOp extends \Bss\AdminActionLog\Convert\FineDiffOp
{
    /** @var int $len */
    private $len;
    /**
     * FineDiffCopyOp constructor.
     * @param $len
     */
    public function __construct($len)
    {
        $this->len = $len;
    }

    /**
     * Get From Len
     *
     * @return int
     */
    public function getFromLen()
    {
        return $this->len;
    }

    /**
     * GetToLen
     *
     * @return int
     */
    public function getToLen()
    {
        return $this->len;
    }

    /**
     * Get Opcode
     *
     * @return string
     */
    public function getOpcode()
    {
        if ($this->len === 1) {
            return 'c';
        }
        return "c{$this->len}";
    }

    /**
     * Increase
     *
     * @param $size
     * @return mixed
     */
    public function increase($size)
    {
        return $this->len += $size;
    }
}
