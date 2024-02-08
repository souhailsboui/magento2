<?php

namespace MageMe\WebForms\Helper;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;

class LogicHelper
{
    const HIDDEN_CSS_CLASS = 'mm-logic-hidden';

    /**
     * @var LogicRepositoryInterface
     */
    private $logicRepository;

    public function __construct(LogicRepositoryInterface $logicRepository)
    {
        $this->logicRepository = $logicRepository;
    }

    public function validateLogicCollision(FormInterface $form) {
        $logicRules = $this->logicRepository->getListByFormId($form->getId(), false);

    }

}