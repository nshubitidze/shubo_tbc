define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'shubo_tbc',
            component: 'Shubo_TBC/js/view/payment/method-renderer/embed'
        }
    );

    return Component.extend({});
});
