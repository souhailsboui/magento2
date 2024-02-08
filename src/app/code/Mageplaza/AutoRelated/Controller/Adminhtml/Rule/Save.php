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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

/**
 * Class Save
 * @package Mageplaza\AutoRelated\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $type = $this->getRequest()->getParam('type');
            $test = $this->getRequest()->getParam('test');

            try {
                $model = $this->autoRelatedRuleFactory->create();

                $id = $this->getRequest()->getParam('rule_id');
                if ($id && !$test) {
                    $model->load($id);
                    if (!$model->getId()) {
                        throw new LocalizedException(__('The wrong rule is specified.'));
                    }
                }

                $validateResult = $model->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_session->setPageData($data);
                    $this->_redirect('mparp/rule/edit', ['id' => $model->getId(), 'type' => $model->getBlockType()]);

                    return;
                }

                if (isset($data['rule'])) {
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    if (isset($data['rule']['similarity_actions'])) {
                        $data['similarity_actions'] = $data['rule']['similarity_actions'];
                    }
                    unset($data['rule']);
                }

                if (isset($data['category_conditions_serialized'])) {
                    $data['category_conditions_serialized'] = $this->helperData->serialize(explode(
                        ',',
                        $data['category_conditions_serialized']
                    ));
                }

                $data['block_type'] = $type;

                if ($id && $test) {
                    unset($data['rule_id']);
                    $data['parent_id'] = $id;
                }

                if ($data['location'] === 'left-popup-content' || $data['location'] === 'right-popup-content') {
                    $data['product_layout'] = 1;
                }

                if ($data['product_layout'] == 0) {
                    $sliderConfig      = [];
                    $sliderConfigField = [
                        'slider_width',
                        'slider_height',
                        'show_next_prev',
                        'show_dots_nav',
                        'slider_autoplay',
                        'auto_timeout',
                    ];
                    foreach ($sliderConfigField as $field) {
                        if (array_key_exists($field, $data)) {
                            $sliderConfig[$field] = $data[$field];
                            unset($data[$field]);
                        }
                    }
                    $sliderConfig          = $this->helperData->serialize($sliderConfig);
                    $data['slider_config'] = $sliderConfig;
                } else {
                    if ($model->getId()) {
                        $data['slider_config'] = $model->getSliderConfig();
                    } else {
                        $data['slider_config'] = '';
                    }
                }

                if ($data['location'] === 'product-tab') {
                    $data['display_mode'] = 1;
                }

                $model->loadPost($data);
                $this->_session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_session->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('mparp/rule/edit', ['id' => $model->getId(), 'type' => $model->getBlockType()]);

                    return;
                }
                $this->_redirect('mparp/rule/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int) $this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('mparp/rule/edit', ['id' => $model->getId(), 'type' => $model->getBlockType()]);
                } else {
                    $this->_redirect('mparp/rule/new');
                }

                return;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_redirect('mparp/rule/edit', [
                    'id'   => $this->getRequest()->getParam('rule_id'),
                    'type' => $this->getRequest()->getParam('type')
                ]);

                return;
            }
        }
        $this->_redirect('mparp/*/');
    }
}
