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
                type: 'ipgpay_ipgpaymagento2',
                component: 'IPGPAY_IPGPAYMagento2/js/view/payment/method-renderer/ipgpay_ipgpaymagento2'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
