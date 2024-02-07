<?php


namespace GlobalColours\Stripe\Plugin;

use StripeIntegration\Payments\Controller\Payment\Index;
use Magento\Framework\App\ResponseInterface;
use StripeIntegration\Payments\Helper\Generic;
use StripeIntegration\Payments\Model\PaymentElement;
use Magento\Framework\Controller\ResultFactory;
// use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
// use Magento\Framework\Exception\NoSuchEntityException;
// use Magento\Framework\Exception\LocalizedException;

class PaymentPlugin
{

    /**
     * @var PaymentElement
     */
    private $paymentElement;


    /**
     * @var Generic
     */
    private $helper;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    // /**
    //  * @var QuoteIdToMaskedQuoteIdInterface
    //  */
    // private $quoteIdToMaskedQuoteId;



    public function __construct(
        PaymentElement $paymentElement,
        Generic $helper,
        ResultFactory $resultFactory,
        // QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
    ) {
        $this->paymentElement = $paymentElement;
        $this->helper = $helper;
        // $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->resultFactory        = $resultFactory;
    }

    public function afterExecute(Index $index, ?ResponseInterface $result)
    {

        $paymentMethodType = $index->getRequest()->getParam('payment_method');

        if ($paymentMethodType !== 'stripe_checkout')
            return $this->returnFromPaymentElement($index->getRequest()->getParam('payment_intent'), $index->getRequest()->getParam('redirect_status'));
    }

    private function returnFromPaymentElement(string $paymentIntentId, string $status)
    {

        $this->paymentElement->load($paymentIntentId, 'payment_intent_id');
        $orderIncrementId = $this->paymentElement->getOrderIncrementId();


        // $order = $this->orderFactory->create()->load($orderIncrementId);
        $order = $this->helper->loadOrderByIncrementId($orderIncrementId);

        $return_url = $order->getData("stripe_return_url");
        $cancel_url = $order->getData("stripe_cancel_url");

        if ($return_url && $cancel_url) {
            $resultRedirect = $this->resultFactory->create(
                ResultFactory::TYPE_REDIRECT
            );

            $url = $status == "failed" ? $cancel_url : $return_url;

            $url = $this->addQueryToUrl($url, "order_number=" . $orderIncrementId);
            $url = $this->addQueryToUrl($url, "redirect_status=" . $status);

            $result =  $resultRedirect->setUrl($url);

            return $result;
        }
    }

    private function addQueryToUrl(string $url, string $queryData)
    {
        $query = parse_url($url, PHP_URL_QUERY);

        // Returns a string if the URL has parameters or NULL if not
        if ($query) {
            return $url .= '&' . $queryData;
        } else {
            return $url .= '?' . $queryData;
        }
    }

    // /**
    //  * get Masked id by Quote Id
    //  *
    //  * @return string|null
    //  * @throws LocalizedException
    //  */
    // public function getQuoteMaskId($quoteId)
    // {
    //     $maskedId = null;
    //     try {
    //         $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
    //     } catch (NoSuchEntityException $exception) {
    //         throw new LocalizedException(__('Current user does not have an active cart.'));
    //     }

    //     return $maskedId;
    // }
}
