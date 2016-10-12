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

class Success extends Action
{
    /**
     * Customer will be returned here after checkout on the Secure Payment Form
     */
    public function execute()
    {
        $order = $this->_getCheckout()->getLastRealOrder();
        if(!empty($order) && $order->getRealOrderId()) {
            $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
        }
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }
}
