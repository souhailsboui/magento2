<?php
namespace Machship\Fusedship\Logger;

use DateTimeZone;

class Logger extends \Monolog\Logger
{

    private $enable;

    private $objectManager;
    private $fusedshipHelper;


    public function writeDebug($text, $data = []) {

        // always check properties
        $this->initProperties();

        // we need to check if this feature is enable or not
        if (!$this->enable) {
            // dont write anything
            return;
        }

        if (!empty($data)) {
            $text .= " : " . json_encode($data);
        }

        $this->info($text);
    }

    private function initProperties() {
        // To be safe and we dont have to handle the constructor
        // we do the initialization here instead

        if (empty($this->objectManager)) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        if (empty($this->fusedshipHelper)) {
            $this->fusedshipHelper = $this->objectManager->get('Machship\Fusedship\Helper\Data');
        }

        if (is_null($this->enable)) {
            $this->enable = $this->fusedshipHelper->isDebugEnabled() ?? false;
        }


    }

}