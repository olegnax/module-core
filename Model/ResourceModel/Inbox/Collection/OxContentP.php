<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

class OxContentP extends OX
{
    const TYPE_CONTENT = 6;

    protected function _initSelect()
    {
        return parent::_initSelect()
            ->addFilter('is_remove', 1)
            ->addFieldToFilter('severity', self::TYPE_CONTENT);
    }

}
