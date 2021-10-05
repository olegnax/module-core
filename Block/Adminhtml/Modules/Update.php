<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml\Modules;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\DeploymentConfig;
use Olegnax\Core\Helper\ModuleInfo;

/** @noinspection ClassNameCollisionInspection */

class Update extends Template
{
    const CACHE_KEY_PREFIX = 'OLEGNAX_';

    const CACHE_TAG = 'extensions';
    /**
     * @var ModuleInfo
     */
    protected $helper;
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    public function __construct(
        Context $context,
        DeploymentConfig $deploymentConfig,
        ModuleInfo $helper,
        array $data = []
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->helper = $helper;
        parent::__construct($context, $data);
        $this->addData(['cache_lifetime' => 86400, 'cache_tags' => [self::CACHE_TAG]]);
    }

    /**
     * Forsed update Data
     */
    public function ForsedUpdate()
    {
        $this->helper->getData(true);
    }

    /**
     * @return array
     */
    public function getModuleVersions()
    {
        $modules = $this->deploymentConfig->get('modules');
        $modules = array_filter($modules, [$this, 'filter_module'], ARRAY_FILTER_USE_KEY);
        $_modules = [];
        foreach ($modules as $moduleName => $enabled) {
            $module = $this->helper->getModuleInfo($moduleName);
            if (empty($module)) {
                continue;
            }
            $module['enabled'] = $enabled;
            $_modules[$moduleName] = $module;
        }

        return $_modules;
    }

    public function filter_module($moduleName)
    {
        return preg_match('/^olegnax_/i', $moduleName);
    }

    /**
     * @return array
     */
    public function getThemeVersions()
    {
        $themes = [
            'Athlete2' => 'frontend/Olegnax/athlete2',
        ];
        $_themes = [];
        foreach ($themes as $themeName => $path) {
            $theme = $this->helper->getThemeInfo($path, $themeName, false);
	        if (empty($theme)) {
		        continue;
	        }
            $_themes[$themeName] = $theme;
        }

        return $_themes;
    }

}
