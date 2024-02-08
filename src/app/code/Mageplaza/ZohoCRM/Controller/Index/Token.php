<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ZohoCRM
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Mageplaza\ZohoCRM\Helper\Data as HelperData;
use Laminas\Http\Request;

/**
 * Class Token
 * @package Mageplaza\ZohoCRM\Controller\Index
 */
class Token extends Action
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * Token constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->session    = $session;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        if ($code) {
            $clientId = $this->helperData->getClientId();
            if (!$clientId) {
                $this->session->setMpZohoErrorMessage('Please fill Client Id!');

                return $this->_redirect('mpzoho/');
            }

            $clientSecret = $this->helperData->getClientSecret();
            if (!$clientSecret) {
                $this->session->setMpZohoErrorMessage('Please fill Client Secret');

                return $this->_redirect('mpzoho/');
            }

            $redirectURI = $this->helperData->getRedirectURIs();
            if (!$redirectURI) {
                $this->session->setMpZohoErrorMessage(__('Please fill Authorized redirect URIs'));

                return $this->_redirect('mpzoho/');
            }

            $params = [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectURI,
                'grant_type'    => 'authorization_code',
                'code'          => $code
            ];

            try {
                $resp = $this->helperData->requestData(
                    $this->helperData->getTokenURL(),
                    Request::METHOD_POST,
                    http_build_query($params),
                    true
                );

                if ($resp && is_array($resp) && isset($resp['access_token'])) {
                    $this->helperData->saveAPIData($resp);
                    $this->session->setMpZohoSuccessMessage(
                        __('The Access Token has been saved successfully. You can close this window, then clean cache and refresh configuration page.')
                    );
                } else {
                    $this->session->setMpZohoErrorMessage(__('Invalid access token. Please try again!'));
                }
            } catch (Exception $e) {
                $this->session->setMpZohoErrorMessage($e->getMessage());
            }
        } else {
            $this->session->setMpZohoErrorMessage(__('Grant token not found!'));
        }

        return $this->_redirect('mpzoho/');
    }
}
