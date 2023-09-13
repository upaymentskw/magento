/**
 * upayments-cc
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        _,
        $,
        Component,
        VaultEnabler,
        quote,
        _urlBuilder,
        globalMessageList,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageserv_UPayments/payment/cc-form',
                paymentToken: ''
            },

            initialize: function () {
                var self = this;

                self._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return self;
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'paymentToken'
                    ]);

                return this;
            },
            getCode: function() {
                return 'upayments_cc';
            },
            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment[this.getCode()].availableTypes;
            },
            /**
             * Get list of months
             * @returns {Object}
             */
            getCcMonths: function () {
                return window.checkoutConfig.payment[this.getCode()].months;
            },

            /**
             * Get list of years
             * @returns {Object}
             */
            getCcYears: function () {
                return window.checkoutConfig.payment[this.getCode()].years;
            },

            /**
             * Check if current payment has verification
             * @returns {Boolean}
             */
            hasVerification: function () {
                return window.checkoutConfig.payment[this.getCode()].hasVerification;
            },
            getCvvImageUrl: function () {
                return window.checkoutConfig.payment[this.getCode()].cvvImageUrl;
            },
            beforePlaceOrder: function () {
                this.getPaymentToken();
            },

            /**
             * Send request to get payment method nonce
             */
            getPaymentToken: function () {
                var self = this;
                fullScreenLoader.startLoader();
                $.post(_urlBuilder.build("upayments/paypage/tokenize"),
                    this.getData()
                )
                .done(function (response) {
                    fullScreenLoader.stopLoader();
                    if(response.success){
                        self.paymentToken(response.token);
                        self.placeOrder();
                    }else{
                        globalMessageList.addErrorMessage({
                            message: response.message
                        });
                    }
                })
                .fail(function (response) {
                    var error = JSON.parse(response.responseText);

                    fullScreenLoader.stopLoader();
                    globalMessageList.addErrorMessage({
                        message: error.message
                    });
                });
            },

            getData: function () {
                var data = this._super();
                if (!('additional_data' in data)) {
                    data['additional_data'] = {};
                }
                data['additional_data']['quote_id'] = quote.getQuoteId();
                data['additional_data']['payment_token'] = this.paymentToken();
                this.vaultEnabler.visitAdditionalData(data);
                return data;
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].vault_code;
            },
            isActive: function () {
                return this.getCode() === this.isChecked();
            },
            getIcon: function () {
                if (this.hasIcon())
                    return window.checkoutConfig.payment[this.getCode()].icon;
            },
            hasIcon: function () {
                return typeof window.checkoutConfig.payment[this.getCode()] !== 'undefined' &&
                    typeof window.checkoutConfig.payment[this.getCode()].icon !== 'undefined';
            },
        });
    });
