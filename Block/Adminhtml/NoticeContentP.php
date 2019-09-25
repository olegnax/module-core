<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml;

use Magento\Framework\View\Element\Template\Context;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxContentPFactory;

class NoticeContentP extends Notice
{

    public function __construct(
        Context $context,
        OxContentPFactory $collector,
        array $data = []
    ) {
        $this->collector = $collector;
        parent::__construct($context, $data);
    }

    /**
     * @return OxContentPFactory
     */
    protected function loadColection()
    {
        if ($this->collector) {
            $location = $this->getLocation();
            if (empty($location)) {
                $location = 'global';
            }

            return $this->collector->create()->addFieldToFilter('ox_type', $location);
        }

        return null;
    }

}
