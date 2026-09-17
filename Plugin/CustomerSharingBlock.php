<?php

namespace WeltPixel\AdvancedWishlist\Plugin;

use Magento\Framework\Escaper;
use Magento\Wishlist\Block\Customer\Sharing as SharingBlock;
use WeltPixel\AdvancedWishlist\Helper\Data as WishlistHelper;


class CustomerSharingBlock
{

    /**
     * @var WishlistHelper
     */
    protected $_helper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * CustomerSharingBlock constructor.
     * @param WishlistHelper $helper
     * @param Escaper $escaper
     */
    public function __construct(
        WishlistHelper $helper,
        Escaper $escaper
    )
    {
        $this->_helper = $helper;
        $this->escaper = $escaper;
    }

    /**
     * @param SharingBlock $subject
     * @param string $result
     * @return array
     */
    public function afterGetBlockHtml(
        SharingBlock $subject, $result
    )
    {
        $isMultiWishlistEnabled = $this->_helper->isMultiWishlistEnabled();

        if ($isMultiWishlistEnabled) {
            /**
             * The id went into the markup exactly as it arrived in the url, which let a crafted
             * link break out of the value attribute and run script on the sharing page. It is only
             * ever a wishlist id, so it is cast to an integer and escaped; a value that is not a
             * usable id adds no field at all rather than an empty one.
             */
            $wishlistId = (int)$subject->getRequest()->getParam('wishlist_id', null);
            if ($wishlistId) {
                $result .= PHP_EOL . '<input type="hidden" name="wishlist_id" value="'
                    . $this->escaper->escapeHtmlAttr((string)$wishlistId) . '" />';
            }
        }

        return $result;
    }
}
