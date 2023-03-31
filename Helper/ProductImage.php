<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2023 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Helper;

use Exception;
use Magento\Catalog\Block\Product\Image as CatalogBlockProductImage;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
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
    /**
     * @var string
     */
    protected $_magentoVersion;

    /**
     * ProductImage constructor.
     *
     * @param Context $context
     * @param Image $imageHelper
     * @param ConfigInterface $viewConfig
     * @param ParamsBuilder $imageParamsBuilder
     */
    public function __construct(
        Context $context,
        Image $imageHelper,
        ConfigInterface $viewConfig,
        ParamsBuilder $imageParamsBuilder
    ) {
        $this->imageHelper = $imageHelper;
        $this->viewConfig = $viewConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        parent::__construct($context);
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param string $imageIdHover
     * @param string $template
     * @param array $attributes
     * @param array $properties
     *
     * @return mixed
     */
    public function getImageHover(
        Product $product,
        $imageId,
        $imageIdHover,
        $template = self::HOVER_TEMPLATE,
        array $attributes = [],
        $properties = []
    ) {
        if (!$this->hasHoverImage($product, $imageId, $imageIdHover)) {
            return $this->getImage($product, $imageId, self::TEMPLATE, $attributes, $properties);
        }

        $image = $this->_getImage($product, $imageId, $properties)->getUrl();
        $imageMiscParams = $this->getImageParams($imageId);
        $image_hoverMiscParams = $this->getImageParams($imageIdHover);

        $image_hover = $this->resizeImage(
            $product,
            $imageIdHover,
            [
                $imageMiscParams['image_width'],
                $imageMiscParams['image_height'],
            ],
            $properties
        )->getUrl();

        $data = [
            'data' => [
                'template' => $template,
                'product_id' => $product->getId(),
                'product' => $product,
                'image_id' => $imageId,
                'image_hover_id' => $imageIdHover,
                'image_url' => $image,
                'image_hover_url' => $image_hover,
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'label_hover' => $this->getLabel($product, $image_hoverMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'class' => $this->getClass($attributes),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param string $imageId_hover
     *
     * @return bool
     */
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
     *
     * @return array
     */
    public function getImageParams($imageId)
    {
        $viewImageConfig = $this->getConfigView()->getMediaAttributes(
            'Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        if (empty($imageMiscParams)) {
            $imageMiscParams = $this->getDefaultParams();
            $this->_logger->critical(sprintf('No options found for "%s" images!', $imageId));
        }

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

    /**
     * @return array
     */
    protected function getDefaultParams()
    {
        return [
            "image_type" => "small_image",
            "image_height" => 240,
            "image_width" => 240,
            "background" => [255, 255, 255],
            "quality" => 80,
            "keep_aspect_ratio" => true,
            "keep_frame" => true,
            "keep_transparency" => true,
            "constrain_only" => true,
        ];
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param string $template
     * @param array $attributes
     * @param array $properties
     *
     * @return mixed
     */
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
                'product' => $product,
                'image_id' => $imageId,
                'image_url' => $image->getUrl(),
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'class' => $this->getClass($attributes),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param array $properties
     *
     * @return Image
     */
    private function _getImage(Product $product, $imageId, $properties = [])
    {
        return $this->imageHelper->init($product, $imageId, $properties);
    }

    /**
     * @param Product $product
     *
     * @param string $imageType
     *
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
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
     *
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
     * Retrieve image class for HTML element
     *
     * @param array $attributes
     *
     * @return string
     */
    private function getClass(array $attributes): string
    {
        return $attributes['class'] ?? 'product-image-photo';
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @param array $attributes
     *
     * @return string|array
     */
    private function getStringCustomAttributes(array $attributes)
    {
        if (!$this->compareVersion()) {
            return $attributes;
        }
        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * @return string
     */
    private function getMagentoVersion()
    {
        if (!$this->_magentoVersion) {
            $this->_magentoVersion = ObjectManager::getInstance()->get(ProductMetadataInterface::class)->getVersion();
        }

        return $this->_magentoVersion;
    }

    /**
     * @param $version
     *
     * @return bool
     */
    protected function compareVersion($version = '2.4.0')
    {
        return version_compare($this->getMagentoVersion(), $version, '<');
    }

    /**
     * @param array $data
     *
     * @return CatalogBlockProductImage
     */
    private function _createTemplate($data = [])
    {
        return ObjectManager::getInstance()->create(CatalogBlockProductImage::class, $data);
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param array|int $size
     * @param array $properties
     *
     * @return Image
     */
    public function resizeImage(Product $product, $imageId, $size, $properties = [])
    {
        $size = $this->prepareSize($size);
        $image = $this->_getImage($product, $imageId, $properties);
        $image->resize($size[0], $size[1]);

        return $image;
    }

    /**
     * @param array|int $size
     *
     * @return array
     */
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

    /**
     * @param Product $product
     * @param string $imageId
     * @param string $imageId_hover
     * @param array|int $size
     * @param string $template
     * @param array $attributes
     * @param array $properties
     *
     * @return CatalogBlockProductImage|mixed
     */
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
            foreach (['image_width', 'image_height'] as $key => $value) {
                if (!isset($size[$key]) || empty($size[$key])) {
                    $size[$key] = $imageMiscParams[$value];
                }
            }
        }

        $image = $this->resizeImage($product, $imageId, $size, $properties);
        try {
            [
                $imageMiscParams['image_width'],
                $imageMiscParams['image_height']
            ] = $image->getResizedImageInfo();
        } catch (Exception $e) {
            $this->_logger->error("OX Product Image: " . $e->getMessage());
            $imageMiscParams['image_width'] = $imageMiscParams['image_height'] = 1;
        }
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
                'product' => $product,
                'image_id' => $imageId,
                'image_hover_id' => $imageId_hover,
                'image_url' => $image,
                'image_hover_url' => $image_hover,
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'label_hover' => $this->getLabel($product, $image_hoverMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'class' => $this->getClass($attributes),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param array|int $size
     * @param string $template
     * @param array $attributes
     * @param array $properties
     *
     * @return CatalogBlockProductImage|mixed
     */
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
            foreach (['image_width', 'image_height'] as $key => $value) {
                if (!isset($size[$key]) || empty($size[$key])) {
                    $size[$key] = $imageMiscParams[$value];
                }
            }
        }
        $image = $this->resizeImage($product, $imageId, $size, $properties);
        $imageMiscParams = $this->getImageParams($imageId);
        try {
            [
                $imageMiscParams['image_width'],
                $imageMiscParams['image_height']
            ] = $image->getResizedImageInfo();
        } catch (Exception $e) {
            $this->_logger->error("OX Product Image: " . $e->getMessage());
            $imageMiscParams['image_width'] = $imageMiscParams['image_height'] = 1;
        }

        $data = [
            'data' => [
                'template' => $template,
                'product_id' => $product->getId(),
                'product' => $product,
                'image_id' => $imageId,
                'image_url' => $image->getUrl(),
                'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                'width' => $imageMiscParams['image_width'],
                'height' => $imageMiscParams['image_height'],
                'ratio' => $this->getRatio($imageMiscParams['image_width'], $imageMiscParams['image_height']),
                'class' => $this->getClass($attributes),
                'custom_attributes' => $this->getStringCustomAttributes($attributes),
            ],
        ];

        return $this->_createTemplate($data);
    }

    /**
     * @param Product $product
     * @param string $image
     * @param array|int $size
     * @param array $properties
     *
     * @return string
     */
    public function getUrlResizedImage(Product $product, $image, $size, $properties = [])
    {
        $image = $this->resizeImage($product, $image, $size, $properties);
        return $image->getUrl();
    }
}
