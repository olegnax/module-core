<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

class Exists extends Collection
{

    /**
     * @param string $url
     * @return bool
     */
    public function execute(string $url)
    {
        $this->addFieldToFilter('url', $url);

        return $this->getSize() > 0;
    }

}
