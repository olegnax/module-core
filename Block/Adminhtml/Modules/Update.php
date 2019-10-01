<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml\Modules;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Http_Client;

/** @noinspection ClassNameCollisionInspection */

class Update extends Template
{

    const MODULE_URL = 'http://olegnax.com/extras/products-status/magento2_modules.json';
    const CACHE_KEY_PREFIX = 'OLEGNAX_';
    /**
     * Cache tag
     */
    const CACHE_TAG = 'extensions';
    /**
     *
     * @var ObjectManager
     */
    public $objectManager;
    protected $_moduleUrl;
    protected $deploymentConfig;
    protected $moduleList;
    protected $componentRegistrar;
    protected $readFactory;
    protected $latestVersions;
    protected $storeManager;
    protected $curlFactory;
    protected $productMetadata;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        ModuleListInterface $moduleList,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Context $context,
        StoreManagerInterface $storeManager,
        CurlFactory $curlFactory,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->moduleList = $moduleList;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->storeManager = $storeManager;
        $this->curlFactory = $curlFactory;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
        $this->getModulesLatestVersions();
        $this->addData(['cache_lifetime' => 86400, 'cache_tags' => [self::CACHE_TAG]]);
    }

    /**
     * @return array
     */
    protected function getModulesLatestVersions()
    {
        $result = $this->_loadCache();
        if (!$result) {
            $result = $this->getModuleData();
            if (!empty($result)) {
                $this->_saveCache($result);
            }
        }
        try {
            $result = (array)json_decode($result, true);

            $this->latestVersions = $result;
        } catch (Exception $e) {
            $this->latestVersions = [];
        }

        return $this->latestVersions;
    }

    public function getModuleData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout' => 2,
                'maxredirects' => 5,
                'useragent' => 'Olegnax: ' . $this->productMetadata->getName() . '/' . $this->productMetadata->getVersion() . ' (' . $this->productMetadata->getEdition() . ')',
                'referer' => $this->getCurrentUrl(),
                'verifypeer' => false,
            ]
        );
        $curl->write(Zend_Http_Client::GET, $this->getModuleUrl(), '1.0');
        $data = $curl->read();
        $curl->close();

        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);

        return $data;
    }

    public function getCurrentUrl()
    {
        return $this->_loadObject(ScopeConfigInterface::class)->getValue('web/unsecure/base_url');
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    public function getModuleUrl()
    {
        if ($this->_moduleUrl === null) {
            $this->_moduleUrl = self::MODULE_URL . $this->getHash();
        }
        return $this->_moduleUrl;
    }

    public function getHash()
    {
        $args = ['hash' => base64_encode($this->getCurrentIp())];
        $args = array_filter($args);
        $query = '';
        if (!empty($args)) {
            $query = '?' . http_build_query($args);
        }

        return $query;
    }

    public function getCurrentIp()
    {
        $variable = [
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR'
        ];
        $value = array_map([$this, 'getServer'], $variable);
        $value = array_map('trim', $value);
        $value[] = $this->getRemoteAddress();
        $value = array_filter($value);
        $value = implode(',', $value);

        return $value;
    }

    public function getRemoteAddress()
    {
        return $this->_loadObject(RemoteAddress::class)->getRemoteAddress();
    }

    /**
     * @return array
     */
    public function getModuleVersions()
    {
        $modules = $this->deploymentConfig->get('modules');
        $modules = array_filter($modules, [$this, 'filter_module'], ARRAY_FILTER_USE_KEY);
        foreach ($modules as $moduleName => $enabled) {
            $module = array_merge($this->getModule($moduleName), ['enabled' => $enabled]);
            if (array_key_exists($moduleName, $this->latestVersions)) {
                $module = array_merge($module, $this->latestVersions[$moduleName], [
                    'server_version' => $this->latestVersions[$moduleName]['version'],
                    'theme_module' => $this->latestVersions[$moduleName]['theme_module'],
                    'update_status' => version_compare($this->latestVersions[$moduleName]['version'],
                        $module['setup_version'], '>'),
                ]);
            } else {
                $module = array_merge($module, [
                    'server_version' => $module['setup_version'],
                    'theme_module' => false,
                    'update_status' => false,
                ]);
            }
            $modules[$moduleName] = $module;
        }

        return $modules;
    }

    protected function getModule($name)
    {
        $module = $this->moduleList->getOne($name);
        if (empty($module)) {
            $module = [
                'name' => $name,
                'setup_version' => $this->getComposerVersion($name),
            ];
        }
        return (array)$module;
    }

    /**
     * @param string $moduleName
     * @param string $type
     * @return string
     */
    protected function getComposerVersion($moduleName, $type = ComponentRegistrar::MODULE)
    {
        $path = $this->componentRegistrar->getPath($type, $moduleName);

        if ($path) {
            $dirReader = $this->readFactory->create($path);

            if ($dirReader->isExist('composer.json')) {
                $data = $dirReader->readFile('composer.json');
                $data = json_decode($data, true);
                if (isset($data['version'])) {
                    return $data['version'];
                }
            }
            if ($dirReader->isExist('etc/module.xml')) {
                $data = $dirReader->readFile('etc/module.xml');
                if (preg_match('/setup_version="([^\"]+)"/i', $data, $matches)) {
                    return $matches[1];
                }
            }
        }

        return '';
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
        foreach ($themes as $themeName => $path) {
            $theme = $this->getTheme($path);
            if ('' === $theme['setup_version']) {
                unset($themes[$themeName]);
                continue;
            }
            if (!array_key_exists('name', $theme)) {
                $theme['name'] = $themeName;
            }
            if (array_key_exists($themeName, $this->latestVersions)) {
                $theme = array_merge($theme, $this->latestVersions[$themeName], [
                    'server_version' => $this->latestVersions[$themeName]['version'],
                    'update_status' => version_compare($this->latestVersions[$themeName]['version'],
                        $theme['setup_version'], '>'),
                ]);
            } else {
                $theme = array_merge($theme, [
                    'server_version' => $theme['setup_version'],
                    'update_status' => false,
                ]);
            }
            $themes[$themeName] = $theme;
        }

        return $themes;
    }

    protected function getTheme($path)
    {
        $theme = [
            'path' => $path,
            'setup_version' => $this->getComposerVersion($path, ComponentRegistrar::THEME),
        ];
        $name = $this->getThemeName($path);
        if (!empty($name)) {
            $theme['name'] = $name;
        }

        return (array)$theme;
    }

    protected function getThemeName($path)
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $path);
        if ($path) {
            $dirReader = $this->readFactory->create($path);
            if ($dirReader->isExist('theme.xml')) {
                $data = $dirReader->readFile('theme.xml');
                if (preg_match('/<title>([^<>]+)<\/title>/i', $data, $matches)) {
                    return $matches[1];
                }
            }
        }

        return null;
    }

    public function getServer($name)
    {
        return $this->_loadObject(RequestInterface::class)->getServer($name);
    }

}
