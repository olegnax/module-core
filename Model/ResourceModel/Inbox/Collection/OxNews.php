<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

use Magento\Framework\Notification\MessageInterface;

class OxNews extends OX
{

    protected function _initSelect()
    {
        return parent::_initSelect()
            ->addFilter('is_remove', 0)
            ->addFilter('is_read', 0)
            ->addFieldToFilter('severity', ['gteq' => MessageInterface::SEVERITY_CRITICAL])
            ->setOrder('date_added')
            ->distinct(true);
    }

}
