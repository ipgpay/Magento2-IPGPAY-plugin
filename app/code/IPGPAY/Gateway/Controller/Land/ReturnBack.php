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

class Return extends Action
{
    /**
     * Customer will be returned here if clicks return button
     */
    public function execute()
    {
        $return_url = 'checkout';
        $order = $this->_getCheckout()->getLastRealOrder();
        if(!empty($order)) {
            $order_status = $order->getStatus();
            if(!empty($order_status)) {
                switch ($order_status) {
                    case Order::STATE_CANCELED:
                        $this->_getCheckout()->restoreQuote();
                        $return_url = 'checkout';
                        break;
                    case Order::STATE_COMPLETE:
                    case Order::STATE_PROCESSING:
                    case Order::STATE_HOLDED:
                        $return_url = 'sales/order/view/order_id/'.$order->getRealOrderId();
                        break;
                    default:
                        break;
                }
            }
        }
        $this->_redirect($return_url);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }
}
