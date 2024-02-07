<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace MageMe\WebForms\Ui\Component\Result\Listing\Modifier;

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Helper\Statistics\ResultStat;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Filters implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;
    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param RequestInterface $request
     */
    public function __construct(
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        RequestInterface            $request
    ) {
        $this->request            = $request;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function modifyMeta(array $meta): array
    {
        $namespace = 'result_grid' . $this->request->getParam(ResultInterface::FORM_ID);
        if ($this->request->getParam(ResultInterface::IS_REPLIED) !== null) {
            $applied = [
                ResultInterface::IS_REPLIED => [$this->request->getParam(ResultInterface::IS_REPLIED)]
            ];
        } elseif ($this->request->getParam(ResultInterface::IS_READ) !== null) {
            $applied = [
                ResultInterface::IS_READ => [$this->request->getParam(ResultInterface::IS_READ)]
            ];
        } elseif ($this->request->getParam(ResultStat::IS_UNREAD_REPLY) !== null) {
            $applied = [
                ResultStat::IS_UNREAD_REPLY => [$this->request->getParam(ResultStat::IS_UNREAD_REPLY)]
            ];
        } elseif ($this->request->getParam(ResultInterface::APPROVED) !== null) {
            $applied = [
                ResultInterface::APPROVED => [(int)$this->request->getParam(ResultInterface::APPROVED)]
            ];
        } elseif ($this->request->getParam('all') !== null) {
            $applied = [
                'placeholder' => true
            ];
        } else {
            return $meta;
        }
        $currentBookmark = $this->bookmarkManagement->getByIdentifierNamespace('current', $namespace);
        if ($currentBookmark) {
            $config                                  = $currentBookmark->getConfig();
            $config['current']['filters']['applied'] = $applied;
            $currentBookmark->setConfig(json_encode($config));
            $this->bookmarkRepository->save($currentBookmark);
        }
        $meta['listing_top']['children']['listing_filters'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'applied' => $applied
                    ]
                ]
            ]
        ];
        return $meta;
    }

}