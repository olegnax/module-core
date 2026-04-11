<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\ViewModel;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

class UrlProvider implements ArgumentInterface
{
    private $storeManager;
    private $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function getBaseUrl($type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        return $this->storeManager->getStore()->getBaseUrl($type, $secure);
    }

    public function isHomePage(): bool
    {
        return $this->request->getFullActionName() === 'cms_index_index';
    }
}
