<?php
namespace Biztech\Ausposteparcel\Model\Config\Source;

use Biztech\Ausposteparcel\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class WebsiteStoreview implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    protected $helper;

 
    public function __construct(
        StoreManagerInterface $storeManager,
        Data $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = $this->_scopeConfig->getValue('ausposteParcel/activation/data');
        $getDataInfo = $this->helper->getDataInfo();
        if (isset($getDataInfo->dom) && (int) ($getDataInfo->c) > 0 && (int) ($getDataInfo->suc) == 1) {
            if (!$this->_options) {
                $this->_options = [];
                foreach ($this->_storeManager->getWebsites() as $website) {
                    $storeView = [];
                    foreach ($website->getGroups() as $group) {
                        $stores = $group->getStores();
                        foreach ($stores as $store) {
                            $url = $store->getConfig('web/unsecure/base_url');
                            $url = $this->helper->getFormatUrl($url);
                            foreach ($getDataInfo->dom as $web) {
                                if (isset($web->suc)) {
                                    if ($web->dom == $url && $web->suc == 1) {
                                        $id = $store->getId();
                                        $name = $store->getName();
                                        if ($id != 0) {
                                            $storeView[] = ['value' => $id, 'label' => __($name)];
                                        }
                                    
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($storeView)) {
                        $this->_options[] = [
                            'label' => __($website->getName()),
                            'value' => $storeView
                        ];
                    }
                }
            }
        }
        if (empty($this->_options)) {
            $this->_options[] = [
                'label' => __("Please Enter A valid licence Key"),
                'value' => '',
                // 'style' => "color: #fff;background-color: #dc3545;border-color: #dc3545; disabled='disabled'",
            ];
        }
        return $this->_options;
    }
}
