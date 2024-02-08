<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebFormsCustomerRegistration\Observer;

use Exception;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebFormsCustomerRegistration\Api\Data\FormInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 *
 */
class WebformsFormImportObserver implements ObserverInterface
{
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     */
    public function __construct(
        FormRepositoryInterface $formRepository
    )
    {
        $this->formRepository = $formRepository;
    }

    /**
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var FormInterface $form */
        $form          = $observer->getData('form');
        $elementMatrix = $observer->getData('elementMatrix');

        $crMap         = is_array($form->getCrMap()) ?
            $form->getCrMap() : json_decode((string)$form->getCrMap(), true);
        if (is_array($crMap)) {
            foreach ($crMap as $entityType => $map) {
                $map = is_array($map) ? $map : json_decode((string)$map, true);
                if (!$map) {
                    $map = [];
                }
                foreach ($map as $attribute => $fieldId)
                    if ($fieldId) {
                        $type                           = 'field_';
                        $crMap[$entityType][$attribute] = $elementMatrix[$type . $fieldId];
                    }
            }
        }

        $form->setCrMap($crMap);
        $this->formRepository->save($form);
    }
}
