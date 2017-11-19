<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\IPGPAYMagento2\Controller\Land;

use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;

class Returns extends Action
{
    /**
     * Customer will be returned here if payment is unsuccessful
     */
    public function execute()
    {
        $this->_getCheckout()->restoreQuote();
        $this->_redirect('checkout');
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }
}
