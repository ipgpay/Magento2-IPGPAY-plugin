/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'IPGPAY_Gateway/payment/ipgpay-form'
            }
            // add required logic here
        });
    }
);