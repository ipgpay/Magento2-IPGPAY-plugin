<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\Gateway\Controller\Config;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;

class Popup extends Action
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig = null;
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

     /**
      * Constructor
      *
      * @param Context $context
      * @param Registry $coreRegistry
      * @param ScopeConfigInterface $scopeConfig
      * @param PageFactory $pageFactory
      */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_scopeConfig = $scopeConfig;
        $this->_resultPageFactory = $pageFactory;
    }
    
    /**
     * Customer will be returned here if payment is unsuccessful
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isUsePopup = $this->getIPGPAYConfig("use_popup");
        $resultJson->setData($isUsePopup);
        return $resultJson;
    }

    /**
     * @param string $key
     * @return string
     */
    private function getIPGPAYConfig($key)
    {
        return  $this->_scopeConfig->getValue("payment/ipgpay_gateway/$key", ScopeInterface::SCOPE_STORE);
    }
}
