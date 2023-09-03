<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Helper;

use Exception;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Olegnax\Core\Block\ChildTemplate;
use Psr\Log\LoggerInterface;

class Helper extends AbstractHelper
{


    const XML_PATH_CORE_LAZY = 'olegnax_core_settings/general/lazyload';
    const CONFIG_MODULE = 'olegnax_core_settings';
    const CHILD_TEMPLATE = ChildTemplate::class;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var array
     */
    protected $isArea = [];
    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(
        Context $context,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;

        parent::__construct($context);
        if (!$this->getModuleConfig(
                'general/install_date',
                0,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )
        ) {
            $this->setModuleConfig('general/install_date', time());
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
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
        return ObjectManager::getInstance()->get($path);
    }

    /**
     * @return string
     */
    public function getThemeCode()
    {
        /** @var DesignInterface $appState */
        $appState = $this->_loadObject(DesignInterface::class);
        return $appState->getDesignTheme()->getCode();
    }
    /**
     * @return bool
     */
    public function isMobileTheme()
    {
		return (bool) preg_match('@^Olegnax\/a2m@', $this->getThemeCode());
    }
    /**
     * @return bool
     */
    public function isLazyLoadEnabled(){
        return (bool) $this->getSystemValue(static::XML_PATH_CORE_LAZY);
    }

    /**
     * @param string $path
     * @param integer $storeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getModuleConfig($path = '', $storeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if (empty($path)) {
            $path = static::CONFIG_MODULE;
        } else {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->getSystemValue($path, $storeCode, $scopeType);
    }

    /**
     * @param string $path
     * @param integer $storeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getSystemValue($path, $storeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $value = $this->scopeConfig->getValue(
            $path,
            $scopeType,
            $storeCode
        );
        if (is_null($value)) {
            $value = '';
        }
        return $value;
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getSystemDefaultValue($path)
    {
        return $this->getSystemValue(
            $path,
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
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
            } catch ( Exception $e) {
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
        return ObjectManager::getInstance()->create($path, $arguments);
    }

    public function getLayoutTemplateHtml($block, $option_path = '', $fileName = '', $arguments = [])
    {
        $value = $this->getSystemValue($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getLayoutTemplateHtmlbyValue($block, $value, $fileName, $arguments);
        }
        return '';
    }

    /**
     * @param string $path
     * @param integer $storeCode
     * @return mixed
     */
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

		if (preg_match($regex_match, strtolower((string)$_SERVER['HTTP_USER_AGENT']))) {
			return TRUE;
		}

		if ((strpos(strtolower((string)$_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
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

		if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower((string)$_SERVER['ALL_HTTP']),'OperaMini') > 0) {
			return TRUE;
		}

		return FALSE;
    }

    public function getBlockTemplateProcessor($content = '')
    {
        if (empty($content) || !is_string($content)) {
            $content = '';
        }
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

    public function getLogger()
    {
        return $this->logger;
    }

    public function log($level, $plugin, $message, array $context = [])
    {
        $message = sprintf('%s: %s', $plugin, trim($message));
        $this->getLogger()->log($level, $message, $context);
    }

    public function debug($plugin, $message, array $context = [])
    {
        $this->log(100, $plugin, $message, $context);
    }
}
