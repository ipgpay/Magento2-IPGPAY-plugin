<?php
/**
  * @copyright Copyright (c) 2017 IPG Group Limited
  * All rights reserved.
  * This software may be modified and distributed under the terms
  * of the MIT license.  See the LICENSE.txt file for details.
**/
namespace IPGPAY\Gateway\Controller\Notification;

use IPGPAY\Gateway\Api\Functions;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use IPGPAY\Gateway\Api\Constants;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Handle
 * @package IPGPAY\Gateway\Controller\Notification
 */
class Handle extends Action
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig = null;
    /**
     * @var string
     */
    protected $signature;
    /**
     * @var array
     */
    protected $fields = [];
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var Payment
     */
    protected $payment;


    /**
     * Constructor
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig

     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }


    /**
     * Entry point for controller handling
     */
    public function execute()
    {
        //Parse and validate the notification params
        $this->parseParams();
        try {
            $this->validate();
        } catch (\Exception $e) {
            return $e->getMessage();   
        }
        
        unset($this->fields['PS_EXPIRETIME']);
        unset($this->fields['PS_SIGTYPE']);

        //Certain notifications can be ignored
        if ($this->canIgnore()) {
            return Constants::NOTIFICATION_RESPONSE_SUCCESSFUL;
        }

        //Load the order and related payment, save the notification to the payment
        $this->loadOrderAndPayment();
        $this->saveNotificationToPayment();

        //Process the notification
        switch ($this->getNotificationType()) {
            case Constants::NOTIFICATION_TYPE_ORDER:
                switch ($this->getTransType()) {
                    case Constants::TRANSACTION_MODE_AUTH:
                        $this->payment->setIsTransactionClosed(false);
                        $this->addTransaction(Payment\Transaction::TYPE_AUTH);
                        break;
                    case Constants::TRANSACTION_MODE_SALE:
                        $this->payment->setIsTransactionClosed(true);
                        $this->addTransaction(Payment\Transaction::TYPE_ORDER);
                        break;
                }
                $this->handleOrderNotification();
                break;
            case Constants::NOTIFICATION_TYPE_ORDER_PENDING:
                $this->handleOrderPendingNotification();
                break;
            case Constants::NOTIFICATION_TYPE_VOID:
                $this->handleVoidNotification();
                break;
            case Constants::NOTIFICATION_TYPE_SETTLE:
                $this->handleSettleNotification();
                break;
            case Constants::NOTIFICATION_TYPE_CREDIT:
                $this->handleCreditNotification();
                break;
            case Constants::NOTIFICATION_TYPE_REBILL_SUCCESS:
                $this->handleRebillSuccessNotification();
                break;
            case Constants::NOTIFICATION_TYPE_ORDER_FAILURE:
                $this->handleOrderFailureNotification();
                break;
        }
        $this->payment->save();

        //Respond with OK
        return Constants::NOTIFICATION_RESPONSE_SUCCESSFUL;
    }

    /**
     * Create an invoice for order
     *
     * @return Order\Invoice|null
     */
    private function createInvoice()
    {
        if($this->order->canInvoice()) {
            $invoice = $this->order->prepareInvoice();
            $invoice->setTransactionId($this->fields['trans_id']);
            $invoice->register();
            $invoice->setState(Order\Invoice::STATE_PAID);
            $invoice->pay();
            $invoice->save();
            return $invoice;
        }
        return null;
    }


    /**
     * Update order state with a comment
     *
     * @param string $stateText
     * @param string $stateCode
     * @return $this
     */
    private function modifyOrderPayment($stateText, $stateCode)
    {
        $message = sprintf("IPGPAY Payment: %s\n\n", $stateText) . $this->getExtraInfo();
        $this->order->addStatusToHistory($stateCode, $message , false)->save();
        return $this;
    }

    /**
     * Parse the notification params
     *
     * @return $this
     */
    private function parseParams()
    {
        $this->signature = $_REQUEST['PS_SIGNATURE'];
        foreach($_REQUEST as $key => $value) {
            if($key != 'PS_SIGNATURE' && array_key_exists($key,$_COOKIE) ==false) {
                $this->fields[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get the secret
     * @return string
     */
    private function getSecret()
    {
        return $this->_scopeConfig->getValue('payment/ipgpay_gateway/secret_key',ScopeInterface::SCOPE_STORE);
    }

    /**
     * Validate the notification
     *
     * @return $this 
     * @throws \Exception
     */
    private function validate()
    {
        if(!Functions::isValidSignature($this->signature, $this->fields, $this->getSecret())){
            throw new \Exception('Invalid signature. Aborting!');
        }

        if(!isset($this->fields['notification_type'])){
            throw new \Exception('Missing notification type');
        }

        if(!isset($this->fields['order_reference'])){
            throw new \Exception('Missing order reference');
        }
        return $this;
    }

    /**
     * @return string|null
     */
    private function getTransType()
    {
        return isset($this->fields['trans_type']) ? $this->fields['trans_type'] : null;
    }

    /**
     * @return string|null
     */
    private function getNotificationType()
    {
        return $this->fields['notification_type'];
    }

    /**
     * Check to see if the notification can be ignored
     *
     * @return bool
     */
    private function canIgnore()
    {
        if(!in_array($this->fields['notification_type'],
            [
                Constants::NOTIFICATION_TYPE_ORDER,
                Constants::NOTIFICATION_TYPE_ORDER_PENDING,
                Constants::NOTIFICATION_TYPE_ORDER_FAILURE,
                Constants::NOTIFICATION_TYPE_VOID,
                Constants::NOTIFICATION_TYPE_SETTLE,
                Constants::NOTIFICATION_TYPE_CREDIT,
                Constants::NOTIFICATION_TYPE_REBILL_SUCCESS,
            ]
        )){
            return true;
        }
        return false;
    }

    /**
     * Load the order and payment
     */
    private function loadOrderAndPayment()
    {
        $this->order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($this->fields['order_reference']);
        $this->payment = $this->order->getPayment();
    }

    /**
     * @return $this
     */
    private function saveNotificationToPayment()
    {
        $this->payment->setTransactionId($this->fields['trans_id']);
        $this->payment->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS,$this->fields);
        $this->payment->setAdditionalData(serialize($this->fields));
        $this->payment->save();
        return $this;
    }

    /**
     * @return string
     */
    private function getExtraInfo()
    {
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
        return $extraInfoFmt;
    }

    /**
     * @return bool
     */
    private function hasInvoice()
    {
        $invoices = $this->order->getInvoiceCollection();
        return  count($invoices) > 0;
    }

    /**
     * @param $type
     * @return $this
     */
    private function addTransaction($type)
    {
        $transaction = $this->payment->addTransaction($type);
        $this->payment->addTransactionCommentsToOrder(
            $transaction,
            "Transaction created from notification type ".$this->fields['notification_type']
        );
        $this->payment->setParentTransactionId(null);
        $this->payment->save();
        $this->order->save();
        return $this;
    }

    /**
     * @return $this
     */
    private function handleOrderNotification()
    {
        if($this->getTransType() == Constants::TRANSACTION_MODE_SALE){
            $orderState = Order::STATE_PROCESSING;
            if(!$this->hasInvoice()) {
                $this->createInvoice();
            }
        } else {
            $orderState = Order::STATE_PENDING_PAYMENT;
        }
        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_APPROVED, $orderState);

        $comment = 'Your payment has been received';
        /** @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender */
        $orderCommentSender = $this->createObject('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');

        $orderCommentSender->send($this->order, true, $comment);
        $this->order->setEmailSent(true);
        $history = $this->order->addStatusHistoryComment('Payment received email sent to customer');
        $history->setIsCustomerNotified(true);
        $this->order->save();
        return $this;
    }

    /**
     * @return $this
     */
    private function handleOrderPendingNotification()
    {
        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_PENDING, Order::STATE_PENDING_PAYMENT);

        $comment = 'Your payment has been received and is pending verification';
        /** @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender */
        $orderCommentSender = $this->createObject('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');

        $orderCommentSender->send($this->order, true, $comment);
        $this->order->setEmailSent(true);
        $history = $this->order->addStatusHistoryComment('Payment pending email sent to customer');
        $history->setIsCustomerNotified(true);
        $this->order->save();
        return $this;
    }

    /**
     * @return $this
     */
    private function handleVoidNotification()
    {
        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_VOIDED, $this->order->getState());
        if($this->order->canCancel()){
            $this->order->setState(Order::STATE_CANCELED);
            $this->order->setStatus(Order::STATE_CANCELED);
            $this->order->save();
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function handleSettleNotification()
    {
        $this->payment->setIsTransactionClosed(true);
        $this->addTransaction(Payment\Transaction::TYPE_CAPTURE);
        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_SETTLED, Order::STATE_PROCESSING);
        if(!$this->hasInvoice()) {
            $this->createInvoice();
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function handleCreditNotification()
    {
        //If credit was done in Magento, state may be moved to closed if whole order has been credited.
        //The gateway supports credits on rebills which doesn't mean the order should be closed.
        $this->addTransaction(Payment\Transaction::TYPE_REFUND);

        /**
         * @var \Magento\Sales\Model\Order\CreditmemoFactory $creditMemoFactory
         */
        $creditMemoFactory = $this->createObject('Magento\Sales\Model\Order\CreditmemoFactory');
        /**
         * @var \Magento\Sales\Model\Service\CreditmemoService $creditMemoService
         */
        $creditMemoService = $this->createObject('Magento\Sales\Model\Service\CreditmemoService');

        $creditMemo = $creditMemoFactory->createByOrder($this->order);

        if ($this->order->getTotalPaid() > abs($this->fields['amount'])) {
            $creditMemo->setAdjustmentNegative($this->order->getTotalPaid() - abs($this->fields['amount']));
        }
        $creditMemo->setBaseGrandTotal(abs($this->fields['amount']));
        $creditMemo->setGrandTotal(abs($this->fields['amount']));
        $creditMemoService->refund($creditMemo, true);

        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_CREDITED, $this->order->getState());
        return $this;
    }

    protected function createObject($name)
    {
        return $this->_objectManager->create($name);
    }

    /**
     * @return $this
     */
    private function handleRebillSuccessNotification()
    {
        $transaction = $this->payment->addTransaction(Payment\Transaction::TYPE_PAYMENT);
        $transaction->setAdditionalInformation(Payment\Transaction::RAW_DETAILS,$this->fields)->save();
        $this->modifyOrderPayment(Constants::TRANSACTION_STATE_APPROVED, $this->order->getState());
        return $this;
    }

    /**
     * @return $this
     */
    private function handleOrderFailureNotification()
    {
        if ($this->hasTransactionId()) {
            $transaction = $this->payment->addTransaction(Payment\Transaction::TYPE_VOID);
            $transaction->setAdditionalInformation(Payment\Transaction::RAW_DETAILS,$this->fields)->save();
        }
        $this->order->setState(Order::STATE_CANCELED);
        $this->order->setStatus(Order::STATE_CANCELED);
        $history = $this->order->addStatusHistoryComment('Order Abandoned');
        $history->setIsCustomerNotified(false); // for backwards compatibility
        $this->order->save();
        return $this;
    }

    /**
     * @return bool
     */
    private function hasTransactionId()
    {
        return !empty($this->fields['trans_id']);
    }
}