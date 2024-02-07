<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Controller\Adminhtml\Export;


use  Exception;
use MageMe\WebForms\Helper\Result\Export\GridToCsvExport as Export;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class ResultGridToCsv extends Action
{
    /**
     * @var Export
     */
    protected $export;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * ResultGridToCsv constructor.
     * @param FileFactory $fileFactory
     * @param Export $export
     * @param Context $context
     */
    public function __construct(
        FileFactory $fileFactory,
        Export      $export,
        Context     $context
    )
    {
        parent::__construct($context);
        $this->export      = $export;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to CSV
     *
     * @return ResponseInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $fileName = 'export.csv';
        return $this->fileFactory->create($fileName, $this->export->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
