<?php

namespace WeltPixel\AdvancedWishlist\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Wishlist\Model\WishlistFactory;
use WeltPixel\AdvancedWishlist\Model\MultipleWishlistProvider;

class MultipleWishlistTitle extends \Magento\Framework\View\Element\Template
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
     * @var bool
     */
    protected $shouldDisplay = false;

    /**
     * @param WishlistFactory $wishlistFactory
     * @param CustomerSession $customerSession
     * @param MultipleWishlistProvider $multipleWishlistProvider
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(WishlistFactory $wishlistFactory,
                                CustomerSession $customerSession,
                                MultipleWishlistProvider $multipleWishlistProvider,
                                \Magento\Framework\View\Element\Template\Context $context,
                                array $data = [])
    {
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->multipleWishlistProvider = $multipleWishlistProvider;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $wishlistId = $this->getRequest()->getParam('wishlist_id');
        if (!$wishlistId) {
            return;
        }

        /**
         * The wishlist is loaded filtered by its owner as well as its id, so an id belonging to
         * another customer does not match. Previously any id was loaded by id alone and its name
         * went straight into the page title, which leaked the names of other people's wishlists.
         */
        $wishlist = $this->multipleWishlistProvider->getCustomerWishlist(
            (int)$wishlistId,
            (int)$this->customerSession->getCustomerId()
        );

        if (!$wishlist) {
            return;
        }

        try {
            $pageTitle = $wishlist->getWishlistName();
            $this->pageConfig->getTitle()->set($pageTitle);
            $this->shouldDisplay = true;
        } catch (\Exception $ex) {}
    }

    /**
     * @return bool
     */
    public function displayBackLink() {
        return $this->shouldDisplay;
    }
}
