/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (
        $,
        Component,
        url
    ) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'IPGPAY_Gateway/payment/ipgpay_gateway'
            },
            getCode: function() {
                return 'ipgpay_gateway';
            },
            isActive: function() {
                return true;
            },
            afterPlaceOrder: function () {
                window.location.replace(url.build('ipgpay/redirect/index'));
            }
        });
    }
);
