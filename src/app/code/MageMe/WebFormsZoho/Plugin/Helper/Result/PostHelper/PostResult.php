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

namespace MageMe\WebFormsZoho\Plugin\Helper\Result\PostHelper;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Helper\Result\PostHelper;
use MageMe\WebFormsZoho\Helper\Zoho\Crm\AddLead;
use MageMe\WebFormsZoho\Helper\Zoho\Desk\AddTicket;
use Magento\Framework\Exception\NoSuchEntityException;

class PostResult
{
    /**
     * @var AddLead
     */
    private $addLead;
    /**
     * @var AddTicket
     */
    private $addTicket;

    /**
     * @param AddTicket $addTicket
     * @param AddLead $addLead
     */
    public function __construct(AddTicket $addTicket, AddLead $addLead)
    {
        $this->addLead = $addLead;
        $this->addTicket = $addTicket;
    }

    /**
     * @param PostHelper $postHelper
     * @param array $data
     * @param FormInterface|\MageMe\WebFormsZoho\Api\Data\FormInterface $form
     * @param array $config
     * @return array
     * @noinspection PhpUnusedParameterInspection
     * @throws NoSuchEntityException
     */
    public function afterPostResult(PostHelper $postHelper, array $data, FormInterface $form, array $config = []): array
    {
        if (!$data['success'] || !($data['model'] instanceof ResultInterface)) {
            return $data;
        }
        $result = $data['model'];
        if ($form->getZohoCrmIsLeadEnabled()) {
            $this->addLead->execute($result);
        }
        if ($form->getZohoDeskIsTicketEnabled()) {
            $this->addTicket->execute($result);
        }
        return $data;
    }

}