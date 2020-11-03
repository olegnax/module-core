<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\ResourceModel\Inbox\Collection;

class Expired extends OX
{

    protected function _initSelect()
    {
        return parent::_initSelect()
            ->addFieldToFilter('is_remove', 0)

            ->addFieldToFilter('date_expire', ['lt' => date('Y-m-d H:i:s')]);
    }

}
