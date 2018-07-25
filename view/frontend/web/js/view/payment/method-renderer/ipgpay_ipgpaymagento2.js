/*browser:true*/
/*global define*/
define(
    ['jquery', 'Magento_Checkout/js/view/payment/default', 'mage/url'],
    function($, Component, url) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'IPGPAY_IPGPAYMagento2/payment/ipgpay_ipgpaymagento2'
            },
            getCode: function() {
                return 'ipgpay_ipgpaymagento2';
            },
            isActive: function() {
                return true;
            },
            afterPlaceOrder: function() {
                $.ajax({
                    url: url.build('ipgpay/config/popup'),
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).done(function(result) {
                    if (result && result.popup === "1") {console.log(result);
                        $('.action.primary.checkout').addClass('pupayment-pay-button').attr("disabled", true);
                        var domain = result.domain.replace(/https?\:\/\//i,'');console.log(domain);
                        var popup = new PuPayment();
                        popup.init(domain, result.url);
                        popup.openModal();
                        $('.loading-mask').css('display', 'none');

                        var isPaymentSuccess = false;
                        var isPaymentDecline = false;
                        var isPaymentError = false;
                        var closeRedirect = function(){
                            if (isPaymentSuccess) {
                                window.location.replace(url.build('ipgpay/land/success'));
                            } else if (isPaymentDecline) {
                                window.location.replace(url.build('ipgpay/land/decline'));
                            } else if (isPaymentError) {
                                window.location.replace(url.build('ipgpay/land/decline'));
                            } else {
                                window.location.replace(url.build('ipgpay/land/returns'));
                            }
                        };
                        popup.onSuccess(function() {
                            isPaymentSuccess = true;
                        });
                        popup.onDecline(function() {
                            isPaymentDecline = true;
                        });
                        popup.onError(function() {
                            isPaymentError = true;
                        });
                        popup.onClose(function() {
                            closeRedirect();
                        });
                    } else {
                        window.location.replace(url.build('ipgpay/redirect/index'));
                    }
                });
            }
        });
    });