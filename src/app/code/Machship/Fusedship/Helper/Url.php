<?php

namespace Machship\Fusedship\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

class Url extends AbstractHelper
{
    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function getSaveAddressUrl()
    {
        return $this->urlBuilder->getUrl('Machship/Fusedship/Controller/Address/Save');
    }
}
