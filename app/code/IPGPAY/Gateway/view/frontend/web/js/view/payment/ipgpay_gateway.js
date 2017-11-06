/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ipgpay_gateway',
                component: 'IPGPAY_Gateway/js/view/payment/method-renderer/ipgpay_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
