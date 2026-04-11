<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\ViewModel;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CmsProcessor implements ArgumentInterface
{
    private $filterProvider;

    public function __construct(FilterProvider $filterProvider)
    {
        $this->filterProvider = $filterProvider;
    }

    public function getBlockTemplateProcessor($content = '')
    {
        if (empty($content) || !is_string($content)) {
            $content = '';
        }
        $blockFilter = $this->filterProvider->getBlockFilter();
        return $blockFilter->filter(trim($content));
    }
}
