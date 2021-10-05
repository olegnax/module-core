<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
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

    public function getBaseUrl()
    {
        return $this->getHelper()->getBaseUrl();
    }

}
