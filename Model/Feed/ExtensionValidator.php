<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\Feed;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\ModuleListInterface;

class ExtensionValidator
{
    /**
     * @var ModuleListInterface
     */
    private $moduleListInterface;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrarInterface;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    public function __construct(
        ModuleListInterface $moduleListInterface,
        ComponentRegistrarInterface $componentRegistrarInterface,
        ReadFactory $readFactory
    ) {
        $this->moduleListInterface = $moduleListInterface;
        $this->componentRegistrarInterface = $componentRegistrarInterface;
        $this->readFactory = $readFactory;
    }

    /**
     * @param string $extensions
     * @param string $vendor
     * @return bool|string
     */
    public function validateExtNeedUpdate($extensions, $vendor = '')
    {
        $extensionsName = $this->validateExt($extensions, $vendor);
        if (!is_array($extensionsName)) {
            return false;
        }
        $extensionsName = array_filter($extensionsName, function ($value) {
            return 1 === $value;
        });
        $extensionsName = array_keys($extensionsName);
        if (empty($extensionsName)) {
            return false;
        }
        $extensions = $this->prepareExtValue($extensions);
        $_extensions = [];
        foreach ($extensionsName as $extensionName) {
            $_extensions[] = sprintf('%s (%s)', $extensionName, $extensions[$extensionName]);
        }
        return implode(', ', $_extensions);
    }

    /**
     * @param string $extensions
     * @param string $vendor
     * @param bool $diff
     * @return array|bool
     */
    public function validateExt($extensions, $vendor = '', $diff = false)
    {
        $extensions = $this->prepareExtValue($extensions);
        if (empty($extensions)) {
            return true;
        }

        $extensionsName = array_keys($extensions);
        $modules = $this->getInstalledExt($vendor);
        if ($diff) {
            $extensionsName = array_diff($extensionsName, $modules);
        } else {
            $extensionsName = array_intersect($extensionsName, $modules);
        }

        if (empty($extensionsName)) {
            return false;
        }

        $_extensions = [];
        foreach ($extensionsName as $extensionName) {
            $_extensions[$extensionName] = $this->validateVersion($extensionName, $extensions[$extensionName]);
        }

        return $_extensions;
    }

    /**
     * @param string $extension
     * @return array
     */
    public function prepareExtValue($extension)
    {
        $extensions = [];
        if (preg_match_all('/([a-z0-9_]+)(\s\(([^\(\)]+)\))*/i', $extension, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $matche) {
                $name = $matche[1];
                $version = isset($matche[3]) ? $matche[3] : null;
                $extensions[$name] = $version;
            }
        }

        return $extensions;
    }

    /**
     * @param string $vendor
     * @return array
     */
    public function getInstalledExt($vendor = '')
    {
        $modules = $this->moduleListInterface->getNames();

        if (!empty($vendor)) {
            $modules = array_filter($modules, function ($moduleName) use ($vendor) {
                return strpos($moduleName, $vendor . '_') === 0;
            });
        }

        $modules = array_filter($modules);
        sort($modules, SORT_STRING);

        return $modules;
    }

    /**
     * @param string $moduleName
     * @param string $version
     * @return int|null
     */
    public function validateVersion($moduleName, $version)
    {
        $cur_version = $this->getComposerVersion($moduleName);
        if (is_string($version) && is_string($cur_version)) {
            return version_compare($version, $cur_version);
        }

        return null;
    }

    /**
     * @param string $moduleName
     * @param string $type
     * @return string|null
     */
    public function getComposerVersion($moduleName, $type = ComponentRegistrar::MODULE)
    {
        $path = $this->componentRegistrarInterface->getPath($type, $moduleName);

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

    /**
     * @param string $extensions
     * @param string $vendor
     * @return array|bool
     */
    public function validateExtInstalled($extensions, $vendor = '')
    {
        return $this->validateExt($extensions, $vendor);
    }

    /**
     * @param string $extensions
     * @param string $vendor
     * @return array|bool
     */
    public function validateExtNotInstalled($extensions, $vendor = '')
    {
        return $this->validateExt($extensions, $vendor, true);
    }
}
