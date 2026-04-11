<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model;

use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ThemeInfo implements ArgumentInterface
{
    protected $design;
    
    public function __construct(
        DesignInterface $design
    ) {
        $this->design = $design;
    }
    
    public function getThemeCode()
    {
        return $this->design->getDesignTheme()->getCode();
    }

    /**
     * @return boolean
     */
    public function isMobileTheme()
    {
        return (bool) preg_match('@^Olegnax\/a2m@', $this->getThemeCode());
    }
}
