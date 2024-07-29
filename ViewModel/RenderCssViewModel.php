<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2024 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\ViewModel;

use Olegnax\Core\Model\DynamicStyle\EscapeCss;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 *  RenderCssViewModel
 */
class RenderCssViewModel implements ArgumentInterface
{
    /**
     * @var EscapeCss
     */
    protected $escapeCss;

    /**
     * @param EscapeCss $escapeCss
     */
    public function __construct(EscapeCss $escapeCss)
    {
        $this->escapeCss = $escapeCss;
    }

    public function escapeCss($css){
        return $this->escapeCss->escapeCss($css);
    }
}
