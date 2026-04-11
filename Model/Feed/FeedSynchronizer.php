<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\Feed;

use Exception;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Notification\MessageInterface;
use Olegnax\Core\Model\Feed\FeedStatusManager;
use Olegnax\Core\Model\Feed\ExtensionValidator;
use Olegnax\Core\Model\Feed\FeedDataProvider;
use Olegnax\Core\Model\Feed\FeedCleaner;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxContent;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxContentP;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OxUpdate;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class FeedSynchronizer
{
    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var FeedStatusManager
     */
    private $feedStatusManager;

    /**
     * @var FeedDataProvider
     */
    private $feedDataProvider;

    /**
     * @var ExtensionValidator
     */
    private $extensionValidator;

    /**
     * @var FeedCleaner
     */
    private $feedCleaner;

    /**
     * @var InboxFactory
     */
    private $inboxFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FeedStatusManager $feedStatusManager,
        FeedDataProvider $feedDataProvider,
        ExtensionValidator $extensionValidator,
        FeedCleaner $feedCleaner,
        InboxFactory $inboxFactory,
        LoggerInterface $logger,
        LockManagerInterface $lockManager
    ) {
        $this->feedStatusManager = $feedStatusManager;
        $this->feedDataProvider = $feedDataProvider;
        $this->extensionValidator = $extensionValidator;
        $this->feedCleaner = $feedCleaner;
        $this->inboxFactory = $inboxFactory;
        $this->logger = $logger;
        $this->lockManager = $lockManager;
    }

    /**
     * @return $this
     */
    public function sync()
    {
        $lockName = 'olegnax_core_feed_update_lock';
        if (!$this->lockManager->lock($lockName, 0)) {
            return $this;
        }

        try {
            $this->feedStatusManager->setLastUpdate();
            $feedData = [];
            $feedXml = $this->feedDataProvider->getFeedXml();
            $installDate = $this->feedStatusManager->getInstallDate();
            $types = [];

            if ($feedXml && current($feedXml->xpath('channel/item'))) {
                foreach ($feedXml->channel->item as $item) {
                    $itemData = $this->prepareFeedItemData($item, $installDate, $types);
                    if ($itemData !== null) {
                        $feedData[] = $itemData;
                    }
                }

                if ($feedData) {
                    if (!empty($types)) {
                        $this->feedCleaner->removeOXType($types);
                    }
                    $this->inboxFactory->create()->parse(array_reverse($feedData));
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            $this->lockManager->unlock($lockName);
        }

        return $this;
    }

    /**
     * Processes individual feed item to validate and prepare array payload for notification
     *
     * @param SimpleXMLElement $item
     * @param int $installDate
     * @param array &$types To collect notification categories for cleanup
     * @return array|null
     */
    private function prepareFeedItemData(SimpleXMLElement $item, $installDate, array &$types)
    {
        $severity = isset($item->severity) ? (int)$item->severity : MessageInterface::SEVERITY_NOTICE;
        $type = isset($item->type) ? $this->escapeString($item->type) : null;
        $extensionUpdate = $this->extensionValidator->validateExtNeedUpdate($item->extensionUpdate, 'Olegnax');
        $validate = [];

        if ($extensionUpdate) {
            $type = OxUpdate::TYPE_UPDATE;
            $validate = [
                'extension_update' => $extensionUpdate,
            ];
        }

        $pubDate = strtotime((string)$item->pubDate);
        $expirationDate = (string)$item->expirationDate ? strtotime((string)$item->expirationDate) : null;

        if (!$this->isValidItem($item, $installDate, $pubDate, $expirationDate, $extensionUpdate, $type, $severity)) {
            return null;
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

        return $_feedData;
    }

    /**
     * @param SimpleXMLElement $item
     * @param int $installDate
     * @param int $pubDate
     * @param int|null $expirationDate
     * @param string|bool $extensionUpdate
     * @param string|null $type
     * @param int $severity
     * @return bool
     */
    private function isValidItem(
        SimpleXMLElement $item,
        $installDate,
        $pubDate,
        $expirationDate,
        $extensionUpdate,
        $type,
        $severity
    ) {
        if ($installDate > $pubDate) {
            return false;
        }
        if ($expirationDate && $expirationDate < gmdate('U')) {
            return false;
        }
        if ($item->extension_update && !$extensionUpdate) {
            return false;
        }
        if (!$type && MessageInterface::SEVERITY_NOTICE > $severity) {
            return false;
        }
        if (!$this->extensionValidator->validateExtInstalled($item->extension, 'Olegnax')) {
            return false;
        }
        if (!$this->extensionValidator->validateExtNotInstalled($item->extensionNot, 'Olegnax')) {
            return false;
        }
        if (!$this->extensionValidator->validateExtInstalled($item->extension3D)) {
            return false;
        }

        return true;
    }

    /**
     * @param SimpleXMLElement $data
     * @return string
     */
    private function escapeString(SimpleXMLElement $data)
    {
        return htmlspecialchars((string)$data);
    }
}
