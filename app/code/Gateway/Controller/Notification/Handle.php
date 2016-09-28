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
namespace IPGPAY\Gateway\Controller\Notification;

use \Magento\Framework\App\Action\Action;
use IPGPAY\Gateway\Api\Constants;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class Handle extends Action
{
    //TODO to be completed
    protected $signature;
    protected $fields = [];
    
    private function getParams()
    {
        $this->signature = $_REQUEST['PS_SIGNATURE'];
        foreach($_REQUEST as $key => $value) {
            if($key != 'PS_SIGNATURE') {
                $this->fields[$key] = $value;
            }
        } 
    }
    
    private function validate()
    {
        if($this->signature != $this->createSignature($this->fields)){
            die('Invalid signature. Aborting!');
        }

        if(!isset($this->fields['notification_type'])){
            die('Invalid notification format');
        }
    }
    
    private function canIgnore()
    {
        if(!in_array($this->fields['notification_type'], array('order', 'orderfailure', 'void', 'settle', 'credit', 'rebillsuccess', 'orderfailure'))){
            return true;
        }   
        return false;
    }

    /**      
     * @param Order
     * @return \Magento\Sales\Model\Order
     */
    private function _getOrder($orderId)
    {
        return $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
    }
    
    public function execute()
    {
        $this->getParams();
        $this->validate();
        
        if ($this->canIgnore()) {
            die('OK'); //Ignore other notifications
        }

        unset($this->fields['PS_EXPIRETIME']);
        unset($this->fields['PS_SIGTYPE']);

        $orderId = $this->fields['order_reference'];
        $order = $this->_getOrder($orderId);
        
        $payment = $order->getPayment();
        if ($payment instanceof Payment) {
            $payment->setTransactionId($this->fields['trans_id']);
        }
        
        $addComments = false;
        if(isset($this->fields['trans_type'])) {
            switch ($this->fields['trans_type']) {
                case 'auth':
                    $payment->setAdditionalData(serialize($this->fields));
                    $payment->setCcTransId($this->fields['trans_id']);
                    $payment->setIsTransactionClosed(0);
                    $transaction = $payment->getTransaction($this->fields['trans_id']);
                    if (!$transaction) {
                        $transaction = $payment->addTransaction(Payment\Transaction::TYPE_AUTH);
                        $addComments = true;
                    }
                    $transaction->setAdditionalInformation(Payment\Transaction::RAW_DETAILS,$this->fields)->save();
                    break;
                case 'sale':
                    $payment->setAdditionalData(serialize($this->fields));
                    $payment->setCcTransId($this->fields['trans_id']);
                    $payment->setIsTransactionClosed(1);
                    $transaction = $payment->getTransaction($this->fields['trans_id']);
                    if (!$transaction) {
                        $transaction = $payment->addTransaction(Payment\Transaction::TYPE_ORDER);
                        $addComments = TRUE;
                    }
                    $transaction->setAdditionalInformation(Payment\Transaction::RAW_DETAILS,$this->fields)->save();
                    break;
            }
        }

        $extraInfoFmt = '';
        if(isset($this->fields['response'])){
            $extraInfoFmt .= "Response: {$this->fields['response']} ";
        }

        if(isset($this->fields['response_code'])){
            $extraInfoFmt .= "Response Code: {$this->fields['response_code']} ";
        }

        if(isset($this->fields['response_text'])){
            $extraInfoFmt .= "Response Text: {$this->fields['response_text']} ";
        }

        if(isset($this->fields['trans_id'])){
            $extraInfoFmt .= "Transaction ID: {$this->fields['trans_id']} ";
        }

        if(isset($this->fields['order_id'])){
            $extraInfoFmt .= "IPGPAY Order ID: {$this->fields['order_id']} ";
        }


        if(isset($this->fields['trans_type'])){
            $extraInfoFmt .= "Transaction Type: {$this->fields['trans_type']} ";
        }

        if(isset($this->fields['auth_code'])){
            $extraInfoFmt .= "Auth Code: {$this->fields['auth_code']} ";
        }

        $invoices = $order->getInvoiceCollection();
        $has_invoice = count($invoices) > 0;

        $response = $this->fields['notification_type'];
        switch($response) {
            case 'order':
                if($this->fields['trans_type'] == Constants::TRANSACTION_MODE_SALE){
                    $orderState = Order::STATE_PROCESSING;
                    if(!$has_invoice) {
                        $this->createInvoice($order, $this->fields['trans_id']);
                    }
                } else {
                    $orderState = Order::STATE_PENDING_PAYMENT;
                }
                $this->modifyOrderPayment($order, $extraInfoFmt, Constants::TRANSACTION_STATE_APPROVED, $orderState);
                //TODO $order->queueNewOrderEmail();
                $order->setEmailSent(true);
                $order->addStatusHistoryComment('Order email sent to customer');
                $order->save();
                break;
            case 'void':
                $this->modifyOrderPayment($order, $extraInfoFmt, Constants::TRANSACTION_STATE_VOIDED, $order->getState());
                break;
            case 'settle':
                $payment->setAdditionalData(serialize($this->fields));
                $payment->setCcTransId($this->fields['trans_id']);
                $payment->setIsTransactionClosed(1);
                $transaction = $payment->getTransaction($this->fields['trans_id']);
                if(!$transaction) {
                    $transaction = $payment->addTransaction(Payment\Transaction::TYPE_CAPTURE);
                }
                $transaction->setAdditionalInformation(Payment\Transaction::RAW_DETAILS,$this->fields)->save();
                $this->modifyOrderPayment($order, $extraInfoFmt, 'Settled', Order::STATE_PROCESSING);
                if(!$has_invoice) {
                    $this->createInvoice($order, $this->fields['trans_id']);
                }
                break;
            case 'orderpending': // Pending
                $this->modifyOrderPayment($order, $extraInfoFmt, 'Pending', Order::STATE_PENDING_PAYMENT);
                $order->queueNewOrderEmail();
                $order->setEmailSent(true);
                $order->addStatusHistoryComment('Order email sent to customer');
                $order->save();
                break;
            case 'credit':
                $payment->setCcTransId($this->fields['trans_id']);
                $transaction = $payment->getTransaction($this->fields['trans_id']);
                if(!$transaction){
                    $transaction = $payment->addTransaction(Payment\Transaction::TYPE_REFUND);
                    $addComments = TRUE;
                }
                $this->addTransactionDetails($transaction, $this->fields);
                $this->modifyOrderPayment($order, $extraInfoFmt, 'Credited', $order->getState(), $addComments);
                //If credit was done in Magento, state may be moved to closed if whole order has been credited.
                //The gateway supports credits on rebills which doesn't mean the order should be closed.
                break;
            case 'rebillsuccess':
                $transaction = $payment->addTransaction(Payment\Transaction::TYPE_PAYMENT);
                $this->addTransactionDetails($transaction, $this->fields);
                $this->modifyOrderPayment($order, $extraInfoFmt, 'Approved', $order->getState());
                break;
            case 'orderfailure':
                $transaction = $payment->addTransaction(Payment\Transaction::TYPE_VOID);
                $this->addTransactionDetails($transaction, $this->fields);
                $this->setStateWithoutProtection($order, Order::STATE_CANCELED, Order::STATE_CANCELED, 'Order Abandoned');
                break;
        }

        $payment->save();

        printf("%s\n", isset($_GET['response']) ? $_GET['response'] : 'OK');
        die();
    }

    /**
     * Sign the request to the payment form
     *
     * @param $arr
     * @return string
     */
    private function createSignature($arr)
    {
        $secret = Mage::getStoreConfig("payment/paymentgateway/secret_key");

        ksort($arr, SORT_STRING);
        foreach($arr as $key => $value) {
            $secret .= sprintf('&%s=%s', $key, $value);
        }

        return sha1($secret);
    }


    /**     
     * Create an invoice for order
     * 
     * @param Order $order
     * @param $transactionId
     * @return Order\Invoice
     */
    private function createInvoice(Order $order, $transactionId)
    {
        // sale is automatically captured, create invoice.
        $invoice = $order->prepareInvoice();
        //$invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);
        $invoice->setTransactionId($transactionId);
        $invoice->register()->pay()->save();
        return $invoice;
    }


    /**    
     * Update order state with a comment
     * 
     * @param Order $order
     * @param $extraInfo
     * @param $stateText
     * @param $stateCode
     */
    private function modifyOrderPayment(Order $order, $extraInfo, $stateText, $stateCode)
    {
        $message = sprintf("IPGPAY Payment: %s\n\n", $stateText) . $extraInfo;
        $order->addStatusToHistory($stateCode, $message ,true)->save();
    }
}

