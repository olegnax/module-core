<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\Feed;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Core\Model\Feed\FeedConfig;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Throwable;

class FeedDataProvider
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var FeedConfig
     */
    private $feedConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrarInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CurlFactory $curlFactory,
        FeedConfig $feedConfig,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManagerInterface,
        ComponentRegistrarInterface $componentRegistrarInterface,
        LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->feedConfig = $feedConfig;
        $this->productMetadata = $productMetadata;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->componentRegistrarInterface = $componentRegistrarInterface;
        $this->logger = $logger;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            $xml = new SimpleXMLElement($data);
        } catch (Throwable $e) {
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
        $curl->setTimeout(2);
        $curl->setOption(CURLOPT_MAXREDIRS, 5);
        $curl->setOption(CURLOPT_USERAGENT, $this->getUserAgent());
        $curl->setOption(CURLOPT_REFERER, $this->getCurrentUrl());
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, 2);

        try {
            $curl->get($this->feedConfig->getFeedUrl());
            $data = $curl->getBody();
        } catch (\Exception $e) {
            $modulePath = $this->componentRegistrarInterface->getPath(
                ComponentRegistrar::MODULE, 
                'Olegnax_Core'
            );
            $caBundlePath = $modulePath . '/etc/cacert.pem';

            if (file_exists($caBundlePath)) {
                $curl->setOption(CURLOPT_CAINFO, $caBundlePath);
                
                try {
                    $curl->get($this->feedConfig->getFeedUrl());
                    $data = $curl->getBody();
                } catch (\Exception $fallbackException) {
                    $this->logger->error('Olegnax Feed Secure Fetch Failed: ' . $fallbackException->getMessage());
                    return '';
                }
            } else {
                return '';
            }
        }

        if ($data === false || $data === '') {
            return '';
        }

        return trim($data);
    }

    /**
     * @return string
     */
    private function getUserAgent()
    {
        return sprintf(
            "Olegnax: %s/%s (%s)",
            $this->productMetadata->getName(),
            $this->productMetadata->getVersion(),
            $this->productMetadata->getEdition()
        );
    }

    /**
     * @return string
     */
    private function getCurrentUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl();
    }
}
