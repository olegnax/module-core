<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block\Adminhtml;


use Olegnax\Core\Block\Template;

class Notice extends Template
{

    protected $collector;

    protected function _toHtml()
    {
        $contents = $this->getColection();
        $content = $this->itemSelection($contents);

        return $content;
    }

    /**
     * @return array
     */
    protected function getColection()
    {
        $contents = [];
        $colection = $this->loadColection();
        if ($colection && $colection->getSize()) {
            foreach ($colection as $notice) {
                $content = $notice->getOxContent();
                if (!empty($content)) {
                    $contents[] = $content;
                }

            }
        }

        return $contents;
    }

    protected function loadColection()
    {
        if ($this->collector) {
            return $this->collector->create();
        }

        return null;
    }

    /**
     * @param array $contents
     * @return string
     */
    protected function itemSelection($contents = [])
    {
        if (!empty($contents)) {
            $key = array_rand($contents);
            $contents = (string)$contents[$key];
        } else {
            $contents = '';
        }

        return $contents;
    }
}