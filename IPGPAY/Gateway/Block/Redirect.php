<?php
/**
 * @version $Id$
 * @copyright Copyright (c) 2002 - 2011 IPG Holdings Limited (a company incorporated in Cyprus).
 * All rights reserved. Use is strictly subject to licence terms & conditions.
 * This computer software programme is protected by copyright law and international treaties.
 * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
 * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
 * For further information, please contact the copyright owner by email copyright@ipgholdings.net
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
        foreach($this->_coreRegistry->registry('ipgpay_payment_form_data') as $key => $value){
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

