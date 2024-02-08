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

namespace MageMe\WebForms\Controller\Adminhtml\Result\Customer;


use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Mail\AdminNotification;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use MageMe\WebForms\Model\Result;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;

class MassEmail extends AbstractAjaxCustomerMassAction
{
    const ACTION = 'email';
    /**
     * @var AdminNotification
     */
    private $adminNotification;

    public function __construct(
        AdminNotification         $adminNotification,
        ResultRepositoryInterface $repository,
        CollectionFactory         $collectionFactory,
        Filter                    $filter, JsonFactory $jsonFactory,
        Context                   $context)
    {
        parent::__construct($repository, $collectionFactory, $filter, $jsonFactory, $context);
        $this->adminNotification = $adminNotification;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        $contact = false;
        $email   = $this->getRequest()->getParam('input');
        if ($email) {
            $contact = [
                'name'  => $email,
                'email' => $email
            ];
        }

        if($contact) {
            /** @var Result $result */
            foreach ($collection as $result) {
                $item = $this->repository->getById($result->getId());
                $this->adminNotification->sendEmail($item, $contact);
            }
        }
        return __('A total of %1 record(s) have been emailed.', $collection->getSize());
    }
}
