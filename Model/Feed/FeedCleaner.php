<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\Feed;

use Magento\Framework\Lock\LockManagerInterface;
use Olegnax\Core\Model\Feed\FeedConfig;
use Olegnax\Core\Model\Feed\FeedStatusManager;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\ExpiredFactory;
use Olegnax\Core\Model\ResourceModel\Inbox\Collection\OXFactory;

class FeedCleaner
{
    /**
     * @var FeedStatusManager
     */
    private $feedStatusManager;

    /**
     * @var FeedConfig
     */
    private $feedConfig;

    /**
     * @var ExpiredFactory
     */
    private $expiredFactory;

    /**
     * @var OXFactory
     */
    private $oxFactory;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    public function __construct(
        FeedStatusManager $feedStatusManager,
        FeedConfig $feedConfig,
        ExpiredFactory $expiredFactory,
        OXFactory $oxFactory,
        LockManagerInterface $lockManager
    ) {
        $this->feedStatusManager = $feedStatusManager;
        $this->feedConfig = $feedConfig;
        $this->expiredFactory = $expiredFactory;
        $this->oxFactory = $oxFactory;
        $this->lockManager = $lockManager;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function removeOXType(array $types = [])
    {
        if (empty($types)) {
            return $this;
        }

        $lockName = 'olegnax_core_feed_cleaner_lock';
        if (!$this->lockManager->lock($lockName, 0)) {
            return $this;
        }

        try {
            $types = array_unique($types);
            $collection = $this->oxFactory->create()->addFieldToFilter(
                'ox_type',
                ['in' => $types]
            );

            $this->massDelete($collection);
        } finally {
            $this->lockManager->unlock($lockName);
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

        $lockName = 'olegnax_core_feed_cleaner_lock';
        if (!$this->lockManager->lock($lockName, 0)) {
            return $this;
        }

        try {
            $this->feedStatusManager->setLastRemove();

            $collection = $this->expiredFactory->create();
            $this->massDelete($collection);
        } finally {
            $this->lockManager->unlock($lockName);
        }

        return $this;
    }

    /**
     * Mass delete items in a collection
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return void
     */
    private function massDelete($collection)
    {
        $ids = $collection->getAllIds();
        if (empty($ids)) {
            return;
        }

        $connection = $collection->getConnection();
        $table = $collection->getMainTable();
        $idField = $collection->getResource()->getIdFieldName();

        $batches = array_chunk($ids, 1000);
        foreach ($batches as $batch) {
            $connection->delete(
                $table,
                [$idField . ' IN (?)' => $batch]
            );
        }
    }
}
