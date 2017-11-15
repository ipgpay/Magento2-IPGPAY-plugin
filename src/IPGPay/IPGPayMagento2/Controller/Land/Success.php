<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPay\IPGPayMagento2\Controller\Land;

use Magento\Framework\App\Action\Action;

class Success extends Action
{
    /**
     * Customer will be returned here after checkout on the Secure Payment Form
     */
    public function execute()
    {
        $order = $this->_getCheckout()->getLastRealOrder();
        if (!empty($order) && $order->getRealOrderId()) {
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
