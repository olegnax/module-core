<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class SimpleTemplate extends \Magento\Framework\View\Element\Template
{

    public function isLoggedIn()
    {
        return $this->getSession()->isLoggedIn();
    }

    public function getSession()
    {
        return ObjectManager::getInstance()->create(Session::class);
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        $value = $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
        if (is_null($value)) {
            $value = '';
        }
        return $value;
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }
}
