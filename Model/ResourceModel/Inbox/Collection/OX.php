<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

use Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

class OX extends Collection
{
    protected function _initSelect()
    {
        return parent::_initSelect()
            ->addFieldToFilter('isOX', 1);
    }

}
