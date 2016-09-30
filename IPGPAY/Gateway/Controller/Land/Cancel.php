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
namespace IPGPAY\Gateway\Controller\Land;

use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;

class Cancel extends Action
{
    /**
     * Customer will be returned here if payment is unsuccessful
     */
    public function execute()
    {
        $order = $this->_getCheckout()->getLastRealOrder();
        if($order->getRealOrderId()) {
            // Flag the order as 'cancelled'
            $order->cancel()
                ->addStatusToHistory(Order::STATE_CANCELED,'Gateway has declined the payment.',true);
        }
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
