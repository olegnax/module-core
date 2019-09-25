<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml;

use Magento\Framework\View\Element\Template\Context;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxNewsFactory;

class NoticeNews extends Notice
{
    public function __construct(
        Context $context,
        OxNewsFactory $collector,
        array $data = []
    ) {
        $this->collector = $collector;
        parent::__construct($context, $data);
    }

    /**
     * @param array $contents
     * @return string
     */
    protected function itemSelection($contents = [])
    {
        return implode('', $contents);
    }
}
