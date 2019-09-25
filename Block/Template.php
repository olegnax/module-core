<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block;

use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Core\Helper\Helper as HelperHelper;
use Olegnax\Core\Helper\ProductImage as HelperProductImage;

abstract class Template extends SimpleTemplate
{
    const CHILD_TEMPLATE = ChildTemplate::class;


    // Helper Product Image //

    public function getImage(
        $product,
        $imageId,
        $template = HelperProductImage::TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        return $this->getHelperProductImage()->getImage($product, $imageId, $template, $attributes, $properties);
    }

    public function getHelperProductImage()
    {
        return $this->_loadObject(HelperProductImage::class);
    }

    public function getImageHover(
        $product,
        $imageId,
        $imageId_hover,
        $template = HelperProductImage::HOVER_TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        return $this->getHelperProductImage()->getImageHover($product, $imageId, $imageId_hover, $template, $attributes,
            $properties);
    }

    public function getResizedImage(
        $product,
        $imageId,
        $size,
        $template = HelperProductImage::TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        return $this->getHelperProductImage()->getResizedImage($product, $imageId, $size, $template, $attributes,
            $properties);
    }

    public function getResizedImageHover(
        $product,
        $imageId,
        $imageId_hover,
        $size,
        $template = HelperProductImage::HOVER_TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        return $this->getHelperProductImage()->getResizedImageHover($product, $imageId, $imageId_hover, $size,
            $template, $attributes, $properties);
    }

    public function hasHoverImage($product, $imageId, $imageId_hover)
    {
        return $this->getHelperProductImage()->hasHoverImage($product, $imageId, $imageId_hover);
    }

    public function getUrlResizedImage($product, $image, $size, $properties = [])
    {
        return $this->getHelperProductImage()->getUrlResizedImage($product, $image, $size, $properties);
    }

    public function isLoggedIn()
    {
        return $this->getHelper()->isLoggedIn();
    }

    // Helper Helper //

    public function getHelper()
    {
        return $this->_loadObject(HelperHelper::class);
    }

    public function getWishlistCount()
    {
        return $this->getHelper()->getWishlistCount();
    }

    public function getCompareListUrl()
    {
        return $this->getHelper()->getCompareListUrl();
    }

    function getCompareListCount()
    {
        return $this->getHelper()->getCompareListCount();
    }

    public function isAutocompleteDisabled()
    {
        return (bool)!$this->getSystemValue(Form::XML_PATH_ENABLE_AUTOCOMPLETE);
    }

    public function getBlockTemplateProcessor($content = '')
    {
        return $this->getHelper()->getBlockTemplateProcessor($content);
    }

    public function getLayoutTemplateHtml($option_path = '', $fileName = '', $arguments = [])
    {
        $value = $this->getConfig($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getLayoutTemplateHtmlbyValue($value, $fileName, $arguments);
        }
        return '';
    }

    public function getLayoutTemplateHtmlbyValue($value = null, $fileName = null, $arguments = [], $separator = '/')
    {
        $block = $this;
        $_fileName = '';
        if (empty($fileName)) {
            $blockTemplate = $block->getTemplate();
            if (preg_match('/(\.[^\.]+?)$/', $blockTemplate)) {
                $fileName = preg_replace('/(\.[^\.]+?)$/', '%s%s$1', $blockTemplate);
            } else {
                $fileName .= '%s%s';
            }
        } else {
            $_fileName = $fileName;
        }
        $blockName = $separator . $block->getNameInLayout() . $separator . $_fileName . $separator . $value;
        $fileName = sprintf($fileName, $separator, $value);
        while ($block->getLayout()->getBlock($blockName)) {
            $blockName .= '_0';
        }
        $_block = $block->getLayout()->createBlock(static::CHILD_TEMPLATE, $blockName);
        $block->setChild($_block->getNameInLayout(), $_block);
        if (!empty($arguments) && is_array($arguments)) {
            foreach ($arguments as $key => $value) {
                $_block->addData($key, $value);
            }
        }
        $content = $_block->setTemplate($fileName)->toHtml();

        return $content;
    }

    public function getLayoutBlockHtml($option_path = '', $blockName = null)
    {
        $value = $this->getConfig($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getBlockValueHtmlby($value, $blockName);
        }
        return '';
    }

    public function getLayoutBlockHtmlbyValue($value = null, $blockName = null, $separator = '/')
    {
        $block = $this;
        if (empty($blockName)) {
            $blockName = $block->getNameInLayout();
        }
        $blockName = $blockName . $separator . $value;
        $_block = $block->getLayout()->getBlock($blockName);
        $content = '';
        if ($_block) {
            $block->setChild($blockName, $_block);
            $content = $_block->toHtml();

            return $content;
        }

        return $content;
    }

    public function getBaseUrl()
    {
        return $this->getHelper()->getBaseUrl();
    }

}
