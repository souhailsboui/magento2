<?php

namespace Machship\Fusedship\Controller\Address;

use Magento\Customer\Controller\Address;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Address implements HttpPostActionInterface
{


    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('customer/address/index');
            return;
        }

        try {
            $postData = $this->getRequest()->getPostValue();

            // Retrieve the custom field value from the form data
            $cfIsResidential = isset($postData['is_residential']) ? $postData['is_residential'] : '';

            // Save the custom field value to the customer address
            // Replace the logic below with your actual saving mechanism
            $addressId = $postData['id'];
            $address = $this->addressRepository->getById($addressId);
            $address->setIsResidential($cfIsResidential);



            $this->addressRepository->save($address);

            $this->messageManager->addSuccessMessage(__('Address saved successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the address.'));
        }

        $this->_redirect('customer/address/index');
    }
}
