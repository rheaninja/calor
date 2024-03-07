define([ 
    'jquery', 
    'mage/utils/wrapper' 
], function ($, wrapper) { 
    'use strict'; 
    return function(stepNavigator){ 
        var customGetActiveItemIndex = wrapper.wrap(stepNavigator.getActiveItemIndex, function(originalGetActiveItemIndex){ 
            var activeIndex = originalGetActiveItemIndex(), 
                shippinginfo = $('div.opc-block-shipping-information'), 
                emptyClass = 'empty_sidebar'; 
            if (activeIndex){ 
                shippinginfo.removeClass(emptyClass); 
            } else { 
                shippinginfo.addClass(emptyClass); 
            } 
            return activeIndex; 
        }); 
        stepNavigator.getActiveItemIndex = customGetActiveItemIndex; 
        return stepNavigator; 
    }; 
});