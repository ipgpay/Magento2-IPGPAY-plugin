<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\Gateway\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

/**
 * Class Redirect
 * @package IPGPAY\Gateway\Block
 */
class Redirect extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Redirect constructor.
     * @param Template\Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getPaymentFormFields()
    {
        $fields = '';
        foreach ($this->_coreRegistry->registry('ipgpay_payment_form_data') as $key => $value) {
            $fields .= '<input type="hidden" name="' . $this->escapeHtml($key) . '" id="' . $this->escapeHtml($key) . '" value="' . $this->escapeHtml($value) . '">';
        }
        return $fields;
    }

    /**
     * @return mixed
     */
    public function getPaymentFormUrl()
    {
        return $this->_coreRegistry->registry('ipgpay_payment_form_url');
    }

    /**
     * @return string
     */
    public function getRedirectMessage()
    {
        return $this->escapeHtml("You're being redirected to the IPGPAY Payment Gateway. Please do not close the browser window.");
    }
}
