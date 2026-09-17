<?php
namespace WeltPixel\AdvancedWishlist\Controller\Multiwishlist;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;
use Magento\Wishlist\Model\WishlistFactory;
use WeltPixel\AdvancedWishlist\Model\MultipleWishlistProvider;
use Psr\Log\LoggerInterface;

class Move extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MultipleWishlistProvider
     */
    protected $multipleWishlistProvider;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * Update constructor.
     * @param WishlistItemFactory $wishlistItemFactory
     * @param CustomerSession $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param LoggerInterface $logger
     * @param WishlistFactory $wishlistFactory
     * @param MultipleWishlistProvider $multipleWishlistProvider
     * @param Context $context
     */
    public function __construct(
        WishlistItemFactory $wishlistItemFactory,
        CustomerSession $customerSession,
        FormKeyValidator $formKeyValidator,
        LoggerInterface $logger,
        WishlistFactory $wishlistFactory,
        MultipleWishlistProvider $multipleWishlistProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->wishlistFactory = $wishlistFactory;
        $this->multipleWishlistProvider = $multipleWishlistProvider;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return;
        }

        $result = [
            'result' => false
        ];
        $customerId = $this->customerSession->getCustomerId();
        $wishlistId = $this->getRequest()->getParam('wishlist_id', null);
        $wishlistItemId = $this->getRequest()->getParam('item_id', null);

        if (!$customerId || !$wishlistId || !$wishlistItemId || !$this->formKeyValidator->validate($this->getRequest())) {
            return $this->prepareResult($result);
        }

        /**
         * Both ends of the move are checked against the session customer: the item has to sit in a
         * wishlist this customer owns, and the destination has to be theirs too. Previously the
         * item was loaded by id alone and the destination was taken straight from the request, so
         * an item could be pulled out of another customer's wishlist, or pushed into one.
         */
        $wishlistItemModel = $this->multipleWishlistProvider
            ->getCustomerWishlistItem($wishlistItemId, $customerId);
        $wishlistModel = $this->multipleWishlistProvider
            ->getCustomerWishlist($wishlistId, $customerId);

        if (!$wishlistItemModel || !$wishlistModel) {
            return $this->prepareResult($result);
        }

        try {
            /** Read before the move, not off an empty model: this was always null before */
            $oldWishlistId = $wishlistItemModel->getWishlistId();
            $wishlistItemModel->setWishlistId($wishlistId);
            $wishlistItemModel->setAddedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
            $wishlistItemModel->save();

            $oldWishlistModel = $this->wishlistFactory->create()->load($oldWishlistId);

            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $wishlistModel]);
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $oldWishlistModel]);

            $result['result'] = true;
            $this->messageManager->addSuccess(__('Your item has been moved successfully.'));
        } catch (\Exception $e) {
            $this->logger->critical(__('There was an issue with the wishlist item move.') . $e->getMessage());
            $this->messageManager->addError(__('There was an issue with the wishlist item move.'));
            return $this->prepareResult($result);
        }

        return $this->prepareResult($result);
    }

    /**
     * @param array $result
     * @return string
     */
    protected function prepareResult($result)
    {
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
