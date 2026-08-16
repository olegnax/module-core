<?php
/**
 * @deprecated version 1.8.2.0
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Class SimpleTemplate
 *
 * @api
 */
class SimpleTemplate extends \Magento\Framework\View\Element\Template
{
	/**
	 * @deprecated version 1.4.0.0
	 *
	 * @return boolean
	 */
	public function isLoggedIn()
    {
        return (bool)$this->_loadObject(HttpContext::class)->getValue(CustomerContext::CONTEXT_AUTH);
    }
	/**
	 * @deprecated version 1.4.0.0
	 */
    public function getSession()
    {
        return ObjectManager::getInstance()->create(Session::class);
    }
	/**
	 * @deprecated version 1.4.0.0
     * use ConfigProvider view model instead
	 */
    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }
	/**
	 * @deprecated version 1.4.0.0
     * use ConfigProvider view model instead
	 */
    public function getSystemValue($path, $storeCode = null)
    {
        $value = $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
        if (is_null($value)) {
            $value = '';
        }
        return $value;
    }
	/**
	 * @deprecated version 1.4.0.0
	 */
    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }
	/**
	 * @deprecated version 1.4.0.0
	 */
    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }
}
