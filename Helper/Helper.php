<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Helper;

use Magento\Catalog\Helper\Product\Compare;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Olegnax\Core\Block\ChildTemplate;

class Helper extends AbstractHelper
{

    const CONFIG_MODULE = 'ox_core';
    const CHILD_TEMPLATE = ChildTemplate::class;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var array
     */
    protected $isArea = [];

    public function __construct(Context $context)
    {
        $this->objectManager = ObjectManager::getInstance();

        parent::__construct($context);
    }

    public function setModuleConfig(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if (!empty($path)) {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->setSystemValue($path, $value, $scope, $scopeId);
    }

    public function setSystemValue($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        return $this->_loadObject(WriterInterface::class)->save($path, $value, $scope, $scopeId);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function _loadObject($path)
    {
        return $this->objectManager->get($path);
    }

    public function getModuleConfig($path = '', $storeCode = null)
    {
        if (!empty($path)) {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            !empty($storeCode) ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeCode
        );
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }

    /**
     * @param string $area
     * @return bool
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->_loadObject(State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * @param string $path
     * @param array $arguments
     * @return mixed
     */
    public function _createObject($path, $arguments = [])
    {
        return $this->objectManager->create($path, $arguments);
    }

    public function getLayoutTemplateHtml($block, $option_path = '', $fileName = '', $arguments = [])
    {
        $value = $this->getConfig($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getLayoutTemplateHtmlbyValue($block, $value, $fileName, $arguments);
        }
        return '';
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getLayoutTemplateHtmlbyValue(
        $block,
        $value = null,
        $fileName = null,
        $arguments = [],
        $separator = '/'
    ) {
        $_fileName = '';
        if (empty($fileName)) {
            $blockTemplate = $block->getTemplate();
            if (preg_match('/(\.[^\.]+?)$/', $blockTemplate)) {
                $fileName = preg_replace('/(\.[^\.]+?)$/', '%s%s$1', $blockTemplate);
            } else {
                $fileName .= '%s%s';
            }
        } else {
            $_fileName = $fileName;
        }
        $blockName = $separator . $block->getNameInLayout() . $separator . $_fileName . $separator . $value;
        $fileName = sprintf($fileName, $separator, $value);
        while ($block->getLayout()->getBlock($blockName)) {
            $blockName .= '_0';
        }
        $_block = $block->getLayout()->createBlock(static::CHILD_TEMPLATE, $blockName);
        $block->setChild($_block->getNameInLayout(), $_block);
        if (!empty($arguments) && is_array($arguments)) {
            foreach ($arguments as $key => $value) {
                $_block->addData($key, $value);
            }
        }
        $content = $_block->setTemplate($fileName)->toHtml();

        return $content;
    }

    public function getLayoutBlockHtml($block, $option_path = '', $blockName = null)
    {
        $value = $this->getConfig($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getBlockValueHtmlby($block, $value, $blockName);
        }
        return '';
    }

    public function getLayoutBlockHtmlbyValue($block, $value = null, $blockName = null, $separator = '/')
    {
        if (empty($blockName)) {
            $blockName = $block->getNameInLayout();
        }
        $blockName = $blockName . $separator . $value;
        $_block = $block->getLayout()->getBlock($blockName);
        $content = '';
        if ($_block) {
            $block->setChild($blockName, $_block);
            $content = $_block->toHtml();

            return $content;
        }

        return $content;
    }

    public function isLoggedIn()
    {
        return $this->_loadObject(Session::class)->isLoggedIn();
    }

    public function getWishlistCount()
    {
        return $this->_loadObject(Data::class)->getItemCount();
    }

    public function getCompareListUrl()
    {
        return $this->_loadObject(Compare::class)->getListUrl();
    }

    public function getCompareListCount()
    {
        return $this->_loadObject(Compare::class)->getItemCount();
    }

    public function getBaseUrl($type = UrlInterface::URL_TYPE_LINK, $secure = null)
    {
        return $this->getStore()->getBaseUrl($type, $secure);
    }

    public function getStore()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->_loadObject(StoreManagerInterface::class);
        return $storeManager->getStore();
    }

    public function isMobile()
    {
        $user_agent = filter_input(INPUT_SERVER, HTTP_USER_AGENT);
        $result = false;
        if (!empty($user_agent)) {
            $result = preg_match(
                "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
                $user_agent);
        }

        return $result;
    }

    public function getBlockTemplateProcessor($content = '')
    {
        $blockFilter = $this->_loadObject(FilterProvider::class)->getBlockFilter();
        return $blockFilter->filter(trim($content));
    }

    public function getUrl($route = '', $params = [])
    {
        /** @var UrlInterface $urlBuilder */
        $urlBuilder = $this->_loadObject(UrlInterface::class);
        return $urlBuilder->getUrl($route, $params);
    }

    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

}
