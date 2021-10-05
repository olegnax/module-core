<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Model;

class Spacer extends \Magento\Config\Block\System\Config\Form\Field {
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

	protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
	{
		return '<tr id="row_' . $element->getHtmlId() . '"><td></td><td colspan="2"><hr class="ox-settings-spacer"></td></tr>';
	}

}