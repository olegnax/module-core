<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfigProvider implements ArgumentInterface
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $value = $this->scopeConfig->getValue($path, $scopeType, $storeCode);
        if (is_null($value)) {
            $value = '';
        }
        return $value;
    }

    public function getModuleConfig($path = '', $storeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if (empty($path)) {
            $path = 'olegnax_core_settings';
        } else {
            $path = 'olegnax_core_settings' . '/' . $path;
        }
        return $this->getSystemValue($path, $storeCode, $scopeType);
    }

    public function getSystemDefaultValue($path)
    {
        return $this->getSystemValue($path, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
