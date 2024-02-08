<?php

namespace MageMe\WebForms\Ui\Component\Form\Listing\Column;

use MageMe\WebForms\Helper\Statistics\FormStat;
use MageMe\WebForms\Helper\StatisticsHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Name extends Column
{
    /**
     * @param StatisticsHelper $statisticsHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct
    (
        StatisticsHelper   $statisticsHelper,
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->statisticsHelper = $statisticsHelper;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->statisticsHelper->showBadge()) {
                    $item[$fieldName] = $this->getStatHtml($item, $fieldName);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $item
     * @param string $fieldName
     * @return string
     */
    private function getStatHtml(array $item, string $fieldName): string
    {
        $stat  = json_decode($item[StatisticsHelper::STATISTICS] ?? "", true) ?? [];
        $value = (int)($stat[FormStat::RESULT_UNREAD] ?? 0);
        return sprintf('<div class="webforms-label">
                                            <span>%s</span>
                                            %s
                                        </div>',
            $item[$fieldName],
            $value > 0 ? "<span class='notifications-counter'>$value</span>" : ""
        );
    }
}