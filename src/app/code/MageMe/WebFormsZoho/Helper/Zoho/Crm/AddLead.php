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

namespace MageMe\WebFormsZoho\Helper\Zoho\Crm;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Model\Field\Type\File;
use MageMe\WebForms\Model\Field\Type\Gallery;
use MageMe\WebFormsZoho\Helper\ZohoHelper;
use MageMe\WebFormsZoho\Ui\Component\Form\Form\Modifier\ZohoIntegrationSettings;
use Magento\Framework\Exception\NoSuchEntityException;

class AddLead
{
    /**
     * @var ZohoHelper
     */
    private $zohoHelper;
    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;
    /**
     * @var FileGalleryRepositoryInterface
     */
    private $fileGalleryRepository;

    /**
     * @param FileGalleryRepositoryInterface $fileGalleryRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param ZohoHelper $zohoHelper
     */
    public function __construct(FileGalleryRepositoryInterface $fileGalleryRepository,
                                FieldRepositoryInterface $fieldRepository, ZohoHelper $zohoHelper)
    {
        $this->zohoHelper      = $zohoHelper;
        $this->fieldRepository = $fieldRepository;
        $this->fileGalleryRepository = $fileGalleryRepository;
    }

    /**
     * @param ResultInterface $result
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(ResultInterface $result)
    {
        /** @var \MageMe\WebFormsZoho\Api\Data\FormInterface $form */
        $form      = $result->getForm();
        $email     = $this->getEmail($form, $result);
        $firstName = '';
        $lastName  = $result->getCustomerName();
        $customer  = $result->getCustomer();
        if ($customer) {
            $firstName = $customer->getFirstname();
            $lastName  = $customer->getLastname();
        }
        $lead      = [
            'LastName' => $lastName,
            'FirstName' => $firstName,
            'Email' => $email
        ];
        if ($form->getZohoCrmLeadOwner()) {
            $lead['Owner'] = $form->getZohoCrmLeadOwner();
        }
        if ($form->getZohoCrmLeadSource()) {
            $lead['Lead_Source'] = $form->getZohoCrmLeadSource();
        }
        $mapFields = $this->mapFields($form, $result);
        $lead      = array_merge($lead, $mapFields['lead']);
        $api       = $this->zohoHelper->getApi();
        $id        = $api->CRM()->insertLead($lead);
        if (!$id) {
            return;
        }
        if ($mapFields['photo']) {
            $api->CRM()->addLeadPhoto($id, $mapFields['photo']['value']);
        }
        if ($mapFields['files']) {
            $api->CRM()->addLeadFiles($id, $mapFields['files']);
        }
    }

    /**
     * @param FormInterface|\MageMe\WebFormsZoho\Api\Data\FormInterface $form
     * @param ResultInterface $result
     * @return string
     */
    protected function getEmail(FormInterface $form, ResultInterface $result): string
    {
        $values  = $result->getFieldArray();
        $emailId = $form->getZohoCrmEmailFieldId();
        $email   = $values[$emailId] ?? '';
        if ($email) {
            return $email;
        }
        $emailList = $result->getCustomerEmail();
        return $emailList[0] ?? '';
    }

    /**
     * @param FormInterface|\MageMe\WebFormsZoho\Api\Data\FormInterface $form
     * @param ResultInterface $result
     * @return array
     * @throws NoSuchEntityException
     */
    protected function mapFields(FormInterface $form, ResultInterface $result): array
    {
        $data      = [
            'lead' => [],
            'files' => [],
            'photo' => []
        ];
        $values    = $result->getFieldArray();
        $mapFields = $form->getZohoCrmMapFields() ?: [];
        foreach ($mapFields as $mapField) {
            if (empty($values[$mapField[FieldInterface::ID]])) {
                continue;
            }
            $value = '';
            $field = $this->fieldRepository->getById((int)$mapField[FieldInterface::ID]);

            if ($mapField[ZohoIntegrationSettings::ZOHO_FIELD_ID] == 'Record_Image') {
                if ($field instanceof File) {
                    $field->setData('result', $result);
                    /** @var FileDropzoneInterface[] $files */
                    $files = $field->getFilteredFieldValue();
                    foreach ($files as $file) {
                        $value = [
                            'path' => $file->getFullPath(),
                            'type' => $file->getMimeType(),
                            'name' => $file->getName()
                        ];
                        break;
                    }
                } elseif ($field instanceof Gallery) {
                    $images = $field->parseValue($value);
                    foreach ($images as $imageId) {
                        $file = $this->fileGalleryRepository->getById((int)$imageId);
                        $value = [
                            'path' => $file->getFullPath(),
                            'type' => $file->getMimeType(),
                            'name' => $file->getName()
                        ];
                    }
                }
                if ($value) {
                    $data['photo'] = [
                        'field' => $mapField[FieldInterface::ID],
                        'value' => $value
                    ];
                }
            } elseif ($field instanceof File) {
                $field->setData('result', $result);

                /** @var FileDropzoneInterface[] $files */
                $files = $field->getFilteredFieldValue();
                $value = [];
                foreach ($files as $file) {
                    $value[] = [
                        'path' => $file->getFullPath(),
                        'type' => $file->getMimeType(),
                        'name' => $file->getName()
                    ];
                }
                if ($value) {
                    $data['files'][] = [
                        'field' => $mapField[ZohoIntegrationSettings::ZOHO_FIELD_ID],
                        'value' => $value
                    ];
                }
            } elseif ($field instanceof Gallery) {
                $value = [];
                $images = $field->parseValue($value);
                foreach ($images as $imageId) {
                    $file = $this->fileGalleryRepository->getById((int)$imageId);
                    $value[] = [
                        'path' => $file->getFullPath(),
                        'type' => $file->getMimeType(),
                        'name' => $file->getName()
                    ];
                }
                if ($value) {
                    $data['files'][] = [
                        'field' => $mapField[ZohoIntegrationSettings::ZOHO_FIELD_ID],
                        'value' => $value
                    ];
                }
            } else {
                $value                                                           = $field->getValueForResultTemplate(
                    $values[$mapField[FieldInterface::ID]],
                    $result->getId(),
                    ['date_format' => 'yyyy-MM-dd']
                );
                $data['lead'][$mapField[ZohoIntegrationSettings::ZOHO_FIELD_ID]] = $value;
            }
        }
        return $data;
    }
}