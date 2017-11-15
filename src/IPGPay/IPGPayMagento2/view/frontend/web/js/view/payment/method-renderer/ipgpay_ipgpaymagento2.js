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
                template: 'IPGPay_IPGPayMagento2/payment/ipgpay_ipgpaymagento2'
            },
            getCode: function () {
                return 'ipgpay_ipgpaymagento2';
            },
            isActive: function () {
                return true;
            },
            afterPlaceOrder: function () {
                $.ajax({
                    url: url.build('ipgpay/config/popup'),
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true
                }).done(function (isUsePopup) {
                    if (isUsePopup=="1") {
                    setTimeout(function () {
                            $('.action.primary.checkout').attr("disabled",true);
                        }, 0);
        
                        $.ajax({
                            url: url.build('ipgpay/redirect/index'),
                            type: 'POST',
                            dataType: 'json',
                            showLoader: true
                        }).done(function (data) {

                            $('.loading-mask').css('display','none')
                            //create modal template
                            var body = document.getElementsByTagName('body')[0];
                  
                            var overlay = document.createElement('div');
                            overlay.id = 'pupayment-overlay';
                            overlay.setAttribute('style', 'display: none;position: fixed;left: 0px;top: 0px;width:100%;height:100%;text-align:center;z-index: 1040;background:#000000;filter:alpha(opacity=70);opacity: .7;');
                
                            var popupContainer = document.createElement('div');
                            popupContainer.id = 'pupayment-popupContainer';
                            popupContainer.setAttribute('style', 'display: none;position:fixed;  top: 0;right: 0;bottom: 0;left: 0;z-index: 1050;');
                
                            popupContainer.innerHTML = '' +
                                '<div style="border-radius: 6px;position: relative; background-color: #c5e0f3;width:375px;height:500px;margin: 25px auto;border:2px solid #fff;text-align:center;">' +
                                '<div style="height:20px;padding:5px;margin-top:-20px;position:relative;box-sizing:border-box;">' +
                                '<button type="button" class="pupayment-close-button" style="position:absolute;top:25px;right:10px;-webkit-appearance: none;padding: 0;cursor: pointer;background: 0 0;border: 0;font-size: 21px;font-weight: 700;line-height: 1;color: #000;text-shadow: 0 1px 0 #fff;opacity: .2;">' +
                                '<span style="cursor: pointer;font-size: 21px;font-weight: 700;line-height: 1;color: #000;text-shadow: 0 1px 0 #fff;text-align:right;">×</span></button>' +
                                '</div>' +
                                '<iframe src="' + data + '" style="width:100%;height:495px;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="100%" height="100%"></iframe>' +
                                '</div>';
                            body.appendChild(overlay);
                            body.appendChild(popupContainer);
        
                            //add click event for close button
                            var closeButton = getClassElement(popupContainer, "pupayment-close-button")[0];
                            closeButton.onclick = closeAndRedirect;
                            
                            openModal();
                            
                            var isPaymentSuccess = false;
                            //add postmessage listener
                            window.addEventListener('message', function (e) {
                                if (data.indexOf(e.origin)>=0) {
                                    switch (e.data.action) {
                                        case 'PuPayment_Success':
                                            isPaymentSuccess = true;
                                            break;
                                        case 'PuPayment_Decline':
                                            window.location.replace(url.build('ipgpay/land/cancel'));
                                            break;
                                        case 'PuPayment_Error':
                                            window.location.replace(url.build('ipgpay/land/cancel'));
                                            break;
                                        case 'PuPayment_Close':
                                            closeModal();
                                            if (isPaymentSuccess) {
                                                window.location.replace(url.build('ipgpay/land/success'));
                                            } else {
                                                window.location.replace(url.build('ipgpay/land/cancel'));
                                            }
                                            break;
                                    }
                                    return true;
                                }
                            }, false);
                            
                            function closeAndRedirect()
                            {
                                if (isPaymentSuccess) {
                                    window.location.replace(url.build('ipgpay/land/success'));
                                } else {
                                    window.location.replace(url.build('ipgpay/land/cancel'));
                                }
                                closeModal();
                            }

                            function openModal()
                            {
                                var overlay = document.getElementById("pupayment-overlay");
                                overlay.style.display = (overlay.style.display == "block") ? "none" : "block";
                                var container = document.getElementById("pupayment-popupContainer");
                                container.style.display = (container.style.display == "block") ? "none" : "block";
                                return false;
                            }
            
                            function closeModal()
                            {
                                overlay = document.getElementById("pupayment-overlay");
                                overlay.style.display = "none";
                                var container = document.getElementById("pupayment-popupContainer");
                                container.style.display = "none";
                            }
            
                            //find elements by class name
                            function getClassElement(node, classname)
                            {
                                if (node.getElementsByClassName) {
                                    //find class and return
                                    return node.getElementsByClassName(classname);
                                } else {
                                    var elems = node.getElementsByTagName(node),
                                        defualt = [];
                                    for (var i = 0; i < elems.length; i++) {
                                        //find all elements
                                        if (elems[i].className.indexOf(classname) != -1) {
                                            //find class name elements
                                            defualt[defualt.length] = elems[i];
                                        }
                                    }
                                    return defualt;
                                }
                            }
                        });
                    } else {
                        window.location.replace(url.build('ipgpay/redirect/index'));
                    }
                });
            }
        });
    }
);

