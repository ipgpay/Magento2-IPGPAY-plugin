<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\IPGPAYMagento2\Controller\Config;

use IPGPAY\IPGPAYMagento2\Controller\Redirect\Index;
use IPGPAY\IPGPAYMagento2\API\ParamSigner;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Popup extends Index
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
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidSignatureTypeException
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($this->_isUsePopup) {

            $order                    = $this->_getCheckout()->getLastRealOrder();
            $formSubmissionParameters = $this->getFormSubmissionParameters($order);

            $signatureLifetime = $this->getIPGPAYConfig('request_expiry');
            if (!$signatureLifetime) {
                $signatureLifetime = Config::DEFAULT_SIGNATURE_LIFETIME;
            }

            $paramSigner = new ParamSigner();
            $paramSigner->setSecret($this->getIPGPAYConfig('secret_key'));
            $paramSigner->setLifeTime($signatureLifetime);
            $paramSigner->setSignatureType('PSSHA1');

            $sigstring = $paramSigner->generateQueryString($formSubmissionParameters);

            $paymentFormUrl = $this->getPaymentFormUrl() . "?" . $sigstring;
            /**
             * @var \Magento\Framework\Controller\Result\Json $resultJson
             */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result = ['popup' => "1",'domain' => $this->getPaymentFormHost(), 'url' => $paymentFormUrl]);
            return $resultJson;
        }
        $resultJson->setData(['popup'=>false]);
        return $resultJson;
    }
}
