<?php

// namespace Biztech\Ausposteparcel\Helper;
namespace Biztech\Ausposteparcel\Controller\Adminhtml\Manifest;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Article extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $info;
    protected $scopeconfiginterface;
    private $_objectManager;

    public function __construct(Context $context, \Biztech\Ausposteparcel\Helper\Info $info, \Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        parent::__construct($context);
        $this->info = $info;
        $this->scopeconfiginterface = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
    }
    public function prepareArticleData($data, $order, $consignment_number = '')
    {
        $deliveryAddress = $order->getShippingAddress()->getData();
        $returnAddress = $this->prepareReturnAddress();
        $deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress, $order);
        $articlesInfo = $this->prepareArticles($data, $order);

        $search = array(
            '[[articles]]',
            '[[RETURN-ADDRESS]]',
            '[[DELIVERY-ADDRESS]]',
            '[[CUSTOMER-EMAIL]]',
            '[[DELIVERY-SIGNATURE]]',
            '[[ORDER-ID]]',
            '[[CHARGE-CODE]]',
            '[[SHIPMENT-ID]]',
            '[[DANGER-GOODS]]',
            '[[printReturnLabels]]',
            '[[deliverPartConsignment]]',
            '[[cashToCollect]]',
            '[[cashToCollectAmount]]',
            '[[emailNotification]]'
        );

        $chargeCode = $this->info->getChargeCode($order, $consignment_number);
        $replace = array(
            $articlesInfo['info'],
            $returnAddress,
            $deliveryInfo,
            $order->getCustomerEmail(),
            ($data['delivery_signature_allowed'] ? 'true' : 'false'),
            $this->info->getIncrementId($order),
            $chargeCode,
            $order->getId(),
            ($data['contains_dangerous_goods'] ? 'true' : 'false'),
            ($data['print_return_labels'] ? 'true' : 'false'),
            ($data['partial_delivery_allowed'] ? 'Y' : 'N'),
            (isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
            (isset($data['cash_to_collect']) ? '<cashToCollectAmount>' . number_format($data['cash_to_collect'], 2) . '</cashToCollectAmount>' : ''),
            ($data['email_notification'] ? 'Y' : 'N')
        );
        $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR . 'articles-template.xml');
        $articleData = str_replace($search, $replace, $template);
        return array('content' => $articleData, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
    }
    public function prepareReturnAddress()
    {
        $returnAddressLine2 = trim($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressLine2'));
        if (!empty($returnAddressLine2)) {
            $returnAddressLine2 = '<returnAddressLine2>' . trim($this->info->xmlData($returnAddressLine2)) . '</returnAddressLine2>';
        } else {
            $returnAddressLine2 = '<returnAddressLine2 />';
        }

        $returnAddressLine3 = trim($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressLine3'));
        if (!empty($returnAddressLine3)) {
            $returnAddressLine3 = '<returnAddressLine3>' . trim($this->info->xmlData($returnAddressLine3)) . '</returnAddressLine3>';
        } else {
            $returnAddressLine3 = '<returnAddressLine3 />';
        }

        $returnAddressLine4 = trim($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressLine4'));
        if (!empty($returnAddressLine4)) {
            $returnAddressLine4 = '<returnAddressLine4>' . trim($this->info->xmlData($returnAddressLine4)) . '</returnAddressLine4>';
        } else {
            $returnAddressLine4 = '<returnAddressLine4 />';
        }

        $search = array(
            '[[returnAddressLine1]]',
            '[[returnAddressName]]',
            '[[returnAddressPostcode]]',
            '[[returnAddressStateCode]]',
            '[[returnAddressSuburb]]',
            '[[returnAddressLine2]]',
            '[[returnAddressLine3]]',
            '[[returnAddressLine4]]'
        );

        $replace = array(
            trim($this->info->xmlData($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressLine1'))),
            trim($this->info->xmlData($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressName'))),
            trim($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressPostcode')),
            trim($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressStateCode')),
            trim($this->info->xmlData($this->scopeconfiginterface->getValue('carriers/ausposteParcel/returnAddressSuburb'))),
            trim($returnAddressLine2),
            trim($returnAddressLine3),
            trim($returnAddressLine4)
        );

        $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-return-address-template.xml');
        return str_replace($search, $replace, $template);
    }
    public function prepareDeliveryAddress($address, $order)
    {
        $street = $address['street'];
        $street = explode("\n", $street);

        $street1 = '<deliveryAddressLine1/>';
        $street2 = '<deliveryAddressLine2/>';
        $street3 = '<deliveryAddressLine3/>';
        $street4 = '<deliveryAddressLine4/>';
        if (isset($street[0]) && !empty($street[0])) {
            $street1 = '<deliveryAddressLine1>' . $this->info->xmlData($street[0]) . '</deliveryAddressLine1>';
        }
        if (isset($street[1]) && !empty($street[1])) {
            $street2 = '<deliveryAddressLine2>' . $this->info->xmlData($street[1]) . '</deliveryAddressLine2>';
        }
        if (isset($street[2]) && !empty($street[2])) {
            $street3 = '<deliveryAddressLine3>' . $this->info->xmlData($street[2]) . '</deliveryAddressLine3>';
        }
        if (isset($street[3]) && !empty($street[3])) {
            $street4 = '<deliveryAddressLine4>' . $this->info->xmlData($street[3]) . '</deliveryAddressLine4>';
        }

        $city = $address['city'];
        $state = 'NA';
        if ($address['region']) {
            $state = $this->info->getRegion($address['region_id']);
        }
        $postalCode = $address['postcode'];
        $company = empty($address['company']) ? '<deliveryCompanyName/>' : '<deliveryCompanyName>' . $this->info->xmlData($address['company']) . '</deliveryCompanyName>';
        $firstname = $address['firstname'] . ' ' . $address['lastname'];
        $email = $address['email'];
        $phone = $address['telephone'];

        $instructions = '';

        $search = array(
            '[[deliveryCompanyName]]',
            '[[deliveryName]]',
            '[[deliveryEmailAddress]]',
            '[[deliveryAddressLine1]]',
            '[[deliveryAddressLine2]]',
            '[[deliveryAddressLine3]]',
            '[[deliveryAddressLine4]]',
            '[[deliverySuburb]]',
            '[[deliveryStateCode]]',
            '[[deliveryPostcode]]',
            '[[deliveryPhoneNumber]]',
            '[[deliveryInstructions]]'
        );

        $replace = array(
            trim($company),
            trim($this->info->xmlData($firstname)),
            trim($email),
            trim($street1),
            trim($street2),
            trim($street3),
            trim($street4),
            trim($this->info->xmlData($city)),
            trim($state),
            trim($postalCode),
            trim($phone),
            ($instructions ? '<deliveryInstructions>' . $this->info->xmlData(base64_decode($instructions)) . '</deliveryInstructions>' : '<deliveryInstructions />')
        );

        $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR. 'article-delivery-address-template.xml');
        return str_replace($search, $replace, $template);
    }
    public function prepareArticles($data, $order)
    {
        $articlesInfo = '';
        $total_weight = 0;

        $number_of_articles = $data['number_of_articles'];
        $start_index = $data['start_index'];
        $end_index = $data['end_index'];
        $articles = array();

        for ($i = $start_index; $i <= $end_index; $i++) {
            if ($data['articles_type'] == 'Custom') {
                $article = $data['article' . $i];
            } else {
                $articles_type = $data['articles_type'];
                $articles = explode('<=>', $articles_type);
                $article = array();
                $article['description'] = trim($articles[0]);
                $article['weight'] = $articles[1];
                $article['height'] = $articles[2];
                $article['width'] = $articles[3];
                $article['length'] = $articles[4];
                $article['unit_value'] = $data['unit_value'];
                $use_order_total_weight = (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useTotalOrderWeight');
                if ($use_order_total_weight == 1) {
                    $weight = $this->info->getOrderWeight($order);
                    $weightPerArticle = $this->info->getAllowedWeightPerArticle();
                    if ($weight == 0) {
                        $default_article_weight = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/defaultWeight');
                        if ($default_article_weight) {
                            $weight = $default_article_weight;
                        }
                    }
                    $exactArticles = (int) ($weight / $weightPerArticle);
                    $totalArticles = $exactArticles;
                    $reminderWeight = fmod($weight, $weightPerArticle);
                    if ($reminderWeight > 0) {
                        $totalArticles++;
                    }

                    if ($totalArticles == 0) {
                        $totalArticles = 1;
                    }

                    if ($weight > $weightPerArticle) {
                        $weight = $weightPerArticle;
                    }
                    if ($reminderWeight > 0 && $i == $totalArticles) {
                        $weight = $reminderWeight;
                    }
                    $article['weight'] = $weight;
                }
            }

            $search = array(
                '[[actualWeight]]',
                '[[articleDescription]]',
                '[[height]]',
                '[[length]]',
                '[[width]]',
                '[[isTransitCoverRequired]]',
                '[[transitCoverAmount]]',
                '[[articleNumber]]',
                '[[unitValue]]'
            );
            $article['weight'] = number_format($article['weight'], 2, '.', '');
            $total_weight += $article['weight'];
            $replace = array(
                $article['weight'],
                $this->info->xmlData($article['description']),
                $article['height'],
                $article['length'],
                '<width>' . $article['width'] . '</width>',
                ($data['transit_cover_required'] ? 'Y' : 'N'),
                ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
                (isset($article['article_number']) ? '<articleNumber>' . $article['article_number'] . '</articleNumber>' : ''),
                (isset($article['unit_value']) ? $article['unit_value'] : '')
            );

            $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-template.xml');
            $articlesInfo .= str_replace($search, $replace, $template);
        }
        return array('info' => $articlesInfo, 'total_weight' => $total_weight);
    }
    public function prepareUpdateArticleData($data, $order, $consignment_number = '')
    {
        $deliveryAddress = $order->getShippingAddress()->getData();
        $returnAddress = $this->prepareReturnAddress();
        $deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress, $order);
        $articlesInfo = $this->prepareUpdatedArticles($order, $data);

        $search = array(
            '[[articles]]',
            '[[RETURN-ADDRESS]]',
            '[[DELIVERY-ADDRESS]]',
            '[[CUSTOMER-EMAIL]]',
            '[[DELIVERY-SIGNATURE]]',
            '[[ORDER-ID]]',
            '[[CHARGE-CODE]]',
            '[[SHIPMENT-ID]]',
            '[[DANGER-GOODS]]',
            '[[printReturnLabels]]',
            '[[deliverPartConsignment]]',
            '[[cashToCollect]]',
            '[[cashToCollectAmount]]',
            '[[emailNotification]]'
        );

        $chargeCode = $this->info->getChargeCode($order, $consignment_number);

        $replace = array(
            $articlesInfo['info'],
            $returnAddress,
            $deliveryInfo,
            $order->getCustomerEmail(),
            ($data['delivery_signature_allowed'] ? 'true' : 'false'),
            $this->info->getIncrementId($order),
            $chargeCode,
            $order->getId(),
            ($data['contains_dangerous_goods'] ? 'true' : 'false'),
            ($data['print_return_labels'] ? 'true' : 'false'),
            ($data['partial_delivery_allowed'] ? 'Y' : 'N'),
            (isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
            (isset($data['cash_to_collect']) ? '<cashToCollectAmount>' . number_format($data['cash_to_collect'], 2) . '</cashToCollectAmount>' : ''),
            ($data['email_notification'] ? 'Y' : 'N')
        );
        $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR . 'articles-template.xml');
        $content = str_replace($search, $replace, $template);
        return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
    }
    public function prepareUpdatedArticles($order, $data)
    {
        $articlesInfo = '';

        $total_weight = 0;

        $articles = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getArticles($order->getId(), $data['consignment_number']);
        foreach ($articles as $article) {
            $search = array(
                '[[actualWeight]]',
                '[[articleDescription]]',
                '[[height]]',
                '[[length]]',
                '[[width]]',
                '[[isTransitCoverRequired]]',
                '[[transitCoverAmount]]',
                '[[articleNumber]]',
                '[[unitValue]]'
            );

            if ($article['article_number'] == $data['article_number']) {
                $article = $data['article'];
                $article['weight'] = number_format($article['weight'], 2, '.', '');
                $total_weight += $article['weight'];
                $replace = array(
                    $article['weight'],
                    $this->info->xmlData($article['description']),
                    $article['height'],
                    $article['length'],
                    '<width>' . $article['width'] . '</width>',
                    ($data['transit_cover_required'] ? 'Y' : 'N'),
                    ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
                    '',
                    // $article['unit_value']
                );
            } else {
                $default_width = 0;
                $use_article_dimensions = (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useArticleDimensions');
                if ($use_article_dimensions == 1) {
                    $default_width = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/defaultWidth');
                }
                // $article['weight'] = number_format($article['weight'], 2, '.', '');
                $total_weight += $article['actual_weight'];
                $replace = array(
                    $article['actual_weight'],
                    trim($article['article_description']),
                    $article['height'],
                    $article['length'],
                    ($article['width'] ? '<width>' . $article['width'] . '</width>' : '<width>' . $default_width . '</width>'),
                    $article['is_transit_cover_required'],
                    ($article['transit_cover_amount'] ? $article['transit_cover_amount'] : 0),
                    '<articleNumber>' . $article['article_number'] . '</articleNumber>',
                    $article['unit_value']
                );
            }

            $template = file_get_contents($this->info->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-template.xml');
            $articlesInfo .= str_replace($search, $replace, $template);
        }
        return array('info' => $articlesInfo, 'total_weight' => $total_weight);
    }
    public function prepareAddArticleData($data, $order, $consignment_number = '')
    {
        $deliveryAddress = $order->getShippingAddress()->getData();
        $returnAddress = $this->prepareReturnAddress();
        $deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress, $order);
        $articlesInfo = $this->prepareAddArticles($order, $data);

        $search = array(
            '[[articles]]',
            '[[RETURN-ADDRESS]]',
            '[[DELIVERY-ADDRESS]]',
            '[[CUSTOMER-EMAIL]]',
            '[[DELIVERY-SIGNATURE]]',
            '[[ORDER-ID]]',
            '[[CHARGE-CODE]]',
            '[[SHIPMENT-ID]]',
            '[[DANGER-GOODS]]',
            '[[printReturnLabels]]',
            '[[deliverPartConsignment]]',
            '[[cashToCollect]]',
            '[[cashToCollectAmount]]',
            '[[emailNotification]]'
        );

        $chargeCode = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getChargeCode($order, $consignment_number);

        $replace = array(
            $articlesInfo['info'],
            $returnAddress,
            $deliveryInfo,
            $order->getCustomerEmail(),
            ($data['delivery_signature_allowed'] ? 'true' : 'false'),
            $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getIncrementId($order),
            $chargeCode,
            $order->getId(),
            ($data['contains_dangerous_goods'] ? 'true' : 'false'),
            ($data['print_return_labels'] ? 'true' : 'false'),
            ($data['partial_delivery_allowed'] ? 'Y' : 'N'),
            (isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
            (isset($data['cash_to_collect']) ? '<cashToCollectAmount>' . number_format($data['cash_to_collect'], 2) . '</cashToCollectAmount>' : ''),
            ($data['email_notification'] ? 'Y' : 'N')
        );
        $template = file_get_contents($this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getTemplatePath() . DIRECTORY_SEPARATOR . 'articles-template.xml');
        $content = str_replace($search, $replace, $template);

        return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
    }
    public function prepareAddArticles($order, $data)
    {
        $articlesInfo = '';

        $total_weight = 0;
        $articles = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getArticles($order->getId(), $data['consignment_number']);
        foreach ($articles as $article) {
            $search = array(
                '[[actualWeight]]',
                '[[articleDescription]]',
                '[[height]]',
                '[[length]]',
                '[[width]]',
                '[[isTransitCoverRequired]]',
                '[[transitCoverAmount]]',
                '[[articleNumber]]',
                '[[unitValue]]'
            );
            
            // $article['weight'] = number_format($article['weight'], 2, '.', '');
            $total_weight += $article['actual_weight'];

            $default_width = 0;
            $use_article_dimensions = (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useArticleDimensions');
            if ($use_article_dimensions == 1) {
                $default_width = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/defaultWidth');
            }

            $replace = array(
                $article['actual_weight'],
                trim($article['article_description']),
                $article['height'],
                $article['length'],
                ($article['width'] ? '<width>' . $article['width'] . '</width>' : '<width>' . $default_width . '</width>'),
                $article['is_transit_cover_required'],
                (($article['is_transit_cover_required'] == 'Y') ? $article['transit_cover_amount'] : 0),
                '<articleNumber>' . $article['article_number'] . '</articleNumber>',
                $article['unit_value']
            );

            $template = file_get_contents($this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-template.xml');
            $articlesInfo .= str_replace($search, $replace, $template);
        }

        $search = array(
            '[[actualWeight]]',
            '[[articleDescription]]',
            '[[height]]',
            '[[length]]',
            '[[width]]',
            '[[isTransitCoverRequired]]',
            '[[transitCoverAmount]]',
            '[[articleNumber]]',
            '[[unitValue]]'
        );

        if ($data['articles_type'] == 'Custom') {
            $article = $data['article'];
        } else {
            $articles_type = $data['articles_type'];
            $articles = explode('<=>', $articles_type);
            $article = array();
            $article['description'] = $articles[0];
            $article['weight'] = $articles[1];
            $article['height'] = $articles[2];
            $article['width'] = $articles[3];
            $article['length'] = $articles[4];
            $article['unit_value'] = $data['unit_value'];

            $use_order_total_weight = (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useTotalOrderWeight');
            if ($use_order_total_weight == 1) {
                $weight = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getOrderWeight($order);
                $weightPerArticle = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getAllowedWeightPerArticle();
                if ($weight == 0) {
                    $default_article_weight = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/defaultweight');
                    if ($default_article_weight) {
                        $weight = $default_article_weight;
                    }
                }
                if ($weight > $weightPerArticle) {
                    $weight = $weightPerArticle;
                }
                $article['weight'] = $weight;
            }
        }

        $article['weight'] = number_format($article['weight'], 2, '.', '');
        $total_weight += $article['weight'];

        $replace = array(
            $article['weight'],
            $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->xmlData($article['description']),
            $article['height'],
            $article['length'],
            '<width>' . $article['width'] . '</width>',
            ($data['transit_cover_required'] ? 'Y' : 'N'),
            ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
            '',
            ($article['unit_value'] ? $article['unit_value'] : 0),
        );

        $template = file_get_contents($this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-template.xml');
        $articlesInfo .= str_replace($search, $replace, $template);

        return array('info' => $articlesInfo, 'total_weight' => $total_weight);
    }
    public function prepareModifiedArticleData($order, $consignment_number = '')
    {
        $deliveryAddress = $order->getShippingAddress()->getData();
        $returnAddress = $this->prepareReturnAddress();
        $deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress, $order);
        $articlesInfo = $this->prepareModifiedArticles($order, $consignment_number);

        $consignment = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getConsignment($order->getId(), $consignment_number);
        $search = array(
            '[[articles]]',
            '[[RETURN-ADDRESS]]',
            '[[DELIVERY-ADDRESS]]',
            '[[CUSTOMER-EMAIL]]',
            '[[DELIVERY-SIGNATURE]]',
            '[[ORDER-ID]]',
            '[[CHARGE-CODE]]',
            '[[SHIPMENT-ID]]',
            '[[DANGER-GOODS]]',
            '[[printReturnLabels]]',
            '[[deliverPartConsignment]]',
            '[[cashToCollect]]',
            '[[cashToCollectAmount]]',
            '[[emailNotification]]'
        );

        $chargeCode = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getChargeCode($order, $consignment_number);
        $instructions = '';

        $replace = array(
            $articlesInfo['info'],
            $returnAddress,
            $deliveryInfo,
            $order->getCustomerEmail(),
            ($consignment['delivery_signature_allowed'] ? 'true' : 'false'),
            $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getIncrementId($order),
            $chargeCode,
            $order->getId(),
            ($consignment['contains_dangerous_goods'] ? 'true' : 'false'),
            ($consignment['print_return_labels'] ? 'true' : 'false'),
            ($consignment['partial_delivery_allowed'] ? 'Y' : 'N'),
            (!empty($consignment['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
            (!empty($consignment['cash_to_collect']) ? '<cashToCollectAmount>' . number_format($data['cash_to_collect'], 2) . '</cashToCollectAmount>' : '')
            // ($data['email_notification'] ? 'Y' : 'N')
        );
        $template = file_get_contents($this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getTemplatePath() . DIRECTORY_SEPARATOR . 'articles-template.xml');
        $content = str_replace($search, $replace, $template);
        return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
    }
    public function prepareModifiedArticles($order, $consignment_number)
    {
        $articlesInfo = '';

        $total_weight = 0;

        $articles = $this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getArticles($order->getId(), $consignment_number);
        foreach ($articles as $article) {
            $search = array(
                '[[actualWeight]]',
                '[[articleDescription]]',
                '[[height]]',
                '[[length]]',
                '[[width]]',
                '[[isTransitCoverRequired]]',
                '[[transitCoverAmount]]',
                '[[articleNumber]]'
            );

            $default_width = 0;
            $use_article_dimensions = (int) $this->scopeconfiginterface->getValue('carriers/ausposteParcel/useArticleDimensions');
            if ($use_article_dimensions == 1) {
                $default_width = $this->scopeconfiginterface->getValue('carriers/ausposteParcel/defaultwidth');
            }

            // $article['weight'] = number_format($article['weight'], 2, '.', '');
            $total_weight += $article['actual_weight'];

            $replace = array(
                $article['actual_weight'],
                trim($article['article_description']),
                $article['height'],
                $article['length'],
                ($article['width'] ? '<width>' . $article['width'] . '</width>' : '<width>' . $default_width . '</width>'),
                $article['is_transit_cover_required'],
                (($article['is_transit_cover_required'] == 'Y') ? $article['transit_cover_amount'] : 0),
                '<articleNumber>' . $article['article_number'] . '</articleNumber>'
            );

            $template = file_get_contents($this->_objectManager->create('Biztech\Ausposteparcel\Helper\Info')->getTemplatePath() . DIRECTORY_SEPARATOR . 'article-template.xml');
            $articlesInfo .= str_replace($search, $replace, $template);
        }
        return array('info' => $articlesInfo, 'total_weight' => $total_weight);
    }
}
