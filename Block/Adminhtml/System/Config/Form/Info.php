<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Olegnax\Core\Block\Adminhtml\System\Config\Form;


use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Olegnax\Core\Block\Adminhtml\NoticeContent;
use Olegnax\Core\Helper\ModuleInfo;

class Info extends Fieldset
{
    /**
     * @var ModuleInfo
     */
    protected $moduleInfo;
    /**
     * @var NoticeContent
     */
    protected $content;

    /**
     * Info constructor.
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param NoticeContent $content
     * @param ModuleInfo $moduleInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        NoticeContent $content,
        ModuleInfo $moduleInfo,
        array $data = []
    ) {
        $this->content = $content;
        $this->moduleInfo = $moduleInfo;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_toTPL();
        $html .= $this->getContent();

        if ($element->getIsNested()) {
            $html = '<tr class="nested"><td colspan="4">' . $html . '</td></tr>';
        }
        return $html;
    }

    /**
     * Template to string
     *
     * @param array $data
     * @return string
     */
    public function _toTPL($data = [])
    {
        ob_start();
        $this->toTPL(array_replace($this->getModuleInfo(), $data));
        return (string)ob_get_clean();
    }

    /**
     * Template
     *
     * @param array $data
     */
    public function toTPL($data = [])
    {
        $rightUrls = [
            'docs' => __('User Guide'),
        ];
        $rightBlock = '';
        foreach ($rightUrls as $key => $label) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $rightBlock .= '<div class="ox-info-block__' . $key . '"><a href="' . $this->escapeUrl($data[$key]) . '" target="_blank">' . $label . '</a></div>';
            }
        }
        $rightBlock .= $this->rightCustomBlocks($data);
        ?>
		<div class="ox-info-block">
			<a href="https://olegnax.com/" target="_blank" class="ox-info-block__logo"></a>
			<div class="ox-info-block__title"><?= $this->escapeHtml($data['title']); ?></div>
            <?= $this->leftCustomBlocks($data); ?>
            <?php if ($data['update_status']): ?>
				<div class="ox-info-block__version expired">
					<div class="ox-module-version">v<?= $this->escapeHtml($data['setup_version']); ?> <span class="ox-server-version">v<?= $this->escapeHtml($data['server_version']) ?></span><?php if (isset($data['url_changelog']) && !empty($data['url_changelog'])): ?> <a href="<?= $this->escapeUrl($data['url_changelog']) ?>" target="_blank"><?= __("What's New") ?></a></div><?php endif; ?>
				</div>
			<?php else: ?>
				<div class="ox-info-block__version">
					<div class="ox-module-version">v<?= $this->escapeHtml($data['setup_version']); ?></div>
				</div>
            <?php endif; ?>
            <?php if (!empty($rightBlock)): ?><div class="ox-info-block__right"><?= $rightBlock; ?></div><?php endif; ?>
		</div>
        <?php
    }

    /**
     * Right Custom Blocks
     *
     * @param array $data
     * @return string
     */
    protected function rightCustomBlocks($data = [])
    {
        return '';
    }

    /**
     * Left Custom Blocks
     *
     * @param array $data
     * @return string
     */
    protected function leftCustomBlocks($data = [])
    {
        return '';
    }

    /**
     * Get Module info
     *
     * @return array
     */
    protected function getModuleInfo()
    {
        return $this->moduleInfo->getModuleInfo($this->getModuleName());
    }

    /**
     * Show notice
     *
     * @return string
     */
    protected function getContent()
    {
        $this->content->setData('location', $this->getModuleName());
        return $this->content->toHtml();
    }
}