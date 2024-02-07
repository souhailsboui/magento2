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

namespace MageMe\WebFormsCustomerRegistration\Helper;

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model;
use MageMe\WebForms\Model\Repository\FieldRepository;
use MageMe\WebForms\Model\ResourceModel\Result;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\LoginAsCustomerAssistance\Api\SetAssistanceInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;


/**
 *
 */
class RegisterHelper extends AbstractHelper
{

    /** @var CustomerFactory */
    protected $customerFactory;

    /** @var CustomerResource */
    protected $customerResource;

    /** @var Model\ResourceModel\Result */
    protected $resultResource;

    /** @var AddressFactory */
    protected $addressFactory;

    /** @var StoreManager */
    protected $storeManager;

    /** @var Random */
    protected $random;

    /** @var  Model\Result */
    protected $result;

    /** @var Data */
    protected $helper;

    /** @var array */
    protected $crMap;

    /**
     * @var Model\Repository\FieldRepository
     */
    protected $fieldRepository;

    /**
     * @var SetAssistanceInterface
     */
    private $assistance;

    /**
     * @param SetAssistanceInterface $assistance
     * @param CustomerFactory $customerFactory
     * @param CustomerResource $customerResource
     * @param AddressFactory $addressFactory
     * @param StoreManager $storeManager
     * @param Random $random
     * @param Data $helper
     * @param FieldRepository $fieldRepository
     * @param Result $resultResource
     * @param Context $context
     */
    public function __construct(
        SetAssistanceInterface           $assistance,
        CustomerFactory                  $customerFactory,
        CustomerResource                 $customerResource,
        AddressFactory                   $addressFactory,
        StoreManager                     $storeManager,
        Random                           $random,
        Data                             $helper,
        Model\Repository\FieldRepository $fieldRepository,
        Model\ResourceModel\Result       $resultResource,
        Context                          $context
    )
    {
        parent::__construct($context);
        $this->customerFactory  = $customerFactory;
        $this->customerResource = $customerResource;
        $this->addressFactory   = $addressFactory;
        $this->storeManager     = $storeManager;
        $this->random           = $random;
        $this->helper           = $helper;
        $this->resultResource   = $resultResource;
        $this->fieldRepository  = $fieldRepository;
        $this->assistance       = $assistance;
    }

    /**
     * @param Model\Result $result
     * @param null $groupId
     * @param bool $approved
     * @return false|Customer
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function registerCustomer(Model\Result $result, $groupId = null, bool $approved = false)
    {
        $this->result = $result;

        $form    = $result->getForm();
        $storeId = $result->getData(ResultInterface::STORE_ID);

        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);

        $email = $result->getCustomerEmail();
        if (!$email) {
            return false;
        }

        $customer = $this->customerFactory->create();
        $customer->setStore($store);
        $customer->loadByEmail($email[0]);
        if ($customer->getId() && !$approved) {
            return false;
        }

        if (!$groupId) {
            $groupId = $store->getConfig('customer/create_account/default_group');
        }
        $customer->setGroupId($groupId);

        $crMap       = $form->getCrMap();
        $this->crMap = $crMap;

        if (!isset($crMap['customer'])) {
            return false;
        }

        // set customer attributes
        foreach ($crMap['customer'] as $code => $fieldId) {
            if ($fieldId) {
                $customer->setData($code, $result->getData('field_' . $fieldId));
            }
        }

        // set password
        $password = !empty($crMap['customer']['password']) ?
            $result->getData('field_' . $crMap['customer']['password']) :
            false;
        if (!$password) {
            $password = $this->random->getRandomString(8);
        }
        if ($password) {
            $passwordFieldId = (int)$crMap['customer']['password'];
            if ($passwordFieldId) {
                try {
                    $field = $this->fieldRepository->getById($passwordFieldId);
                } catch (LocalizedException $exception) {
                    $field = false;
                }
                if ($field && $field->getIsEncrypt()) {
                    $customer->setData('password', '');
                    $customer->setPasswordHash($password);
                } else {
                    $customer->setPassword($password);
                }
            } else {
                $customer->setPassword($password);
            }
        }

        // set custom attributes
        if (isset($crMap['custom_attribute'])) {
            $customAttributes = is_array($crMap['custom_attribute']) ? $crMap['custom_attribute'] : json_decode($crMap['custom_attribute']);
            if (is_array($customAttributes))
                foreach ($customAttributes as $fieldId) {
                    if ($fieldId) {
                        $field = $this->fieldRepository->getById($fieldId);
                        if ($field->getCode()) {
                            $customer->setData($field->getCode(), $result->getData('field_' . $fieldId));
                        }
                    }
                }
        }

        $customer->setStore($store)->save();

        if ($customer->getId()) {
            // update result
            $this->resultResource->save($result->setData(ResultInterface::CUSTOMER_ID, $customer->getId()));

            // create billing address profile
            if (isset($crMap['billing'])) {
                $saveBilling    = false;
                $billingAddress = $this->addressFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setData('is_default_billing', '1')
                    ->setData('is_default_shipping', '1');
                $billingAddress->setSaveInAddressBook('1');
                foreach ($crMap['billing'] as $code => $fieldId) {
                    if ($fieldId) {
                        $value = $result->getData('field_' . $fieldId);
                        if ($value) {
                            $saveBilling = true;
                            $billingAddress->setData($code, $value);
                        }
                    }
                }
                $billingAddress->setData('street', $this->getStreet());
                $billingAddress = $this->setRegion($billingAddress);

                if ($saveBilling) {
                    $billingAddress->save();
                }
            }

            // create shipping address profile
            if (isset($crMap['shipping'])) {
                $saveShipping    = false;
                $shippingAddress = $this->addressFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setData('is_default_shipping', '1');
                $shippingAddress->setSaveInAddressBook('1');
                foreach ($crMap['shipping'] as $code => $fieldId) {
                    if ($fieldId) {
                        $value = $result->getData('field_' . $fieldId);
                        if ($value) {
                            $saveShipping = true;
                            $shippingAddress->setData($code, $value);
                        }
                    }
                }
                $shippingAddress->setData('street', $this->getStreet('shipping'));
                $shippingAddress = $this->setRegion($shippingAddress, 'shipping');

                if ($saveShipping) {
                    $shippingAddress->save();
                }
            }

            // set additional attributes
            if (isset($crMap['additional'])) {
                foreach ($crMap['additional'] as $code => $fieldId) {
                    if ($fieldId) {
                        if ($code == 'assistance_allowed') {
                            $this->assistance->execute($customer->getId(), (bool)$result->getData('field_' . $fieldId));
                        }
                    }
                }
            }

            // set data for default email notifications
            $customer->setData('id', $customer->getId());
            $customer->setData('name', $customer->getName());

            // send welcome email
            if ($form->getCrIsDefaultNotificationEnabled()) {
                try {
                    $type = 'registered';
                    $customer->sendNewAccountEmail($type, '', $storeId);
                } catch (LocalizedException $e) {
                }
            }

            // send activation email
            if ($customer->isConfirmationRequired()) {
                try {
                    $type = 'confirmation';
                    $customer->sendNewAccountEmail($type, '', $storeId);
                } catch (LocalizedException $e) {
                }
            }
            return $customer;
        }
        return false;
    }

    /**
     * @param string $type
     * @return array|false
     */
    protected function getStreet($type = 'billing')
    {
        if (empty($this->result) || empty($this->crMap[$type])) {
            return false;
        }

        $street = [];

        foreach ($this->crMap[$type] as $code => $fieldId) {
            if (strstr((string)$code, 'street') && $fieldId) {
                $value = $this->result->getData('field_' . $fieldId);
                if ($value) {
                    $street[] = $value;
                }
            }
        }

        return $street;
    }

    /**
     * @param $address
     * @param string $type
     * @return mixed
     */
    protected function setRegion($address, string $type = 'billing')
    {
        if (empty($this->result) || empty($this->crMap[$type])) {
            return false;
        }

        foreach ($this->crMap[$type] as $code => $fieldId) {
            if (strstr((string)$code, 'region') && $fieldId) {
                $value = json_decode($this->result->getData('field_' . $fieldId), true);
                if (isset($value['region'])) {
                    $address->setRegion($value['region']);
                }
                if (isset($value['region_id'])) {
                    $address->setRegionId($value['region_id']);
                }
            }
        }
        return $address;
    }
}
