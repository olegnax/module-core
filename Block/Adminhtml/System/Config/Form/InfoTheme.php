<?php


namespace Olegnax\Core\Block\Adminhtml\System\Config\Form;


use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\Js;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Core\Block\Adminhtml\NoticeContent;
use Olegnax\Core\Helper\ModuleInfo;

class InfoTheme extends Info
{
    /**
     * @var mixed
     */
    protected $_helper;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        NoticeContent $content,
        ModuleInfo $moduleInfo,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $content, $moduleInfo, $data);
    }

    protected function rightCustomBlocks($data = [])
    {
        if (!$this->moduleInfo->isActive('Olegnax_Athlete2')) {
            return '';
        }
        $this->_helper = ObjectManager::getInstance()->get(Helper::class);
        $code = $this->_helper->getSystemDefaultValue('athlete2_license/general/code');
        $license = $this->_helper->get();
        $status = !empty($license)
            && isset($license->data->the_key)
            && $license->data->the_key == $code
            && $license->data->status == "active";
        $supportExpired = isset($license->notices->support);
        $notice = [];
        if ($status) {
            $notice[] = '<div class="ox-info-block__support support-' . ($supportExpired ? 'expired' : 'active') . '"><div class="wrapper"><span class="label">' . __('Support') . '</span>';
            if ($supportExpired) {
                $notice[] = '<a href="https://themeforest.net/item/athlete2-strong-magento-2-theme/23693737" target="_blank">' . __('Renew') . '</a>';
            } else {
                $notice[] = '<a href="https://olegnax.com/help" target="_blank">' . __('Active') . '</a>';
            }
            $notice[] = '</div></div>';
        }

        return implode($notice);
    }
}