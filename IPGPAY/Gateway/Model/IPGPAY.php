<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IPGPAY\Gateway\Model;

use IPGPAY\Gateway\Api\Request\Credit;
use IPGPAY\Gateway\Api\Request\Settle;
use IPGPAY\Gateway\Api\Request\Void;
use IPGPAY\Gateway\Api\Response\Success;
use \Magento\Payment\Model;
use \Magento\Payment\Model\MethodInterface;
use \Magento\Framework\Exception;
use Magento\Sales\Model\Order\Payment;

/**
 * Pay In Store payment method model
 */
class IPGPAY extends Model\Method\AbstractMethod implements MethodInterface
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ipgpay_gateway';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal          = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded      = true;


    /**       
     * capture - Settle an authorization transaction
     * 
     * @param Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws Exception\PaymentException
     */
    public function capture(Model\InfoInterface $payment, $amount)
    {
        $orderExtraInfo = $payment->getAdditionalData();
        $this->validateOrderExtraInfo($orderExtraInfo);
        
        $capture = new Settle ([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '1',
            'test_mode' => $this->getConfigData('test_mode')
        ]);

        $orderExtraInfo = unserialize($orderExtraInfo);
        try {
            $capture->setOrderId($orderExtraInfo['order_id']);
            $res = $capture->sendRequest();
            
            if ($res instanceof Success) {
                $payment->setCcTransId($res->TransId);
                $payment->setTransactionId($res->TransId);
            } else {
                throw new Exception\PaymentException(__($res->Response . ' (' . $res->ResponseCode . ') ' . $res->ResponseText));
            }
        } catch (\Exception $e){
            throw new Exception\PaymentException(__("Cannot issue a capture on this transaction: ".$e->getMessage()));
        }

        return $this;
    }


    /**    
     * void - Cancel an authorization transaction that has not yet been settled.
     * 
     * @param Model\InfoInterface|Payment $payment
     * @return $this
     * @throws Exception\PaymentException
     */
    public function void(Model\InfoInterface $payment)
    {
        $orderExtraInfo = $payment->getAdditionalData();
        $this->validateOrderExtraInfo($orderExtraInfo);

        $void = new Void ([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '1',
            'test_mode' => $this->getConfigData('test_mode')
        ]);

        $orderExtraInfo = unserialize($orderExtraInfo);

        try {
            $void->setOrderId($orderExtraInfo['order_id']);
            $res = $void->sendRequest();
            if($res instanceof Success){
                $payment->setTransactionId($res->TransId);
            }else{
                throw new Exception\PaymentException(__($res->Response . ' (' . $res->ResponseCode . ') ' . $res->ResponseText));
            }
        } catch(\Exception $e){
            throw new Exception\PaymentException(__("Cannot issue a void on this transaction: ".$e->getMessage()));
        }
        return $this;
    }

    /**
     * refund - Processes a partial or whole refund on an existing transaction.
     *
     * @param Model\InfoInterface|Payment $payment
     * @return $this
     * @throws Exception\PaymentException
     */
    public function refund(Model\InfoInterface $payment, $amount)
    {
        $orderExtraInfo = $payment->getAdditionalData();
        $this->validateOrderExtraInfo($orderExtraInfo);

        $credit = new Credit ([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '1',
            'test_mode' => $this->getConfigData('test_mode')
        ]);

        $orderExtraInfo = unserialize($orderExtraInfo);

        try {
            $credit->setOrderId($orderExtraInfo['order_id']);
            $credit->setTransId($payment->getParentTransactionId());
            $credit->setAmount($amount);
            $res = $credit->sendRequest();

            if ($res instanceof Success) {
                $payment->setTransactionId($res->TransId);
            } else {
                throw new Exception\PaymentException(__($res->Response . ' (' . $res->ResponseCode . ') ' . $res->ResponseText));
            }
        } catch(\Exception $e){
            throw new Exception\PaymentException(__("Cannot issue a credit on this transaction: ".$e->getMessage()));
        }

        return $this;
    }

    /**
     * @param $orderExtraInfo
     * @throws Exception\PaymentException
     */
    protected function validateOrderExtraInfo($orderExtraInfo)
    {
        if(empty($orderExtraInfo)){
            if($this->getDebugFlag())
            {
                $this->debug(["info"=>"Unable to locate original order reference"]);
            }
            throw new Exception\PaymentException(__("Unable to locate original order reference"));
        }    
    }
}
