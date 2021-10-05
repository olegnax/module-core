<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 *
 * @noinspection PhpMissingFieldTypeInspection
 */
declare(strict_types=1);

namespace Olegnax\Core\Plugin\Backend\Magento\Cms\Model\Wysiwyg;

use Closure;
use Magento\Cms\Model\Wysiwyg\Validator as CmsValidator;
use Magento\Framework\Registry;
use Olegnax\Core\Helper\Helper;

class Validator
{
    public const XML_PATH_DISABLE = 'athlete2_settings/general/cms_validator_disable';
    public const VARIABLE_TO_DISABLE = 'ox_core_cms_validator_disable';
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Validator constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper,
        Registry $coreRegistry
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param CmsValidator $subject
     * @param Closure $proceed
     * @param string $content
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function aroundValidate(
        CmsValidator $subject,
        Closure $proceed,
        string $content
    ) {
        if (
            !$this->helper->getConfig(static::XML_PATH_DISABLE)
            && !$this->coreRegistry->registry(static::VARIABLE_TO_DISABLE)
        ) {
            $proceed($content);
        }
    }
}
