<?php

namespace Olegnax\Core\Model\Feed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\FlagManager;

class FeedStatusManager
{

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * FeedConfig constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param FlagManager $flagManager
     */
    public function __construct(DeploymentConfig $deploymentConfig, FlagManager $flagManager)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->flagManager = $flagManager;
    }

    /**
     * Get the last update timestamp.
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return (int)$this->flagManager->getFlagData('olegnax_core_admin_notifications_lastcheck');
    }

    /**
     * Set the last update timestamp.
     *
     * @return void
     */
    public function setLastUpdate()
    {
        $this->flagManager->saveFlag('olegnax_core_admin_notifications_lastcheck', time());
    }

    /**
     * Get the install date.
     *
     * @return int
     */
    public function getInstallDate()
    {
        return strtotime($this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));
    }

    /**
     * Get the last remove timestamp.
     *
     * @return int
     */
    public function getLastRemove()
    {
        return (int)$this->flagManager->getFlagData('olegnax_core_admin_notifications_lastremove');
    }

    /**
     * Set the last remove timestamp.
     *
     * @return void
     */
    public function setLastRemove()
    {
        $this->flagManager->saveFlag('olegnax_core_admin_notifications_lastremove', time());
    }
}
