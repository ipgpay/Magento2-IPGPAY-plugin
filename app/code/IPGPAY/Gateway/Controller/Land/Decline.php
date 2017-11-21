<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\Gateway\Controller\Land;

use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;

class Decline extends Action
{
    /**
     * Customer will be returned here if payment is declined
     */
    public function execute()
    {
        $order = $this->_getCheckout()->getLastRealOrder();
        if (!empty($order)) {
            if ($order->getRealOrderId()) {
                if ($order->getState() != Order::STATE_COMPLETE) {
                    $order->cancel()->addStatusToHistory(Order::STATE_CANCELED, 'Gateway has declined the payment.', true);
                }
            }
        }
        $this->_getCheckout()->restoreQuote();
        $this->_redirect('checkout/onepage/failure');
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }
}
