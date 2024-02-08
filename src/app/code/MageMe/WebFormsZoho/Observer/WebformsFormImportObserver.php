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

namespace MageMe\WebFormsZoho\Observer;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebFormsZoho\Api\Data\FormInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

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

        /** CRM */
        $oldId = $form->getZohoCrmEmailFieldId();
        if ($oldId && !empty($elementMatrix['field_' . $oldId])) {
            $form->setZohoCrmEmailFieldId($elementMatrix['field_' . $oldId]);
        }
        $serializedFields = $form->getZohoCrmMapFieldsSerialized();
        if ($serializedFields) {
            $map = json_decode($serializedFields, true);
            if (is_array($map)) {
                foreach ($map as &$mapField) {
                    if (!empty($mapField[FieldInterface::ID]) &&
                        !empty($elementMatrix['field_' . $mapField[FieldInterface::ID]])) {
                        $mapField[FieldInterface::ID] = $elementMatrix['field_' . $mapField[FieldInterface::ID]];
                    }
                }
            }
            $form->setZohoCrmMapFields($map);
        }

        /** Desk */
        $oldId = $form->getZohoDeskEmailFieldId();
        if ($oldId && !empty($elementMatrix['field_' . $oldId])) {
            $form->setZohoDeskEmailFieldId($elementMatrix['field_' . $oldId]);
        }
        $serializedFields = $form->getZohoDeskMapFieldsSerialized();
        if ($serializedFields) {
            $map = json_decode($serializedFields, true);
            if (is_array($map)) {
                foreach ($map as &$mapField) {
                    if (!empty($mapField[FieldInterface::ID]) &&
                        !empty($elementMatrix['field_' . $mapField[FieldInterface::ID]])) {
                        $mapField[FieldInterface::ID] = $elementMatrix['field_' . $mapField[FieldInterface::ID]];
                    }
                }
            }
            $form->setZohoDeskMapFields($map);
        }

        $this->formRepository->save($form);
    }
}
