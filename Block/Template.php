<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2026 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block;

use Olegnax\Core\Helper\Helper as HelperHelper;
use Olegnax\Core\Helper\ProductImage as HelperProductImage;

abstract class Template extends SimpleTemplate
{
    const CHILD_TEMPLATE = ChildTemplate::class;

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
	/**
	 * @deprecated version 1.4.0.0
	 */
    public function isLoggedIn()
    {
        return $this->getHelper()->isLoggedIn();
    }

	/**
	 * @deprecated version 1.4.0.0
	 */
    public function getHelper()
    {
        return $this->_loadObject(HelperHelper::class);
    }
	/**
	 * @deprecated version 1.4.0.0 
     * use Customer view model instead
	 */
    public function getWishlistCount()
    {
        return $this->getHelper()->getWishlistCount();
    }
	/**
	 * @deprecated version 1.4.0.0 
     * use Customer view model instead
	 */
    public function getCompareListUrl()
    {
        return $this->getHelper()->getCompareListUrl();
    }
	/**
	 * @deprecated version 1.4.0.0 
     * use Customer view model instead
	 */
    function getCompareListCount()
    {
        return $this->getHelper()->getCompareListCount();
    }
	/**
	 * @deprecated version 1.4.0.0
     * use CmsProcessor view model instead
	 */
    public function getBlockTemplateProcessor($content = '')
    {
        if (empty($content) || !is_string($content)) {
            $content = '';
        }
        return $this->getHelper()->getBlockTemplateProcessor($content);
    }
    
	/**
	 * @deprecated version 1.4.0.0
     * use UrlProvider view model instead
	 */
    public function getBaseUrl()
    {
        return $this->getHelper()->getBaseUrl();
    }

}
