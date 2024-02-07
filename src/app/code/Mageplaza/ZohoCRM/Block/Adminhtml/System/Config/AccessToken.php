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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ZohoCRM\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ZohoCRM\Helper\Data as HelperData;

/**
 * Class AccessToken
 * @package Mageplaza\ZohoCRM\Block\Adminhtml\System\Config
 */
class AccessToken extends Field
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_ZohoCRM::system/config/access_token.phtml';

    /**
     * AccessToken constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperData = $helperData;
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        /**
         * @var Button $button
         */
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id'    => 'access_token_button',
                'label' => __('Get Access Token'),
                'class' => 'primary',
            ]
        );

        return $button->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAccessTokenUrl()
    {
        $url = $this->helperData->getAccountZohoUrl() . 'oauth/v2/auth?';
        $url .= 'scope=ZohoCRM.modules.ALL,ZohoCRM.settings.ALL';
        $url .= '&client_id=' . $this->helperData->getClientId();
        $url .= '&response_type=code&access_type=offline&prompt=consent';
        $url .= '&redirect_uri=' . $this->getAuthorizedRedirectURIs();

        return $url;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorizedRedirectURIs()
    {
        return $this->helperData->getAuthorizedRedirectURIs();
    }
}
