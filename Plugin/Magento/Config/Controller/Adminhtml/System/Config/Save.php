<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Plugin\Magento\Config\Controller\Adminhtml\System\Config;


use Closure;
use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Config\Controller\Adminhtml\System\Config\Save as Config;
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
    protected $O0O;
    /**
     * @var RedirectFactory
     */
    protected $O00;
    /**
     * @var RequestInterface
     */
    protected $OOO;

    /**
     * Save constructor.
     *
     * @param RequestInterface $a
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        RequestInterface $OOO,
        ManagerInterface $O0O,
        RedirectFactory $OO0
    ) {
        $this->OOO = $OOO;
        $this->O0O = $O0O;
        $this->O00 = $OO0;
    }

    /**
     * @param Config $OOOO
     * @param Closure $OOO
     *
     * @return Redirect
     */
    public function aroundExecute(
        Config $OOOO,
        Closure $OOO
    ) {

        $O0OO = $this->OOO->getParam('section');
        $O00O = $this->OO0('YWN0aXZl');
        $OOOOOO = $this->OO0('Z2V0');
        $OOOO0O = $this->OO0('ZGF0YQ==');
        $OOO00O = $this->OO0('c3RhdHVz');
        $OO0O0O = $this->OO0('YWN0aXZhdGlvbl9pZA==');
        $O0OO0O = $this->OO0('dGhlX2tleQ==');
        $O000 = time();
        $OO0 = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        switch ($O0OO) {
            case $this->OO0('YXRobGV0ZTJfc2V0dGluZ3M='):
            case $this->OO0('YXRobGV0ZTJfZGVzaWdu'):
                $OOO0 = $this->O0O('XE9sZWduYXhcQXRobGV0ZTJcSGVscGVyXEhlbHBlcg==');
                if ($OOO0) {
                    $OO0OO = $this->OO0('Z2VuZXJhbC9pbnN0YWxsX2RhdGU=');
                    $OO0O0 = (int)$OOO0->getModuleConfig($OO0OO, 0, $OO0);
                    if ($OO0O0) {
                        if ($OO0O0 && $O000 > $OO0O0 + rand(27 * 44800, 28 * 49371)) {
                            if ($OOO0->$OOOOOO()
                                && !is_string($OOO0->$OOOOOO())
                                && $this->O00($this->O00($OOO0->$OOOOOO(), $OOOO0O), $OO0O0O)
                                && $this->O00($this->O00($OOO0->$OOOOOO(), $OOOO0O), $O0OO0O)
                                && $O00O == $this->O00($this->O00($OOO0->$OOOOOO(), $OOOO0O), $OOO00O)
                            ) {
                                return $OOO();
                            }
                        } else {
                            return $OOO();
                        }
                    } else {
                        $OOO0->setModuleConfig($OO0OO, time(), $OO0, 0);
                        return $OOO();
                    }
                }
                break;
            case $this->OO0('b2xlZ25heF9pbnN0YWdyYW1fcHJv'):
            case $this->OO0('b2xlZ25heF9pbnN0YWdyYW1fcHJvX2FwcGVhcmFuY2U='):
                $OO00 = $this->O0O('XE9sZWduYXhcSW5zdGFncmFtRmVlZFByb1xIZWxwZXJcSGVscGVy');
                if ($OO00) {
                    $OO00O = $this->OO0('Z2VuZXJhbC9pbnN0YWxsX2RhdGU=');
                    $OO000 = (int)$OO00->getModuleConfig($OO00O, 0, $OO0);
                    if ($OO000) {
                        if ($OO000 > $O000 - rand(18900 * 64, 45 * 30720)) {
                            if ($OO00 && $OO00->$OOOOOO() && !is_string($OO00->$OOOOOO())
                                && $this->O00($OO00->$OOOOOO(), $OOOO0O)) {
                                if (
                                    $this->O00($this->O00($OO00->$OOOOOO(), $OOOO0O), $OOO00O) == $O00O
                                    && $this->O00($this->O00($OO00->$OOOOOO(), $OOOO0O), $O0OO0O)
                                    && $this->O00($this->O00($OO00->$OOOOOO(), $OOOO0O), $OO0O0O)
                                ) {
                                    return $OOO();
                                }
                            }
                        } else {
                            return $OOO();
                        }
                    } else {
                        $OO00->setModuleConfig($OO00O, time(), $OO0, 0);
                        return $OOO();
                    }
                }
                break;
            case $this->OO0('b2xlZ25heF9pbmZpbml0ZXNjcm9sbF9wcm8='):
                $O0O0 = $this->O0O('XE9sZWduYXhcSW5maW5pdGVTY3JvbGxQcm9cSGVscGVyXEhlbHBlcg==');
                if ($O0O0) {
                    $O0O0O = $this->OO0('Z2VuZXJhbC9pbnN0YWxsX2RhdGU=');
                    $O0O00 = (int)$O0O0->getModuleConfig($O0O0O, 0, $OO0);
                    if ($O0O00) {
                        if ($O000 > $O0O00 + rand(9450 * 128, 246 * 5619)) {
                            if ($O0O0 && $O0O0->$OOOOOO() && !is_string($O0O0->$OOOOOO())
                                && $this->O00($O0O0->$OOOOOO(), $OOOO0O)) {
                                if (
                                    $this->O00($this->O00($O0O0->$OOOOOO(), $OOOO0O), $O0OO0O)
                                    && $this->O00($this->O00($O0O0->$OOOOOO(), $OOOO0O), $OO0O0O)
                                    && $O0O0->$OOOOOO()->data->status == $O00O) {
                                    return $OOO();
                                }
                            }
                        } else {
                            return $OOO();
                        }
                    } else {
                        $O0O0->setModuleConfig($O0O0O, time(), $OO0, 0);
                        return $OOO();
                    }
                }
                break;
            default:
                return $OOO();
                break;
        }
        $this->O0O->addErrorMessage($this->OO0('RXJyb3IgTDEgOiBDb25maWd1cmF0aW9uIHdhcyBub3Qgc2F2ZWQu'));
        /** @var Redirect $O00 */
        $O00 = $this->O00->create();
        return $O00->setPath(
            $this->OO0('YWRtaW5odG1sL3N5c3RlbV9jb25maWcvZWRpdA=='),
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true,
            ]
        );
    }

    /**
     * @param string $b
     *
     * @return string
     */
    private function OO0($b)
    {
        $a = '_';
        $a .= 'd';
        $f = '4' . $a;
        $f .= 'e';
        $k = '6' . $f;
        $k .= 'c';
        $z = 'e' . $k;
        $z .= 'o';
        $h = 's' . $z;
        $h .= 'd';
        $o = 'a' . $h;
        $o .= 'e';
        $oo = 'b' . $o;

        return $oo($b);
    }

    /**
     * @param string $object
     *
     * @return mixed
     */
    private function O0O($object)
    {
        $object = $this->OO0($object);
        if (class_exists($object)) {
            return ObjectManager::getInstance()->get($object);
        } else {
            return null;
        }
    }

    private function O00($a, $b)
    {
        return ($a && is_object($a) && !is_array($a) && isset($a->$b)) ? $a->$b : null;
    }
}
