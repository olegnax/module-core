<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RemoveRequireJsConfigs implements ObserverInterface
{
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig) 
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $itemsToRemove = $transport->getData('configs_to_remove') ?: [];

         /* Remove Owl Carousel from requirejs config if it is disabled */
        $isOwlEnabled = $this->scopeConfig->isSetFlag('olegnax_core_settings/general/owlcarousel', ScopeInterface::SCOPE_STORE);

        if (!$isOwlEnabled) {
            $itemsToRemove['owl.carousel'] = 'Olegnax_Core/owl.carousel/owl.carousel.min';
            $itemsToRemove['OXowlCarousel'] = 'Olegnax_Core/owl.carousel';
        }

        $transport->setData('configs_to_remove', $itemsToRemove);
    }
}