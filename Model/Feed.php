<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2025 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model;

use Exception;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\ExistsFactory;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\ExpiredFactory;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxContent;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxContentP;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OXFactory;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxUpdate;
use Olegnax\Core\Model\Feed\FeedStatusManager;
use Olegnax\Core\Model\Feed\FeedConfig;
use SimpleXMLElement;

class Feed extends AbstractModel
{

    /**
     * @var FeedStatusManager
     */
    private $feedStatusManager;
    /**
     * @var FeedConfig
     */
    private $feedConfig;

    protected $expiredFactory;
    protected $productMetadata;
    protected $storeManagerInterface;
    protected $moduleListInterface;
    protected $componentRegistrarInterface;
    protected $readFactory;
    protected $oxFactory;
    protected $existsFactory;
    protected $inboxFactory;
    protected $curlFactory;

    public function __construct(
        ExpiredFactory $expiredFactory,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManagerInterface,
        ModuleListInterface $moduleListInterface,
        ComponentRegistrarInterface $componentRegistrarInterface,
        ReadFactory $readFactory,
        OXFactory $oxFactory,
        ExistsFactory $existsFactory,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        FeedStatusManager $feedStatusManager,
        FeedConfig $feedConfig
    ) {
        $this->expiredFactory = $expiredFactory;
        $this->productMetadata = $productMetadata;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->moduleListInterface = $moduleListInterface;
        $this->componentRegistrarInterface = $componentRegistrarInterface;
        $this->readFactory = $readFactory;
        $this->oxFactory = $oxFactory;
        $this->existsFactory = $existsFactory;
        $this->inboxFactory = $inboxFactory;
        $this->curlFactory = $curlFactory;
        $this->feedStatusManager = $feedStatusManager;
        $this->feedConfig = $feedConfig;
    }

    /**
     * @return $this
     */
    public function checkUpdate()
    {

        $feedData = [];

        $feedXml = $this->getFeedXml();
        $installDate = $this->feedStatusManager->getInstallDate();
        $types = [];

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $severity = isset($item->severity) ? (int)$item->severity : MessageInterface::SEVERITY_NOTICE;
                $type = isset($item->type) ? $this->escapeString($item->type) : null;
                $extensionUpdate = $this->validateExtNeedUpdate($item->extensionUpdate, 'Olegnax');
                $validate = [];
                if ($extensionUpdate) {
                    $type = OxUpdate::TYPE_UPDATE;
                    $validate = [
                        'extension_update' => $extensionUpdate,
                    ];
                }
                $pubDate = strtotime((string)$item->pubDate);
                $expirationDate = (string)$item->expirationDate ? strtotime((string)$item->expirationDate) : null;

                if ($installDate > $pubDate) {
                    continue;
                }
                if ($expirationDate && $expirationDate < gmdate('U')) {
                    continue;
                }
                if ($item->extension_update && !$extensionUpdate) {
                    continue;
                }
                if (!$type && MessageInterface::SEVERITY_NOTICE > $severity) {
                    continue;
                }
                if (!$this->validateExtInstalled($item->extension, 'Olegnax')) {
                    continue;
                }
                if (!$this->validateExtNotInstalled($item->extensionNot, 'Olegnax')) {
                    continue;
                }
                if (!$this->validateExtInstalled($item->extension3D)) {
                    continue;
                }

                if ($type) {
                    if (OxContent::TYPE_CONTENT > $severity) {
                        $severity = OxContent::TYPE_CONTENT;
                    }
                    $types[] = $type;
                } else {
                    if (OxContent::TYPE_CONTENT == $severity || OxContentP::TYPE_CONTENT == $severity) {
                        $type = 'global';

                        $types[] = $type;
                    }
                }
                $_feedData = [
                    'severity' => $severity,
                    'date_added' => date('Y-m-d H:i:s', $pubDate),
                    'date_expire' => $expirationDate,
                    'title' => $this->escapeString($item->title),
                    'description' => $this->escapeString($item->description),
                    'url' => $this->escapeString($item->link),
                    'ox_content' => (string)$item->content,
                    'ox_image' => (string)$item->image,
                    'ox_type' => $type,
                    'ox_validate' => json_encode($validate),
                    'isOX' => 1,
                ];

                if ($type && !in_array($type, ['update'])) {
                    $_feedData['is_read'] = 1;
                    $_feedData['is_remove'] = 1;
                }

                $feedData[] = $_feedData;
            }

            if ($feedData) {
                if (!empty($types)) {
                    $this->removeOXType($types);
                }
                $this->inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->feedStatusManager->setLastUpdate();

        return $this;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            $xml = new SimpleXMLElement('<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"></rss>');
        }

        return $xml;
    }

    /**
     * @return string
     */
    public function getFeedData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout' => 2,
                'maxredirects' => 5,
                'useragent' => $this->getUserAgent(),
                'referer' => $this->getCurrentUrl(),
                'verifypeer' => false,
                'verifyhost' => false,
            ]
        );
        $curl->write( Request::METHOD_GET, $this->feedConfig->getFeedUrl(), '1.0');
        $data = $curl->read();
        if ($data === false || $data === '') {
            return '';
        }
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        return $data;
    }
    /**
     * @return string
     */
    protected function getUserAgent()
    {
        return sprintf("Olegnax: %s/%s (%s)", $this->productMetadata->getName(),
            $this->productMetadata->getVersion(), $this->productMetadata->getEdition());
    }

    /**
     * @return StoreManagerInterface
     */
    public function getCurrentUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl();
    }

    /**
     * @param SimpleXMLElement $data
     * @return string
     */
    private function escapeString(SimpleXMLElement $data)
    {
        return htmlspecialchars((string)$data);
    }

    /**
     * @param string $extensions
     * @param string $vendor
     * @return bool|string
     */
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

    /**
     * @param string $extension
     * @return array
     */
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

    /**
     * @param string $vendor
     * @return array
     */
    protected function getInstalledExt($vendor = '')
    {
        $modules = $this->moduleListInterface->getNames();

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

    /**
     * @param string $moduleName
     * @param string $version
     * @return string|null
     */
    protected function validateVersion($moduleName, $version)
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
    protected function getComposerVersion($moduleName, $type = ComponentRegistrar::MODULE)
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
    protected function validateExtInstalled($extensions, $vendor = '')
    {
        return $this->validateExt($extensions, $vendor);
    }

    /**
     * @param string $extensions
     * @param string $vendor
     * @return array|bool
     */
    protected function validateExtNotInstalled($extensions, $vendor = '')
    {
        return $this->validateExt($extensions, $vendor);
    }

    /**
     * @param array $types
     * @return $this
     */
    public function removeOXType($types = [])
    {

        if (empty($types) || !is_array($types)) {
            return $this;
        }

        $types = array_unique($types);
        /** @var OXFactory $collection */
        $collection = $this->oxFactory->create()->addFieldToFilter('ox_type',
            array('in' => $types));
        foreach ($collection as $model) {
            $model->setIsRemove(1)->save()->delete();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeExpiredItems()
    {
        if ($this->feedStatusManager->getLastRemove() + $this->feedConfig->getRemoveFrequency() > time()) {
            return $this;
        }

        /** @var ExpiredFactory $collection */
        $collection = $this->expiredFactory->create();
        foreach ($collection as $model) {
            $model->setIsRemove(1)->save()->delete();
        }
        $this->feedStatusManager->setLastRemove();

        return $this;
    }


    /**
     * @param SimpleXMLElement $link
     * @return mixed
     */
    private function isItemExists($link)
    {
        return $this->existsFactory->create()->execute($this->escapeString($link));
    }

}
