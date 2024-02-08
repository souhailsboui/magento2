<?php

namespace Machship\Fusedship\Block\Cart\View;


use Magento\Framework\View\Element\Template;


class CustomCart extends Template {

    public function isShowResidentialOption() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fusedshipHelper = $objectManager->get('Machship\Fusedship\Helper\Data');

        return $fusedshipHelper->getIsShowResidentialOption();
    }

}