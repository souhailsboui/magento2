<?php
namespace Biztech\Ausposteparcel\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadLabels extends Action
{
    protected $resultPageFactory;
     /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

     /**
     * @var FileFactory
     */
    protected $fileFactory;

    public function __construct(
        Context $context,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $urlArray = $this->getRequest()->getParam('labels');
        $labelsContent = array();
        foreach ($urlArray as $i => $j) {
            $labelPath = $j;
            $labelContent =  file_get_contents($labelPath);
            if ($labelContent) {
                $labelsContent[] = $labelContent;
            }
        }
        if (!empty($labelsContent)) {
            $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
            return $this->fileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }
        return $this->_redirect('*/*/index');
    }
}
