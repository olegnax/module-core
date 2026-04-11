<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Core\Plugin;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\RequireJs\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RequireJsConfigPlugin
{
    protected $eventManager;
    protected $dataObjectFactory;
    protected $scopeConfig;

    public function __construct(
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Remove items from requirejs config. 
     * Require static content redeploy.
     * 
     * @param Config $subject
     * @param string $result The concatenated requirejs-config.js string
     * @return string
     */
    public function afterGetConfig(Config $subject, string $result)
    {
        // Expecting an array of key => value, e.g.: 'owl.carousel' => 'Olegnax_Core/owl.carousel/owl.carousel.min'
        $transport = $this->dataObjectFactory->create(['data' => ['configs_to_remove' => []]]);

        $this->eventManager->dispatch(
            'olegnax_core_requirejs_config', 
            ['transport' => $transport]
        );

        if(!$this->isModificationAllowed()){
            return $result;
        }

        $itemsToRemove = $transport->getData('configs_to_remove');

        if (!empty($itemsToRemove) && is_array($itemsToRemove)) {
            foreach ($itemsToRemove as $jsKey => $jsValue) {
                
                $safeKey = preg_quote($jsKey, '/');
                
                // If the value is a boolean (like a mixin), match without quotes
                if (strtolower((string)$jsValue) === 'true' || strtolower((string)$jsValue) === 'false') {
                    $safeValue = preg_quote(strtolower((string)$jsValue), '/');
                    $pattern = '/[\'"]' . $safeKey . '[\'"]\s*:\s*' . $safeValue . '\s*,?/';
                } 
                // Otherwise, it's a standard path, match with quotes
                else {
                    $safeValue = preg_quote((string)$jsValue, '/');
                    $pattern = '/[\'"]' . $safeKey . '[\'"]\s*:\s*[\'"]' . $safeValue . '[\'"]\s*,?/';
                }
                
                $result = preg_replace($pattern, '', $result);
            }
        }

        return $result;
    }

    protected function isModificationAllowed(){
        return $this->scopeConfig->isSetFlag('olegnax_core_settings/general/requirejs_modify', ScopeInterface::SCOPE_STORE);
    }
}
