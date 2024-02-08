<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Manifest;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;
    public $_collectionFactory;
    public $ausposteParcelInfoHelper;
    public $ausposteParcelHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Biztech\Ausposteparcel\Model\Cresource\Manifest\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Biztech\Ausposteparcel\Helper\Info $ausposteParcelInfoHelper,
        \Biztech\Ausposteparcel\Helper\Data $ausposteParcelHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_collectionFactory = $collectionFactory;
        $this->moduleManager = $moduleManager;
        $this->ausposteParcelInfoHelper = $ausposteParcelInfoHelper;
        $this->ausposteParcelHelper = $ausposteParcelHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('biztech_ausposteparcel/manifest/index', ['_current' => true]);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('manifestGrid');
        $this->setDefaultSort('manifest_number');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->ausposteparcelHelper = $objectManager->get('Biztech\Ausposteparcel\Helper\Data');
            $this->messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');

            if (!empty($this->ausposteparcelHelper->getAllWebsites())) {
                if ($this->scopeConfig->getValue('carriers/ausposteParcel/manifestSync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) {
                    $manifest_collection = $this->_collectionFactory->getLastItem();
                    if ($manifest_collection->getDespatchDate() == '' || $manifest_collection->getDespatchDate() == null) {
                        $manifestNumber = $manifest_collection->getManifestNumber();
                        if ($manifestNumber) {
                            $consignmentArticleCount = $this->ausposteParcelInfoHelper->getConsignmentArticleByManifestNumber($manifestNumber);
                            $numberOfArticles = (int) $consignmentArticleCount['numberOfArticles'];
                            $numberOfConsignments = (int) $consignmentArticleCount['numberOfConsignments'];
                            $this->ausposteParcelHelper->updateManifest($manifestNumber, $numberOfArticles, $numberOfConsignments);
                            $config_model = $objectManager->get('\Magento\Framework\App\Config\Storage\WriterInterface');
                            $config_model->save('carriers/ausposteParcel/manifestSync', 0, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                        }
                    }
                }
                $collection = $this->_collectionFactory->load();
                $this->setCollection($collection);
                return parent::_prepareCollection();
            } else {
                $this->messageManager->addError(__('Extension- Australia Post Parcel Send is not enabled. Please enable it from Store > Configuration > Sales > Shipping Methods -> Appjetty Australia Post Parcel Send.'));
                return $this;
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $this;
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('manifest_number', [
            'header' => __('Manifest #'),
            'align' => 'center',
            'index' => 'manifest_number',
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Number',
            'sortable' => true
        ]);
        $this->addColumn('despatch_date', [
            'header' => __('Dispatched On'),
            'type' => 'datetime',
            'index' => 'despatch_date',
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Date',
            'sortable' => true
        ]);

        $this->addColumn('number_of_consignments', [
            'header' => __('No. of Consignments'),
            'index' => 'number_of_consignments',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Totalconsignments',
            'filter' => false
        ]);

        $this->addColumn('number_of_articles', [
            'header' => __('No. of Articles'),
            'index' => 'number_of_articles',
            'sortable' => true,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Totalarticles',
            'filter' => false
        ]);

        $this->addColumn('label', [
            'header' => __('Print'),
            'index' => 'label',
            'sortable' => false,
            'renderer' => 'Biztech\Ausposteparcel\Block\Adminhtml\Renderer\Manifest\Labelprint',
            'filter' => false
        ]);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('manifest_id');
        $this->getMassactionBlock()->setFormFieldName('manifest_id');

        $this->getMassactionBlock()->addItem(
            'create',
            [
            'label' => __('Download Manifest Summary'),
            'url' => $this->getUrl('*/*/massDownloadLabels')
                ]
        );
        return $this;
    }
}
