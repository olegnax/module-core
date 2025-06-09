<?php

namespace Olegnax\Core\Model\Feed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Olegnax\Core\Helper\Helper as CoreHelper;

class FeedStatusManager
{

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * FeedConfig constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param CoreHelper $coreHelper
     */
    public function __construct(DeploymentConfig $deploymentConfig, CoreHelper $coreHelper)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->coreHelper = $coreHelper;
    }


    /**
     * Get the last update timestamp.
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return (int)$this->coreHelper->getModuleConfig('admin_notifications/lastcheck');
    }

    /**
     * Set the last update timestamp.
     *
     * @return void
     */
    public function setLastUpdate()
    {
        $this->coreHelper->setModuleConfig('admin_notifications/lastcheck', time());
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
        return (int)$this->coreHelper->getModuleConfig('admin_notifications/lastremove');
    }

    /**
     * Set the last remove timestamp.
     *
     * @return void
     */
    public function setLastRemove()
    {
        $this->coreHelper->setModuleConfig('admin_notifications/lastremove', time());
    }
}
