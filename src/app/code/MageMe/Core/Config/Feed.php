<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\Core\Config;

/**
 * Class Feed
 */
class Feed extends \Magento\AdminNotification\Model\Feed
{
    const MAGEME_FEED_URL = 'mageme.com/feeds/m2.rss';

    public function getFeedUrl(): string
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . self::MAGEME_FEED_URL;
        }
        return $this->_feedUrl;
    }

    public function observe()
    {
        $this->checkUpdate();
    }

    /**
     * @inheritdoc
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('mageme_notifications_lastcheck');
    }

    /**
     * @inheritdoc
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'mageme_notifications_lastcheck');

        return $this;
    }
}
