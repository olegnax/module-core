<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

class OxUpdate extends OX
{

    const TYPE_UPDATE = 'update';

    protected function _initSelect()
    {
        return parent::_initSelect()
            ->addFieldToFilter('ox_type', self::TYPE_UPDATE)
            ->setOrder('date_added');
    }

}
