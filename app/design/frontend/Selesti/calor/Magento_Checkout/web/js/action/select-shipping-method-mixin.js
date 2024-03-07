define([
    'jquery'
], function ($) {
    'use strict';

    return function (target) {
        return function (shippingMethod) {
            if(shippingMethod){
                if(shippingMethod.method_code == 'flatrate'){
                    $('#onepage-checkout-shipping-method-additional-load .delivery-information').hide();
                    $('.remove-delivery-date').click();
                }else if(shippingMethod.method_code == 'freeshipping'){
                    $('#onepage-checkout-shipping-method-additional-load .delivery-information').show();
                }
            }
            // call original method
            target(shippingMethod);
        };
    }
});