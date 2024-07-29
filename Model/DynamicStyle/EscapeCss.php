<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2024 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model\DynamicStyle;

class EscapeCss
{
    public function escapeCss($css){
        if (!empty($css)) {
            $css = preg_replace('/[\r\n\t]/', ' ', $css);
            $css = preg_replace('/[\r\n\t ]{2,}/', ' ', $css);
            $css = preg_replace('/\s+(\:|\;|\{|\})\s+/', '\1', $css);
            $css = preg_replace('/<[^<>]+>(.*?)<\/[^<>]+>/m', '/* Forbidden tags in styles */', $css);
            return $css;
        }
        return '';
    }
}