/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'upayments_cc',
                component: 'Mageserv_UPayments/js/view/payment/method-renderer/upayments-cc'
            }
        );
        return Component.extend({});
    }
);
