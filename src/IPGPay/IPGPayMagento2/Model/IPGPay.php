<?php
/**
 * @version $Id$
 * @copyright Copyright (c) 2002 - 2016 IPG Holdings Limited (a company incorporated in Cyprus).
 * All rights reserved. Use is strictly subject to licence terms & conditions.
 * This computer software programme is protected by copyright law and international treaties.
 * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
 * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
 * For further information, please contact the copyright owner by email copyright@ipgholdings.net
 **/
namespace IPGPay\IPGPayMagento2\Model;

use IPGPay\IPGPayMagento2\Api\Request\Credit;
use IPGPay\IPGPayMagento2\Api\Request\Settle;
use IPGPay\IPGPayMagento2\Api\Request\VoidRequest;
use IPGPay\IPGPayMagento2\Api\Response\Success;
use Magento\Payment\Model;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Exception;
use Magento\Sales\Model\Order\Payment;

/**
 * Pay In Store payment method model
 */
class IPGPay extends Model\Method\AbstractMethod implements MethodInterface
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ipgpay_ipgpaymagento2';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;

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
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

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
        
        $capture = new Settle([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '0', //do not notify to avoid duplicate invoices
            'test_mode' => $this->getConfigData('test_mode')
        ]);
        // set amount to request params
        $capture->setAmount($amount);

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
        } catch (\Exception $e) {
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

        $void = new VoidRequest([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '0', //do not notify to avoid duplicate
            'test_mode' => $this->getConfigData('test_mode')
        ]);

        $orderExtraInfo = unserialize($orderExtraInfo);

        try {
            $void->setOrderId($orderExtraInfo['order_id']);
            $res = $void->sendRequest();
            if ($res instanceof Success) {
                $payment->setTransactionId($res->TransId);
            } else {
                throw new Exception\PaymentException(__($res->Response . ' (' . $res->ResponseCode . ') ' . $res->ResponseText));
            }
        } catch (\Exception $e) {
            throw new Exception\PaymentException(__("Cannot issue a void on this transaction: ".$e->getMessage()));
        }
        return $this;
    }

    /**
     * refund - Processes a partial or whole refund on an existing transaction.
     *
     * @param Model\InfoInterface|Payment $payment
     * @param $amount
     * @return $this
     * @throws Exception\PaymentException
     */
    public function refund(Model\InfoInterface $payment, $amount)
    {
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        $orderExtraInfo = $payment->getAdditionalData();
        $this->validateOrderExtraInfo($orderExtraInfo);

        $credit = new Credit([
            'api_base_url' => $this->getConfigData('api_base_url'),
            'api_client_id' => $this->getConfigData('account_id'),
            'api_key' => $this->getConfigData('api_key'),
            'notify' => '0', //do not notify to avoid duplicate
            'test_mode' => $this->getConfigData('test_mode')
        ]);

        $orderExtraInfo = unserialize($orderExtraInfo);

        try {
            $credit->setOrderId($orderExtraInfo['order_id']);
            $transId = $payment->getParentTransactionId();

            if(!isset($transId)) {
                $logger->addCritical('get parent transaction id by other ways', $orderExtraInfo);                
            }            
            $credit->setTransId($transId);
            $credit->setAmount($amount);
            $res = $credit->sendRequest();

            if ($res instanceof Success) {
                $payment->setTransactionId($res->TransId);
            } else {
                throw new Exception\PaymentException(__($res->Response . ' (' . $res->ResponseCode . ') ' . $res->ResponseText));
            }
        } catch (\Exception $e) {
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
        if (empty($orderExtraInfo)) {
            if ($this->getDebugFlag()) {
                $this->debug(["info"=>"Unable to locate original order reference"]);
            }
            throw new Exception\PaymentException(__("Unable to locate original order reference"));
        }
    }
}
