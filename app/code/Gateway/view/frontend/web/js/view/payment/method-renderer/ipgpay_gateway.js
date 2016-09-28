/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'IPGPAY_Gateway/js/action/set-payment-method'
    ],
    function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        setPaymentMethodAction
    ) {
        'use strict';
        return Component.extend({
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
                this.selectPaymentMethod();
                setPaymentMethodAction(this.messageContainer);
                return false;
            }
        });
    }
);
