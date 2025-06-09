<?php

namespace Olegnax\Core\Model\Feed;


class FeedConfig
{
    /**
     * @var string
     */
    const FEED_URL = "olegnax.com/extras/products-status/feed.xml";

    /**
     * @var integer
     */
    const FREQUENCY = 24;

    /**
     * @var integer
     */
    const REMOVE_FREQUENCY = 6;

    /**
     * Get the feed remove frequency.
     *
     * @return int
     */
    public function getRemoveFrequency()
    {
        return self::REMOVE_FREQUENCY;
    }

    /**
     * Get the feed frequency.
     *
     * @return int
     */
    public function getFrequency()
    {
        return self::FREQUENCY * 3600;
    }

    /**
     * @return string
     */
    public function getFeedUrl()
    {
        return $this->getScheme() . self::FEED_URL;
    }

    /**
     * @return string
     */
    private function getScheme()
    {
        return 'https://';
    }
}
