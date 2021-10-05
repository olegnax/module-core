<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml;

use Exception;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxUpdateFactory;

class NoticeUpdate extends Notice
{
    public function __construct(
        Context $context,
        OxUpdateFactory $collector,
        array $data = []
    ) {
        $this->collector = $collector;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function getColection()
    {
        $contents = [];
        $colection = $this->loadColection();
        if ($colection && $colection->getSize()) {
            foreach ($colection as $notice) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $validate = [];
                try {
                    $_validate = $notice->getOxValidate();
                    $_validate = json_decode($_validate, true);
                    $validate = $_validate;
                } catch (Exception $exception) {
                    $validate = [];
                }

                if (!$this->checkExtNeedUpdate($validate)) {
                    continue;
                }

                $content = $notice->getOxContent();
                if (!empty($content)) {
                    $contents [] = $content;
                }
            }
        }

        return $contents;
    }

    protected function checkExtNeedUpdate($validate)
    {
        if (!empty($validate)) {
            if (is_array($validate) && array_key_exists('extension_update', $validate)) {
                return $this->validateExtNeedUpdate($validate['extension_update'], 'Olegnax');
            }
        }

        return false;
    }

    protected function validateExtNeedUpdate($extensions, $vendor = '')
    {
        $extensionsName = $this->validateExt($extensions, $vendor);
        if (!is_array($extensionsName)) {
            return false;
        }
        $extensionsName = array_filter($extensionsName, function ($value) {
            return 1 === $value;
        });
        $extensionsName = array_keys($extensionsName);

        return $extensionsName;
    }

    protected function validateExt($extensions, $vendor = '', $diff = false)
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

    protected function prepareExtValue($extension)
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

    protected function getInstalledExt($vendor = '')
    {
        $modules = $this->_loadObject(ModuleListInterface::class)->getNames();

        $dispatchResult = new DataObject($modules);
        $modules = $dispatchResult->toArray();

        if (!empty($filter)) {
            $_modules = [];
            foreach ($modules as $module) {
                $_module = explode('_', $module);
                $_vendor = array_shift($_module);
                if ($vendor == $_vendor) {
                    $_modules[] = $module;
                }
            }
            $modules = $_modules;
        }
        $modules = array_filter($modules);
        sort($modules, SORT_STRING);

        return $modules;
    }

    protected function validateVersion($moduleName, $version)
    {
        $cur_version = $this->getComposerVersion($moduleName);
        if (is_string($version) && is_string($cur_version)) {
            return version_compare($version, $cur_version);
        }
        return 0;
    }

    protected function getComposerVersion($moduleName, $type = ComponentRegistrar::MODULE)
    {
        $path = $this->_loadObject(ComponentRegistrarInterface::class)->getPath($type, $moduleName);

        if ($path) {
            $dirReader = $this->_loadObject(ReadFactory::class)->create($path);

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
     * @param array $contents
     * @return string
     */
    protected function itemSelection($contents = [])
    {
        return implode('', $contents);
    }

    protected function validateExtInstalled($extensions, $vendor = '')
    {
        return $this->validateExt($extensions, $vendor);
    }

}
