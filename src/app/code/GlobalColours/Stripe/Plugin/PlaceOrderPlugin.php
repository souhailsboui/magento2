<?php


namespace GlobalColours\Stripe\Plugin;

use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use StripeIntegration\Payments\Helper\Generic;



class PlaceOrderPlugin
{
    /**
     * @var Generic
     */
    private $helper;


    public function __construct(
        Generic $helper,
    ) {
        $this->helper = $helper;
    }

    public function afterResolve(PlaceOrder $placeOrder, $result, Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderIncrementId = $result["order"]["order_number"];
        if (
            empty($orderIncrementId) ||
            empty($args['input']['stripe_return_url']) ||
            empty($args['input']['stripe_return_url']) ||
            !$this->isValidUrl($args['input']['stripe_return_url']) ||
            !$this->isValidUrl($args['input']['stripe_cancel_url'])
        ) {
            return $result;
        };
        $order = $this->helper->loadOrderByIncrementId($orderIncrementId);

        $order->setData("stripe_return_url", $args['input']['stripe_return_url']);
        $order->setData("stripe_cancel_url", $args['input']['stripe_cancel_url']);

        $order->save();

        return $result;
    }

    private function isValidUrl(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }

        return false;
    }
}
