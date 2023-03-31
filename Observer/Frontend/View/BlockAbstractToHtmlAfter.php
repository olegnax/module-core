<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Core\Observer\Frontend\View;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface as ViewLayout;
use Olegnax\Core\Helper\Helper;

class BlockAbstractToHtmlAfter implements ObserverInterface
{
    const BLOCK_NAME = 'ox_require.js';
    const FIND_BLOCK_NAME = 'require.js';
    const BLOCK_TEMPLATE = 'Olegnax_Core::require_js.phtml';
    /**
     * @var Helper
     */
    protected $_helper;
    /**
     * @var ViewLayout
     */
    protected $_viewLayout;

    /**
     * BlockAbstractToHtmlAfter constructor.
     * @param Helper $helper
     * @param ViewLayout $viewLayout
     */
    public function __construct(
        Helper $helper,
        ViewLayout $viewLayout
    ) {
        $this->_helper = $helper;
        $this->_viewLayout = $viewLayout;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {

        if (!$this->_helper->isEnabled()) {
            return;
        }
        /** @var AbstractBlock $block */
        $block = $observer->getData('block');
        if (static::FIND_BLOCK_NAME == $block->getNameInLayout()) {
            $_html = $block = $this->_viewLayout
                ->createBlock(
                    Template::class,
                    static::BLOCK_NAME,
                    [
                        'data' =>
                            [
                                'helper' => $this->_helper,
                            ],
                    ]
                )
                ->setTemplate(static::BLOCK_TEMPLATE)
                ->toHtml();

            if (empty($_html)) {
                return;
            }
            /** @var DataObject $transport */
            $transport = $observer->getData('transport');
            /** @var string $html */
            $html = $transport->getData('html');
            $html .= $_html;
            $transport->setData('html', $html);
            $observer->setData('transport', $transport);
        }
    }
}
