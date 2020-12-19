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
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
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
        $result = $this->_loadObject(WriterInterface::class)->save($path, $value, $scope, $scopeId);
        $this->scopeConfig->clean();
        return $result;
    }

    public function deleteSystemValue($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $result = $this->_loadObject(WriterInterface::class)->delete($path, $scope, $scopeId);
        $this->scopeConfig->clean();
        return $result;
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
        if (empty($path)) {
            $path = static::CONFIG_MODULE;
        } else {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function getSystemDefaultValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
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
            } catch (\Exception $e) {
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
        $value = $this->getSystemValue($option_path);

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
            if (!preg_match('#([^_\:])_([^_\:])\:\:#i', $fileName)) {
                $className = array_slice(array_filter(explode('\\', get_class($block))), 0, 2);
                if ('Magento' !== $className[0]) {
                    $fileName = implode('_', $className) . '::' . $fileName;
                }
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
            $_block->addData($arguments);
        }
        $content = $_block->setTemplate($fileName)->toHtml();

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

    /**
     * Get Product version
     *
     * @return string
     */
    function getVersion()
    {
        return $this->_loadObject(ProductMetadataInterface::class)->getVersion();
    }

    /**
     * @param string $version
     * @param string $operator
     * @return bool
     */
    function getVersionCompare($version, $operator = '<=')
    {
        return version_compare($this->getVersion(), $version, $operator);
    }

    /**
     * @return bool
     */
    public function isLegacyJQuery() {
        return $this->getVersionCompare('2.3.2');
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
		$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|"  
					 . "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|"  
					 . "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|"  
					 . "symbian|smartphone|mmp|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|"  
					 . "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220"  
					 . ")/i";  

		if (preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']))) {  
			return TRUE;  
		}  

		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {  
			return TRUE;  
		}      

		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));  
		$mobile_agents = array(  
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
			'wapr','webc','winw','winw','xda ','xda-');  

		if (in_array($mobile_ua,$mobile_agents)) {  
			return TRUE;  
		}  

		if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {  
			return TRUE;  
		}  

		return FALSE;  
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


    public function clearCache()
    {
        /** @var TypeListInterface $cacheTypeList */
        $cacheTypeList = $this->_loadObject(TypeListInterface::class);
        $types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice',
        ];
        foreach ($types as $type) {
            $cacheTypeList->cleanType($type);
        }
        /** @var Pool $CacheFrontendPool */
        $CacheFrontendPool = $this->_loadObject(Pool::class);
        foreach ($CacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

}
