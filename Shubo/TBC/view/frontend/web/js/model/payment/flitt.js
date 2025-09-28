define([
    'jquery',
    'flittCheckout',
    'Magento_Checkout/js/model/quote'
], function ($, flittCheckout, quote) {
    'use strict';

    return {
        defaults: {
            paymentService: null
        },

        init: async function(selector) {
            const options = {
                options: window.checkoutConfig.payment.shubo_tbc.options,
                params: {
                    merchant_id: window.checkoutConfig.payment.shubo_tbc.merchant_id,
                    currency: window.checkoutConfig.quoteData.quote_currency_code,
                    lang: this.getLang()
                }
            }

            this.paymentService = flittCheckout(selector, options);

            return this.paymentService;
        },

        setFinalParams: async function () {
            const quoteMaskId = quote.getQuoteId();

            if (!quoteMaskId) {
                return Promise.reject(new Error('Quote ID not available'));
            }

            return fetch('/tbc/payment/params', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ quoteMaskId })
            })
                .then(response => {
                    return response.json();
                })
                .then(params => {
                    this.paymentService.setParams(params)
                })
                .catch(error => {
                    return Promise.reject('Something went wrong! Please try again later.')
                });
        },

        makeTransaction: async function() {
            const self = this;
            await this.setFinalParams();

            return new Promise((resolve, reject) => {
                const payment = self.paymentService.submit();

                payment.$on('success', (model) => {
                    resolve(model.attr('info.order_data'));
                });

                payment.$on('error', (model) => {
                    const response_code = model.attr('error.code');
                    const response_description = model.attr('error.message');

                    reject(`${response_description} (code: ${response_code})`);
                });
            });
        },

        getLang: function () {
            if (window.LOCALE) {
                return window.LOCALE.split('-')[0];
            }

            return 'ka';
        }
    };
});
