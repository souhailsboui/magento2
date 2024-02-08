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

namespace MageMe\WebFormsZoho\Ui\Component\Result\Listing\MassActions\SubActions;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class ZohoCrm extends Action
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param null $actions
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        RequestInterface        $request,
        UrlInterface            $urlBuilder,
        ContextInterface        $context,
        array                   $components = [],
        array                   $data = [],
                                $actions = null
    )
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder     = $urlBuilder;
        $this->request        = $request;
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $actions   = [];
        $actions[] = $this->getSendSubmissionMeta();

        $config                  = $this->getConfiguration();
        $config['actionDisable'] = $this->isDisabled();
        $this->setData('config', $config);

        $this->actions = $actions;
        parent::prepare();
    }

    /**
     * @return array
     */
    private function getSendSubmissionMeta(): array
    {
        return [
            'type' => 'zoho_send_submission',
            'label' => __('Send Lead'),
            'url' => $this->urlBuilder->getUrl(
                'webformszoho/result/ajaxCreateLead'
            ),
            'isAjax' => true,
            'confirm' => [
                'title' => __('Send Lead'),
                'message' => __('Are you sure?'),
                '__disableTmpl' => true,
            ],
        ];
    }

    /**
     * @return bool
     */
    protected function isDisabled(): bool
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$formId) {
            return true;
        }
        try {

            /** @var \MageMe\WebFormsZoho\Api\Data\FormInterface $form */
            $form = $this->formRepository->getById($formId);
            if (!$form->getZohoCrmIsLeadEnabled()) {
                return true;
            }
        } catch (NoSuchEntityException $e) {
            return true;
        }
        return false;
    }
}