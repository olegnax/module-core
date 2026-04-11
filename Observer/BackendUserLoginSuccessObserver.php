<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Observer;

use Exception;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Olegnax\Core\Model\Feed\FeedSynchronizer;
use Olegnax\Core\Model\Feed\FeedCleaner;
use Olegnax\Core\Model\Feed\FeedConfig;
use Olegnax\Core\Model\Feed\FeedStatusManager;
use Psr\Log\LoggerInterface;

class BackendUserLoginSuccessObserver implements ObserverInterface
{
    /**
     * @var FeedConfig
     */
    private $feedConfig;

    /**
     * @var FeedStatusManager
     */
    private $feedStatusManager;

    /**
     * @var FeedSynchronizer
     */
    private $feedSynchronizer;

    /**
     * @var FeedCleaner
     */
    private $feedCleaner;

    /**
     * @var Session
     */
    protected $_backendAuthSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FeedSynchronizer $feedSynchronizer,
        FeedCleaner $feedCleaner,
        FeedStatusManager $feedStatusManager,
        FeedConfig $feedConfig,
        Session $backendAuthSession,
        LoggerInterface $logger
    ) {
        $this->feedSynchronizer = $feedSynchronizer;
        $this->feedCleaner = $feedCleaner;
        $this->feedStatusManager = $feedStatusManager;
        $this->feedConfig = $feedConfig;
        $this->_backendAuthSession = $backendAuthSession;
        $this->logger = $logger;
    }

    /**
     * Executed on backend user login success
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->feedConfig->getFrequency() + $this->feedStatusManager->getLastUpdate() > time()) {
            return;
        }
        try {
            $this->feedSynchronizer->sync();
            $this->feedCleaner->removeExpiredItems();
        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
