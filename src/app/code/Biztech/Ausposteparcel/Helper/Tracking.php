<?php

namespace Biztech\Ausposteparcel\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Tracking extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $info;
    protected $scopeconfiginterface;
    private $_objectManager;
    protected $_trackFactory;

    public function __construct(Context $context, \Biztech\Ausposteparcel\Helper\Info $info, \Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory)
    {
        parent::__construct($context);
        $this->info = $info;
        $this->scopeconfiginterface = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
        $this->_trackFactory = $trackFactory;
    }

    public function addTrackingToShipment($trackingNumber, $shipmentReference, $shippingMethod, $trackingLabel = 'Auspost eParcel')
    {
        $retour = false;

        if (!$trackingNumber) {
            return false;
        }

        $shipmentReference = str_replace("\n", "", $shipmentReference);
        $shipmentReference = str_replace("\r", "", $shipmentReference);
        $shipment = $this->_objectManager->get('Magento\Sales\Model\Order\Shipment')->loadByIncrementId($shipmentReference);

        if ($this->shipmentContainsTracking($shipment, $trackingNumber)) {
            return false;
        }

        $debug = 'process tracking ' . $trackingNumber . ' for shipment #' . $shipmentReference . "\n";
        if ($shipment->getId()) {
            if (!$this->shipmentContainsTracking($shipment, $trackingNumber)) {
                try {
                    $debug .= 'import tracking=' . $trackingNumber . ' for shipment=' . $shipment->getincrement_id() . "\n";
                    $data = array(
                        'carrier_code' => $shippingMethod,
                        'title' => $trackingLabel,
                        'number' => $trackingNumber,
                    );

                    $track = $this->_trackFactory->create();
                    $track->setCarrierCode($shippingMethod);
                    $track->setTitle($trackingLabel);
                    $track->setTrackNumber($trackingNumber);
                    $shipment->addTrack($track)->save();

                    $retour = true;
                } catch (\Exception $ex) {
                    $retour = false;
                    return $debug .= 'Error  : ' . $ex->getMessage() . "\n";
                }
            } else {
                $retour = false;
                return $debug .= 'Tracking already exist' . "\n";
            }
        } else {
            $retour = false;
            $debug .= 'Unable to retrieve shipment' . "\n";
        }

        return $retour;
    }

    public function shipmentContainsTracking($shipment, $tracking)
    {
        $exist = false;

        if ($shipment->getOrder()) {
            foreach ($shipment->getOrder()->getTracksCollection() as $track) {
                if ($track->gettrack_number() == $tracking) {
                    $exist = true;
                }
            }
        }
        return $exist;
    }
}
