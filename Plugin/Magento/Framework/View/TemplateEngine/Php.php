<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Plugin\Magento\Framework\View\TemplateEngine;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;

class Php
{

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Php constructor.
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @param TemplateEnginePhp $subject
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return array
     */
    public function beforeRender(
        TemplateEnginePhp $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ) {
        if (!isset($dictionary['escaper'])) {
            $dictionary['escaper'] = $this->escaper;
        }

        return [$block, $fileName, $dictionary];
    }
}
