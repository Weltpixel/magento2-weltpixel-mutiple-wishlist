<?php

namespace WeltPixel\AdvancedWishlist\Plugin;

use WeltPixel\AdvancedWishlist\Helper\Data as WishlistHelper;

class WishlistItemModel
{

    /**
     * @var WishlistHelper
     */
    protected $_helper;

    /**
     * WishlistItemModel constructor.
     * @param WishlistHelper $_helper
     */
    public function __construct(
        WishlistHelper $_helper
    )
    {
        $this->_helper = $_helper;
    }

    /**
     * @param \Magento\Wishlist\Model\Item $subject
     * @param \Magento\Checkout\Model\Cart $cart
     * @param $delete
     * @return array
     */
    public function beforeAddToCart(
        \Magento\Wishlist\Model\Item $subject,
        \Magento\Checkout\Model\Cart $cart,
        $delete = false
    )
    {
        if ($this->_helper->keepItemInWishlistAfterAddToCart()) {
            $delete = false;
        }
        return [$cart, $delete];
    }
}
