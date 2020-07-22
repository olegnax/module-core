<?php


namespace Olegnax\Core\Helper;


use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\ModuleListInterface;
use Zend_Http_Client;

class ModuleInfo extends Helper
{
    const MODULE_URL = 'https://olegnax.com/extras/products-status/magento2_modules.json';
    const CACHE_PERIOD = 24 * 60 * 60;
    const XML_PATH_CACHE = 'olegnax_core/module/cache';
    const XML_PATH_CACHE_DATE = 'olegnax_core/module/datacache';
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;
    /**
     * @var CurlFactory
     */
    protected $curlFactory;
    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var string
     */
    protected $_moduleUrl;
    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;
    /**
     * @var ReadFactory
     */
    protected $readFactory;

    public function __construct(
        Context $context,
        CurlFactory $curlFactory,
        ProductMetadataInterface $productMetadata,
        RemoteAddress $remoteAddress,
        ComponentRegistrarInterface $componentRegistrar,
        ModuleListInterface $moduleList,
        ReadFactory $readFactory,
        RequestInterface $request
    ) {
        $this->curlFactory = $curlFactory;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->remoteAddress = $remoteAddress;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->request = $request;
        parent::__construct($context);
    }

    public function getServer(
        $name
    ) {
        return $this->request->getServer($name);
    }

    public function getModuleInfo($moduleName, $onlyInstalled = true)
    {
        $data = $this->getModule($moduleName);
        if ('' == $data['setup_version'] && $onlyInstalled) {
            return [];
        }
        $apiData = $this->getData();
        if (array_key_exists($moduleName, $apiData)) {
            $data = array_replace($data, $apiData[$moduleName]);
        }
        if (!isset($data['theme_module'])) {
            $data['theme_module'] = false;
        }
        $data['server_version'] = isset($data['version']) ? $data['version'] : $data['setup_version'];
        $data['update_status'] = version_compare(
            $data['server_version'],
            $data['setup_version'],
            '>'
        );

        return $data;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isActive($moduleName){
        return $this->_moduleManager->isEnabled($moduleName);
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
        $module['title'] = str_replace('_', ' ', $module['name']);
        return (array)$module;
    }

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

    public function getData($forsed = false)
    {
        $date = (int)$this->getSystemDefaultValue(static::XML_PATH_CACHE_DATE);
        try {
            if (empty($this->data)) {
                $this->data = $this->decodeData($this->getSystemDefaultValue(static::XML_PATH_CACHE));
            }
            if ($date + static::CACHE_PERIOD < time() || $forsed || empty($this->data)) {
                $this->data = $this->decodeData($this->loadData());
                if (!empty($this->data)) {
                    $this->setSystemValue(
                        static::XML_PATH_CACHE,
                        json_encode($this->data),
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        0
                    );
                    $this->setSystemValue(
                        static::XML_PATH_CACHE_DATE,
                        time(),
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        0
                    );
                }
            }
        } catch (Exception $e) {
            $this->data = [];
        }

        return $this->data;
    }

    private function decodeData($data = '')
    {
        if (empty($data)) {
            return [];
        }
        return (array)json_decode($data, true);
    }

    protected function loadData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout' => 2,
                'maxredirects' => 5,
                'useragent' => sprintf(
                    'Olegnax: %s/%s (%s)',
                    $this->productMetadata->getName(),
                    $this->productMetadata->getVersion(),
                    $this->productMetadata->getEdition()
                ),
                'referer' => $this->getSystemDefaultValue('web/unsecure/base_url'),
                'verifypeer' => 0,
                'verifyhost' => 0,
            ]
        );
        $curl->write(Zend_Http_Client::GET, $this->getModuleUrl());
        $data = $curl->read();
        $curl->close();

        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);

        return $data;
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
        return $this->remoteAddress->getRemoteAddress();
    }

    public function getThemeInfo($themePath, $themeName = null, $onlyInstalled = true)
    {
        $data = $this->getTheme($themePath);
        if ('' == $data['setup_version'] && $onlyInstalled) {
            return [];
        }
        if (empty($themeName) && isset($data['name']) && !empty($data['name'])) {
            $themeName = $data['name'];
        }
        $apiData = $this->getData();
        if (!empty($themeName) && array_key_exists($themeName, $apiData)) {
            $data = array_replace($data, $apiData[$themeName]);
        }
        $data['server_version'] = isset($data['version']) ? $data['version'] : $data['setup_version'];
        $data['update_status'] = version_compare(
            $data['server_version'],
            $data['setup_version'],
            '>'
        );

        return $data;
    }

    private function getTheme($themePath)
    {
        $theme = [
            'path' => $themePath,
            'setup_version' => $this->getComposerVersion($themePath, ComponentRegistrar::THEME),
        ];
        $name = $this->getThemeName($themePath);
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


}