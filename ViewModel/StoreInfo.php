<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\ViewModel;

use Olegnax\Core\Model\ThemeInfo;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class StoreInfo implements ArgumentInterface
{
    protected $themeInfo;
    protected $productMetadata;

    public function __construct(
        ThemeInfo $themeInfo,
        ProductMetadataInterface $productMetadata
    ) {
        $this->themeInfo = $themeInfo;
        $this->productMetadata = $productMetadata;
    }
    
    public function getThemeCode()
    {
        return $this->themeInfo->getThemeCode();
    }

    /**
     * @return boolean
     */
    public function isMobileTheme()
    {
        return $this->themeInfo->isMobileTheme();
    }

    public function getVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
