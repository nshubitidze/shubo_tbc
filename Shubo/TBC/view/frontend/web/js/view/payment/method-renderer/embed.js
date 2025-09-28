define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'Shubo_TBC/js/model/payment/flitt'
], function ($, Component, messageList, fullScreenLoader, additionalValidators, redirectOnSuccessAction, flittPayment) {
    'use strict';

    return Component.extend({
        defaults: {
            code: 'shubo_tbc',
            template: 'Shubo_TBC/payment/embed',
        },

        isActive: function () {
            return this.getCode() === this.isChecked();
        },

        render: function () {
            flittPayment.init('#tbc-payment-wrapper');
        },

        placeOrder: async function () {
            var self = this;

            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                try {
                    await flittPayment.makeTransaction();

                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();

                    this.getPlaceOrderDeferredObject()
                        .done(
                            function () {
                                self.afterPlaceOrder();

                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
                                }
                            }
                        );
                    return true;
                } catch (message) {
                    messageList.addErrorMessage({message});
                } finally {
                    self.isPlaceOrderActionAllowed(true);
                    fullScreenLoader.stopLoader();
                }
            }

            return false;
        },
    });
});
