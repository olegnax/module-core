<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Observer;

use Exception;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Olegnax\Core\Model\Feed;
use Olegnax\Core\Model\FeedFactory;
use Olegnax\Core\Model\Feed\FeedConfig;
use Olegnax\Core\Model\Feed\FeedStatusManager;
use Psr\Log\LoggerInterface;

class PredispatchAdminActionControllerObserver implements ObserverInterface
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
     * @var FeedFactory
     */
    protected $_feedFactory;

    /**
     * @var Session
     */
    protected $_backendAuthSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FeedFactory $feedFactory
     * @param Session $backendAuthSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeedFactory $feedFactory,
        FeedStatusManager $feedStatusManager,
        FeedConfig $feedConfig,
        Session $backendAuthSession,
        LoggerInterface $logger
    ) {
        $this->_feedFactory = $feedFactory;
        $this->feedStatusManager = $feedStatusManager;
        $this->feedConfig = $feedConfig;
        $this->_backendAuthSession = $backendAuthSession;
        $this->logger = $logger;
    }

    /**
     * Predispatch admin action controller
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            if ($this->feedConfig->getFrequency() + $this->feedStatusManager->getLastUpdate() > time()) {
                return;
            }
            try {
                $feedModel = $this->_feedFactory->create();
                /* @var $feedModel Feed */
                $feedModel->checkUpdate();
                $feedModel->removeExpiredItems();
            } catch (Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

}
