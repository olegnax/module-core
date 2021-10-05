<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Plugin\Magento\Config\Controller\Adminhtml\System\Config;


use Closure;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class Save
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Save constructor.
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->_request = $request;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param \Magento\Config\Controller\Adminhtml\System\Config\Save $subject
     * @param Closure $proceed
     * @return Redirect
     */
    public function aroundExecute(
        \Magento\Config\Controller\Adminhtml\System\Config\Save $subject,
        Closure $proceed
    ) {

        $section = $this->_request->getParam('section');
        $s = "active";
        $deta = time();

        switch ($section) {
            case $this->reader('YXRobGV0ZTJfc2V0dGluZ3M='):
            case $this->reader('YXRobGV0ZTJfZGVzaWdu'):
                $object = $this->loader('XE9sZWduYXhcQXRobGV0ZTJcSGVscGVyXEhlbHBlcg==');
                if ($object) {
                    $date = (int)$object->getModuleConfig(
                        $this->reader('Z2VuZXJhbC9pbnN0YWxsX2RhdGU='),
                        0,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                    if ($date && $deta > $date + 27 * 44800) {
                        if ($object->get()
                            && !is_string($object->get())
                            && isset($object->get()->data)
                            && $s == $object->get()->data->status
                        ) {
                            return $proceed();
                        }
                    } else {
                        return $proceed();
                    }
                }
                break;
            case $this->reader('b2xlZ25heF9pbnN0YWdyYW1fcHJv'):
            case $this->reader('b2xlZ25heF9pbnN0YWdyYW1fcHJvX2FwcGVhcmFuY2U='):
                $object = $this->loader('XE9sZWduYXhcSW5zdGFncmFtRmVlZFByb1xIZWxwZXJcSGVscGVy');
                if ($object) {
                    $date = (int)$object->getModuleConfig(
                        $this->reader('Z2VuZXJhbC9pbnN0YWxsX2RhdGU='),
                        0,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                    if ($date) {
                        if ($deta > $date + 18900 * 64) {
                            if ($object && $object->get() && !is_string($object->get())
                                && isset($object->get()->data)) {
                                if ($object->get()->data->status == $s) {
                                    return $proceed();
                                }
                            }
                        } else {
                            return $proceed();
                        }
                    } else {
                        return $proceed();
                    }
                }
                break;
            case $this->reader('b2xlZ25heF9pbmZpbml0ZXNjcm9sbF9wcm8='):
                $object = $this->loader('XE9sZWduYXhcSW5maW5pdGVTY3JvbGxQcm9cSGVscGVyXEhlbHBlcg==');
                if ($object) {
                    $date = (int)$object->getModuleConfig(
                        $this->reader('Z2VuZXJhbC9pbnN0YWxsX2RhdGU='),
                        0,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                    if ($date) {
                        if ($deta > $date + 9450 * 128) {
                            if ($object && $object->get() && !is_string($object->get())
                                && isset($object->get()->data)) {
                                if ($object->get()->data->status == $s) {
                                    return $proceed();
                                }
                            }
                        } else {
                            return $proceed();
                        }
                    } else {
                        return $proceed();
                    }
                }
                break;
            default:
                return $proceed();
                break;
        }
        $eCode = base64_decode('RXJyb3IgTDEgOiBDb25maWd1cmF0aW9uIHdhcyBub3Qgc2F2ZWQu');
        $this->messageManager->addErrorMessage($eCode);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            $this->reader('YWRtaW5odG1sL3N5c3RlbV9jb25maWcvZWRpdA=='),
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true,
            ]
        );
    }

    /**
     * @param string $str
     * @return string
     */
    private function reader($str)
    {
        return base64_decode($str);
    }

    /**
     * @param string $object
     * @return mixed
     */
    private function loader($object)
    {
        $object = $this->reader($object);
        if (class_exists($object)) {
            return ObjectManager::getInstance()->get($object);
        } else {
            return null;
        }
    }
}
