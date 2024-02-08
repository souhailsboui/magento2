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

namespace MageMe\WebForms\Ui\Component\Form\Listing\Column;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Helper\Statistics\FormStat;
use MageMe\WebForms\Helper\Statistics\ResultStat;
use MageMe\WebForms\Helper\StatisticsHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Results extends Column
{
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;
    /**
     * @var StatisticsHelper
     */
    private $statisticsHelper;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Results constructor.
     *
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param StatisticsHelper $statisticsHelper
     * @param ResultRepositoryInterface $resultRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct
    (
        RequestInterface          $request,
        UrlInterface              $urlBuilder,
        StatisticsHelper          $statisticsHelper,
        ResultRepositoryInterface $resultRepository,
        ContextInterface          $context,
        UiComponentFactory        $uiComponentFactory,
        array                     $components = [],
        array                     $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resultRepository = $resultRepository;
        $this->statisticsHelper = $statisticsHelper;
        $this->urlBuilder       = $urlBuilder;
        $this->request          = $request;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->statisticsHelper->getConfigStatisticEnabled()) {
                    $item[$fieldName] = $this->getStatHtml($item);
                } else {
                    $item[$fieldName] = $this->resultRepository->getListByFormId($item[FormInterface::ID])->getTotalCount();
                }
            }
        }
        return $dataSource;
    }


    /**
     * @param array $item
     * @return string
     */
    private function getStatHtml(array $item): string
    {
        $stat          = json_decode($item[StatisticsHelper::STATISTICS] ?? "", true) ?? [];
        $showNullStats = $this->statisticsHelper->getConfigStatisticShowNullStats();
        $html          = '';
        if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_ALL)) {
            $count = (int)($stat[FormStat::RESULT_ALL] ?? 0);
            $html  .= $this->getLinkHtml([
                'store' => $this->request->getParam('store'),
                ResultInterface::FORM_ID => $item[FormInterface::ID],
                'all' => 1
            ], __('All'), $count);
        }
        if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_UNREAD)) {
            $count = (int)($stat[FormStat::RESULT_UNREAD] ?? 0);
            if ($showNullStats || $count > 0) {
                $html .= $this->getLinkHtml([
                    'store' => $this->request->getParam('store'),
                    ResultInterface::FORM_ID => $item[FormInterface::ID],
                    ResultInterface::IS_READ => 0
                ], __('Unread'), $count);
            }
        }
        if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_REPLIED)) {
            $count = (int)($stat[FormStat::RESULT_REPLIED] ?? 0);
            if ($showNullStats || $count > 0) {
                $html .= $this->getLinkHtml([
                    'store' => $this->request->getParam('store'),
                    ResultInterface::FORM_ID => $item[FormInterface::ID],
                    ResultInterface::IS_REPLIED => 1
                ], __('Replied'), $count);
            }
        }
        if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_FOLLOW_UP)) {
            $count = (int)($stat[FormStat::RESULT_FOLLOW_UP] ?? 0);
            if ($showNullStats || $count > 0) {
                $html .= $this->getLinkHtml([
                    'store' => $this->request->getParam('store'),
                    ResultInterface::FORM_ID => $item[FormInterface::ID],
                    ResultStat::IS_UNREAD_REPLY => 1
                ], __('Follow Up'), $count);
            }
        }
        if ($item[FormInterface::IS_APPROVAL_CONTROLS_ENABLED]) {
            if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_STATUS_NOT_APPROVED)) {
                $count = (int)($stat[FormStat::RESULT_STATUS_NOT_APPROVED] ?? 0);
                if ($showNullStats || $count > 0) {
                    $html .= $this->getLinkHtml([
                        'store' => $this->request->getParam('store'),
                        ResultInterface::FORM_ID => $item[FormInterface::ID],
                        ResultInterface::APPROVED => ApprovalStatus::STATUS_NOT_APPROVED
                    ], __('Not Approved'), $count);
                }
            }
            if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_STATUS_PENDING)) {
                $count = (int)($stat[FormStat::RESULT_STATUS_PENDING] ?? 0);
                if ($showNullStats || $count > 0) {
                    $html .= $this->getLinkHtml([
                        'store' => $this->request->getParam('store'),
                        ResultInterface::FORM_ID => $item[FormInterface::ID],
                        ResultInterface::APPROVED => ApprovalStatus::STATUS_PENDING
                    ], __('Pending'), $count);
                }
            }
            if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_STATUS_APPROVED)) {
                $count = (int)($stat[FormStat::RESULT_STATUS_APPROVED] ?? 0);
                if ($showNullStats || $count > 0) {
                    $html .= $this->getLinkHtml([
                        'store' => $this->request->getParam('store'),
                        ResultInterface::FORM_ID => $item[FormInterface::ID],
                        ResultInterface::APPROVED => ApprovalStatus::STATUS_APPROVED
                    ], __('Approved'), $count);
                }
            }
            if ($this->statisticsHelper->getStatEnabled(FormStat::RESULT_STATUS_COMPLETED)) {
                $count = (int)($stat[FormStat::RESULT_STATUS_COMPLETED] ?? 0);
                if ($showNullStats || $count > 0) {
                    $html .= $this->getLinkHtml([
                        'store' => $this->request->getParam('store'),
                        ResultInterface::FORM_ID => $item[FormInterface::ID],
                        ResultInterface::APPROVED => ApprovalStatus::STATUS_COMPLETED
                    ], __('Completed'), $count);
                }
            }
        }
        return $html;
    }

    /**
     * @param array $params
     * @param string $label
     * @param int $count
     * @return string
     */
    private function getLinkHtml(array $params, string $label, int $count): string
    {
        return sprintf("<div><a href='javascript:void(0)' onclick='window.open(\"%s\", \"_blank\")'>%s (%s)</a></div>",
            $this->getUrl('webforms/result', $params),
            $label,
            $count
        );
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

}
