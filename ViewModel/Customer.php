<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Core\ViewModel;

use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Catalog\Helper\Product\Compare as CompareHelper;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Customer implements ArgumentInterface
{
    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;
    /**
     * @var CompareHelper
     */
    protected $compareHelper;
    /**
     * @var CustomerContext
     */
    protected $httpContext;

    public function __construct(
        WishlistHelper $wishlistHelper,
        CompareHelper $compareHelper,
        HttpContext $httpContext
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->compareHelper = $compareHelper;
        $this->httpContext = $httpContext;
    }

    /**
     * Retrieve wishlist item count (include config settings)
     *
     * Used in top link menu only
     *
     * @return int
     */
    public function getWishlistCount()
    {
        return $this->wishlistHelper->getItemCount();
    }

    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getCompareListUrl()
    {
        return $this->compareHelper->getListUrl();
    }

    /**
     * Retrieve count of items in compare list
     *
     * @return int
     */
    public function getCompareListCount()
    {
        return $this->compareHelper->getItemCount();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
