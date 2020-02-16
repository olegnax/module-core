<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Helper;

use Magento\Catalog\Block\Product\Image as CatalogBlockProductImage;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\View;
use Magento\Framework\View\ConfigInterface;

/**
 * Description of Image
 *
 * @author Master
 */
class ProductImage extends AbstractHelper
{

    const TEMPLATE = 'Magento_Catalog::product/image_with_borders.phtml';
    const HOVER_TEMPLATE = 'Magento_Catalog::product/hover_image_with_borders.phtml';
    public $_objectManager;
    /**
     * @var Image
     */
    protected $imageHelper;
    /**
     * @var ConfigInterface
     */
    protected $viewConfig;
    /**
     * @var View
     */
    protected $configView;
    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    public function __construct(
        Context $context,
        Image $imageHelper,
        ConfigInterface $viewConfig,
        ParamsBuilder $imageParamsBuilder
    ) {

        $this->_objectManager = ObjectManager::getInstance();
        $this->imageHelper = $imageHelper;
        $this->viewConfig = $viewConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        parent::__construct($context);
    }

    public function getImageHover(
        Product $product,
        $imageId,
        $imageId_hover,
        $template = self::HOVER_TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        if (!$this->hasHoverImage($product, $imageId, $imageId_hover)) {
            return $this->getImage($product, $imageId, self::TEMPLATE, $attributes, $properties);
        }

        $image = $this->_getImage($product, $imageId, $properties)->getUrl();
        $imageMiscParams = $this->getImageParams($imageId);
        $image_hoverMiscParams = $this->getImageParams($imageId_hover);

        $image_hover = $this->resizeImage($product, $imageId_hover,
            [$imageMiscParams['image_width'], $imageMiscParams['image_height']], $properties)->getUrl();

        $data = [
            'data' => [
                'template' => $template,
                'product_id' => $product->getId(),
                'image_id' => $imageId,
                'image_hover_id' => $imageId_hover,
                'image_url' => $image,
                'image_hover_url' => $image_hover,
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'label_hover' => $this->getLabel($product, $image_hoverMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    public function hasHoverImage(Product $product, $imageId, $imageId_hover)
    {
        if ($imageId != $imageId_hover) {
            $_imageId = $this->getImageParams($imageId);
            $_imageId_hover = $this->getImageParams($imageId_hover);
            if ($_imageId['image_type'] !== $_imageId_hover['image_type']) {
                $image = $product->getData($_imageId['image_type']);
                $image_hover = $product->getData($_imageId_hover['image_type']);
                return $image && $image_hover && 'no_selection' !== $image_hover && $image !== $image_hover;
            }
        }

        return false;
    }

    /**
     * @param int $imageId
     * @return array
     */
    protected function getImageParams($imageId)
    {
        $viewImageConfig = $this->getConfigView()->getMediaAttributes('Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE, $imageId);

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);

        return $imageMiscParams;
    }

    /**
     * Retrieve config view
     *
     * @return View
     */
    protected function getConfigView()
    {
        if (!$this->configView) {
            $this->configView = $this->viewConfig->getViewConfig();
        }
        return $this->configView;
    }

    public function getImage(
        Product $product,
        $imageId,
        $template = self::TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        $image = $this->_getImage($product, $imageId, $properties);
        $imageMiscParams = $this->getImageParams($imageId);

        $data = [
            'data' => [
                'template' => $template,
                'product_id' => $product->getId(),
                'image_id' => $imageId,
                'image_url' => $image->getUrl(),
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    private function _getImage(Product $product, $imageId, $properties = [])
    {
        return $this->imageHelper->init($product, $imageId, $properties);
    }

    /**
     * @param Product $product
     *
     * @param string $imageType
     * @return string
     */
    private function getLabel(Product $product, $imageType): string
    {
        $label = "";
        if (!empty($imageType)) {
            $label = $product->getData($imageType . '_' . 'label');
        }
        if (empty($label)) {
            $label = $product->getName();
        }
        return (string)$label;
    }

    /**
     * Calculate image ratio
     *
     * @param $width
     * @param $height
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }
        return 1.0;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getStringCustomAttributes(array $attributes): string
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    private function _createTemplate($data = [])
    {
        return $this->_objectManager->create(CatalogBlockProductImage::class, $data);
    }

    public function resizeImage(Product $product, $imageId, $size, $properties = [])
    {
        $size = $this->prepareSize($size);
        $image = $this->_getImage($product, $imageId, $properties);
        $image->resize($size[0], $size[1]);

        return $image;
    }

    private function prepareSize($size)
    {
        if (is_array($size) && 1 >= count($size)) {
            $size = array_shift($size);
        }
        if (!is_array($size)) {
            $size = [$size, $size];
        }
        $size = array_map('floatval', $size);
        $size = array_map('abs', $size);
        return $size;
    }

    public function getResizedImageHover(
        Product $product,
        $imageId,
        $imageId_hover,
        $size,
        $template = self::HOVER_TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        if (!is_array($template)) {
            $template = [
                $template,
                self::TEMPLATE,
            ];
        } else {
            foreach ([self::HOVER_TEMPLATE, self::TEMPLATE] as $key => $value) {
                if (!isset($template[$key]) || empty($template[$key])) {
                    $template[$key] = $value;
                }
            }
        }
        if (!$this->hasHoverImage($product, $imageId, $imageId_hover)) {
            return $this->getResizedImage($product, $imageId, $size, $template[1], $attributes, $properties);
        }
        $imageMiscParams = $this->getImageParams($imageId);
        if (empty($size)) {
            $size = [$imageMiscParams['image_width'], $imageMiscParams['image_height']];
        } elseif (is_array($size)) {
            foreach (['image_width', 'image_width'] as $key => $value) {
                if (!isset($size[$key]) || empty($size[$key])) {
                    $size[$key] = $imageMiscParams[$value];
                }
            }
        }

        $image = $this->resizeImage($product, $imageId, $size, $properties);
        list($imageMiscParams['image_width'], $imageMiscParams['image_height']) = $image->getResizedImageInfo();
        $image = $image->getUrl();
        $image_hover = $this->resizeImage($product, $imageId_hover, $size, $properties)->getUrl();
        $image_hoverMiscParams = $this->getImageParams($imageId_hover);

        if (array_key_exists('class', $attributes)) {
            unset($attributes['class']);
        }

        $data = [
            'data' => [
                'template' => $template[0],
                'product_id' => $product->getId(),
                'image_id' => $imageId,
                'image_hover_id' => $imageId_hover,
                'image_url' => $image,
                'image_hover_url' => $image_hover,
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'label_hover' => $this->getLabel($product, $image_hoverMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    public function getResizedImage(
        Product $product,
        $imageId,
        $size,
        $template = self::TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        $imageMiscParams = $this->getImageParams($imageId);
        if (empty($size)) {
            return $this->getImage($product, $imageId, $template, $attributes, $properties);
        }
        if (is_array($size)) {
            foreach (['image_width', 'image_width'] as $key => $value) {
                if (!isset($size[$key]) || empty($size[$key])) {
                    $size[$key] = $imageMiscParams[$value];
                }
            }
        }
        $image = $this->resizeImage($product, $imageId, $size, $properties);
        $imageMiscParams = $this->getImageParams($imageId);
        list($imageMiscParams['image_width'], $imageMiscParams['image_height']) = $image->getResizedImageInfo();

        $data = [
            'data' => [
                'template' => $template,
                'product_id' => $product->getId(),
                'image_id' => $imageId,
                'image_url' => $image->getUrl(),
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    public function getUrlResizedImage(Product $product, $image, $size, $properties = [])
    {
        $image = $this->resizeImage($product, $image, $size, $properties);
        return $image->getUrl();
    }

}
