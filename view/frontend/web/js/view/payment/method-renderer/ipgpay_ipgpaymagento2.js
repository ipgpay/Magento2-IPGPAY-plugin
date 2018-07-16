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
                    if (result && result.popup === "1") {
                        var formUrl = result.url;
                        var domain = result.domain;
                        var pwidth = 375;
                        var pheight = 504;
                        setTimeout(function() {
                            $('.action.primary.checkout').attr("disabled", true);
                        }, 0);
                        $('.loading-mask').css('display', 'none')
                        //create modal template
                        var body = document.getElementsByTagName('body')[0];
                        var overlay = document.createElement('div');
                        overlay.id = 'pupayment-overlay';
                        overlay.setAttribute('style', 'display: none;position: fixed;left: 0px;top: 0px;width:100%;height:100%;text-align:center;z-index: 1040;background:#000000;filter:alpha(opacity=70);opacity: .7;');
                        var popupContainer = document.createElement('div');
                        popupContainer.id = 'pupayment-popupContainer';
                        popupContainer.setAttribute('style', 'display: none;position:fixed;  top: 0;right: 0;bottom: 0;left: 0;z-index: 1050;');
                        popupContainer.innerHTML = '' + '<div style="border-radius: 6px;position: relative;overflow:hidden;width:'+pwidth+'px;height:'+pheight+'px;margin: 25px auto;border:2px solid #fff;text-align:center;">' +
                            '<button type="button" class="pupayment-close-button" style="position:absolute;top:2px;right:7px;-webkit-appearance: none;padding: 0;cursor: pointer;background: 0 0;border: 0;font-size: 21px;font-weight: 700;line-height: 1;color: #000;text-shadow: 0 1px 0 #fff;opacity: .2;">' +
                            '<span style="cursor: pointer;font-size: 21px;font-weight: 700;line-height: 1;color: #000;text-shadow: 0 1px 0 #fff;text-align:right;">×</span></button>' +
                            '<iframe src="' + formUrl + '" style="width:100%;'+pheight+'px;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="100%" height="100%"></iframe>' +
                            '</div>';
                        body.appendChild(overlay);
                        body.appendChild(popupContainer);
                        //add click event for close button
                        var closeButton = popupContainer.getElementsByClassName("pupayment-close-button")[0];
                        closeButton.onclick = closeAndRedirect;
                        var isPaymentSuccess = false;
                        var isPaymentDecline = false;
                        var isPaymentError = false;
                        //add postmessage listener
                        window.addEventListener('message', function(e) {
                            console.log(e);
                            if (formUrl.indexOf(e.origin) === 0) {
                                switch (e.data.action) {
                                    case 'PuPayment_Success':
                                        isPaymentSuccess = true;
                                        break;
                                    case 'PuPayment_Decline':
                                        isPaymentDecline = true;
                                        break;
                                    case 'PuPayment_Error':
                                        isPaymentError = true;
                                        break;
                                    case 'PuPayment_Close':
                                        closeModal();
                                        processLandUrl();
                                        break;
                                }
                                return true;
                            }
                        }, false);

                        var closeAndRedirect = function() {
                            closeModal();
                            processLandUrl();
                        };

                        var processLandUrl = function() {
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

                        var openModal = function() {
                            var overlay = document.getElementById("pupayment-overlay");
                            overlay.style.display = (overlay.style.display === "block") ? "none" : "block";
                            var container = document.getElementById("pupayment-popupContainer");
                            container.style.display = (container.style.display === "block") ? "none" : "block";
                            return false;
                        };

                        var closeModal = function() {
                            overlay = document.getElementById("pupayment-overlay");
                            overlay.style.display = "none";
                            var container = document.getElementById("pupayment-popupContainer");
                            container.style.display = "none";
                        };
                        openModal();
                    } else {
                        window.location.replace(url.build('ipgpay/redirect/index'));
                    }
                });
            }
        });
    });