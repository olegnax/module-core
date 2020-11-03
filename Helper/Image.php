<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\Core\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Factory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Description of Image
 *
 * @author Master
 */
class Image extends AbstractHelper
{
    const BASE_TEMPLATE_FILE_NAME = '{dirname}/{destinationSubdir}/{filename}_{width}_{height}.{extension}';

    protected $_baseFile;
    /**
     * @var int
     */
    protected $_width;
    /**
     * @var int
     */
    protected $_height;
    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 90;
    /**
     * @var bool
     */
    protected $_keepAspectRatio = true;
    /**
     * @var bool
     */
    protected $_keepFrame = true;
    /**
     * @var bool
     */
    protected $_keepTransparency = true;
    /**
     * @var bool
     */
    protected $_constrainOnly = true;
    /**
     * @var bool
     */
    protected $_cropOnly = false;

    protected $_resizeTemplate = '';
    /**
     * @var int[]
     */
    protected $_backgroundColor = [255, 255, 255];
    /**
     * @var \Magento\Framework\Image
     */
    protected $_processor;
    /**
     * @var string
     */
    protected $_destinationSubdir;
    /**
     * @var WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var Factory
     */
    protected $_imageFactory;
    /**
     * @var array
     */
    protected $attributes;
    /**
     * @var File
     */
    protected $_file;
    /**
     * @var string
     */
    private $__file;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Factory $imageFactory,
        File $file,
        StoreManagerInterface $storeManager
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_file = $file;
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    public function adaptiveResize($size, $fileTemplate = null)
    {
        if (!empty($fileTemplate)) {
            $this->setFileTemplate($fileTemplate);
        }
        try {
            [$width, $height] = $this->prepareSize($size);
            $this->setWidth($width);
            $this->setHeight($height);
            if ($this->_keepAspectRatio && $this->_cropOnly) {
                $this->crop();
            }
            $this->resize();
        } catch (Exception $e) {
            $this->_logger->error("Image Resize: " . $e, [$this->_baseFile]);
        }

        return $this;
    }

    public function setFileTemplate($fileTemplate)
    {
        $this->_resizeTemplate = $fileTemplate;
        return $this;
    }

    private function prepareSize($size)
    {
        if (is_array($size) && 1 >= count($size)) {
            $size = array_shift($size);
        }
        if (!is_array($size)) {
            $size = [$size, $size];
        }
        $size = array_map('intval', $size);
        $size = array_map('abs', $size);
        return $size;
    }

    public function crop()
    {
        if ($this->_width == 0 && $this->_height == 0 && !$this->getBaseFile()) {
            return $this;
        }
        if ($this->_fileExists($this->getResizedFile())) {
            return $this;
        }

        $_imageSrcWidth = $this->getImageProcessor()->getOriginalWidth();
        $_imageSrcHeight = $this->getImageProcessor()->getOriginalHeight();
        $_imageSrcRatio = $_imageSrcWidth / $_imageSrcHeight;
        $_ratio = $this->_width / $this->_height;

        if ($_imageSrcRatio != $_ratio) {
            if (1 > $_imageSrcRatio) {
                $newHeight = round($_imageSrcWidth / $_ratio);
                $deltHeight = $_imageSrcHeight - $newHeight;

                $_deltHeight = floor($deltHeight / 2);
                $__deltHeight = $deltHeight % 2;

                $this->getImageProcessor()->crop($_deltHeight + $__deltHeight, 0, 0, $_deltHeight);
            } elseif (1 < $_imageSrcRatio) {
                $newWidth = round($_imageSrcHeight * $_ratio);
                $deltWidth = $_imageSrcWidth - $newWidth;

                $_deltWidth = floor($deltWidth / 2);
                $__deltWidth = $deltWidth % 2;

                $this->getImageProcessor()->crop(0, $_deltWidth + $__deltWidth, $_deltWidth, 0);
            }
        }

        return $this;
    }

    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    protected function _fileExists($filename)
    {
        return $this->_file->fileExists($this->_mediaDirectory->getAbsolutePath($filename));
    }

    /**
     * @return string
     */
    private function getResizedFile()
    {
        $path_data = pathinfo($this->getBaseFile());

        if (is_array($path_data) && !empty($path_data)) {
            $filename = str_replace(
                [
                    '{dirname}',
                    '{basename}',
                    '{extension}',
                    '{filename}',
                    '{width}',
                    '{height}',
                    '{originalWidth}',
                    '{originalHeight}',
                    '{destinationSubdir}',
                    '{keepFrame}',
                    '{constrainOnly}',
                    '{keepAspectRatio}',
                    '{crop}',
                    '{keepTransparency}',
                    '{quality}',
                ],
                [
                    $path_data['dirname'],
                    $path_data['basename'],
                    $path_data['extension'],
                    $path_data['filename'],
                    $this->getWidth(),
                    $this->getHeight(),
                    $this->getOriginalWidth(),
                    $this->getOriginalHeight(),
                    $this->getDestinationSubdir(),
                    $this->getQuality(),
                    $this->_keepFrame,
                    $this->_constrainOnly,
                    $this->_keepAspectRatio,
                    $this->_cropOnly,
                    $this->_keepTransparency,
                ],
                $this->_resizeTemplate
            );

            return $filename;
        }
        return '';
    }

    /**
     * @return string
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return Image
     */
    public function setBaseFile($file)
    {
        if ($this->_fileExists($file)) {
            $this->_baseFile = $file;
        } else {
            $this->_logger->error(__('The file does not exist: ') . $file);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width ?: $this->getOriginalWidth();
    }

    /**
     * @param int $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->_width = abs((int)$width);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->getImageProcessor()->getOriginalWidth();
    }

    /**
     * @return \Magento\Framework\Image
     */
    public function getImageProcessor()
    {
        if (!$this->_processor) {
            $filename = $this->getBaseFile() ? $this->_mediaDirectory->getAbsolutePath($this->getBaseFile()) : null;
            $this->_processor = $this->_imageFactory->create($filename);
        }
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        return $this->_processor;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height ?: $this->getOriginalHeight();
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->_height = abs((int)$height);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->getImageProcessor()->getOriginalHeight();
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * Get image quality
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->_quality;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $quality = abs((int)$quality);
        if (0 < $quality) {
            $this->_quality = $quality;
        }
        return $this;
    }

    /**
     * @return $this
     * @see \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    public function resize()
    {
        if ($this->_width == 0 && $this->_height == 0 && !$this->getBaseFile()) {
            return $this;
        }

        $resizedFile = $this->getResizedFile();
        if ($resizedFile && $this->_fileExists($resizedFile)) {
            return $this;
        }

        $new_filename = $this->_mediaDirectory->getAbsolutePath($resizedFile);

        $this->getImageProcessor()->resize($this->_width, $this->_height);

        $this->getImageProcessor()->save($new_filename);

        return $this;
    }

    /**
     * @param \Magento\Framework\Image $processor
     * @return $this
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getUrl()
    {
        $media_url = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        if (!$this->getBaseFile()) {
            return $media_url . $this->__file;
        }
        
        return $media_url . $this->getResizedFile();
    }

    /**
     * @param string $size
     * @return $this
     */
    public function setSize($size)
    {
        // determine width and height from string
        $width = $height = 0;

        if (is_numeric($size)) {
            $width = $height = $size;
        } elseif (is_array($size)) {
            if (1 >= count($size)) {
                [$width, $height] = $size;
            } else {
                $width = $height = array_shift($size);
            }
        } elseif (is_string($size)) {
            [$width, $height] = explode('x', strtolower($size), 2);
        }

        foreach (['width', 'height'] as $wh) {
            ${$wh} = (int)${$wh};
            if (empty(${$wh})) {
                ${$wh} = null;
            }
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    /**
     * @param $file
     * @throws Exception
     */
    public function removeResized($file)
    {
        if (!empty($file)) {
            $this->init($file);
        }
        $file = $this->getBaseFile();

        if (!empty($file)) {
            $new_filename = pathinfo($file);
            $filename = implode(
                '_',
                [
                    $new_filename['filename'],
                    '*',
                ]
            );
            $new_filename['filename'] = $filename . '.' . $new_filename['extension'];
            $pattern = implode(
                '/',
                [
                    $this->getDestinationSubdir(),
                    $new_filename['dirname'],
                    $new_filename['filename'],
                ]
            );
            $pattern = $this->_mediaDirectory->getAbsolutePath($pattern);
            $files = Glob::glob($pattern);
            if (!empty($files)) {
                foreach ($files as $_file) {
                    if ($this->_fileExists($_file)) {
                        $this->_file->rm($_file);
                    }
                }
            }
        }
    }

    /**
     * @param $file
     * @param array $attributes
     * @return $this
     * @throws Exception
     */
    public function init($file, $attributes = [])
    {
        $this->_reset();
        $this->__file = $file;
        $this->setBaseFile($file);
        $this->attributes = $attributes;
        $this->setImageProperties();

        return $this;
    }

    /**
     * Resset all attributes
     */
    protected function _reset()
    {
        $this->_processor = null;
        $this->_baseFile = null;
        $this->setQuality(90);
        $this->setFileTemplate(static::BASE_TEMPLATE_FILE_NAME);
        $this->setBackgroundColor([255, 255, 255]);
        $this->setConstrainOnly(true);
        $this->setDestinationSubdir('resized');
        $this->setKeepAspectRatio(true);
        $this->setKeepFrame(true);
        $this->setKeepTransparency(true);
        $this->setWidth(null);
        $this->setHeight(null);
    }

    /**
     * @param int[] $rgbArray
     * @return $this
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = $flag && $flag !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * @param bool $keep
     * @return $this
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = $keep && $keep !== 'false';
        return $this;
    }

    /**
     * Set image properties
     *
     * @return $this
     */
    protected function setImageProperties()
    {
        // Set 'keep frame' flag
        $frame = $this->getAttribute('frame');
        if (!empty($frame)) {
            $this->setKeepFrame($frame);
        }
        // Set 'quality'
        $quality = $this->getAttribute('quality');
        if (!empty($quality)) {
            $this->setQuality($quality);
        }

        // Set 'constrain only' flag
        $constrain = $this->getAttribute('constrain');
        if (!empty($constrain)) {
            $this->setConstrainOnly($constrain);
        }

        // Set 'keep aspect ratio' flag
        $aspectRatio = $this->getAttribute('aspect_ratio');
        if (!empty($aspectRatio)) {
            $this->setKeepAspectRatio($aspectRatio);
        }

        // Set 'keep $crop' flag
        $crop = $this->getAttribute('crop');
        if (!empty($crop)) {
            $this->seCropOnly($crop);
        }

        // Set 'transparency' flag
        $transparency = $this->getAttribute('transparency');
        if (!empty($transparency)) {
            $this->setKeepTransparency($transparency);
        }

        // Set background color
        $background = (array)$this->getAttribute('background');
        if (!empty($background)) {
            $this->setBackgroundColor($background);
        }

        $fileTemplate = $this->getAttribute('fileTemplate');
        $this->setFileTemplate(!empty($fileTemplate) ? $fileTemplate : static::BASE_TEMPLATE_FILE_NAME);

        return $this;
    }

    /**
     * Retrieve image attribute
     *
     * @param string $name
     * @return string
     */
    protected function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function seCropOnly($flag)
    {
        $this->_cropOnly = $flag && $flag !== 'false';
        return $this;
    }

}
