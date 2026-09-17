<?php
namespace WeltPixel\AdvancedWishlist\Controller\Multiwishlist;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Wishlist\Model\WishlistFactory;
use WeltPixel\AdvancedWishlist\Model\MultipleWishlistProvider;

class Delete extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var MultipleWishlistProvider
     */
    protected $multipleWishlistProvider;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * Delete constructor.
     * @param WishlistFactory $wishlistFactory
     * @param CustomerSession $customerSession
     * @param MultipleWishlistProvider $multipleWishlistProvider
     * @param FormKeyValidator $formKeyValidator
     * @param Context $context
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        CustomerSession $customerSession,
        MultipleWishlistProvider $multipleWishlistProvider,
        FormKeyValidator $formKeyValidator,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->multipleWishlistProvider = $multipleWishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_redirect('/');
            return;
        }

        $result = [
            'result' => false
        ];
        $customerId = $this->customerSession->getCustomerId();
        $wishlistId = $this->getRequest()->getParam('wishlistId', null);

        if (!$customerId || !$wishlistId || !$this->formKeyValidator->validate($this->getRequest())) {
            return $this->prepareResult($result);
        }

        /**
         * The wishlist is loaded filtered by its owner as well as its id, so an id belonging to
         * another customer does not match and nothing is deleted. Previously the row was loaded by
         * id alone, and the session customer id was read but never used.
         */
        $wishlistModel = $this->multipleWishlistProvider->getCustomerWishlist($wishlistId, $customerId);

        if (!$wishlistModel) {
            return $this->prepareResult($result);
        }

        try {
            $wishlistModel->delete();
            $result['result'] = true;
        } catch (\Exception $e) {
            $result['msg'] = $e->getMessage();
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
