var config = {
    config: {
        mixins: {

            // This script is binding to the CHECKOUT page and loads initially
            'Magento_Checkout/js/action/set-shipping-information': {
                'Machship_Fusedship/js/action/set-shipping-information-mixin': true
            },

            // 'Magento_Checkout/js/action/select-shipping-address': {
            //     'Machship_Fusedship/js/action/select-shipping-address-mixin': true
            // },

            // This script is binding to the CART page and loads initially
            "Magento_Checkout/js/view/cart/shipping-estimation" :  {
                "Machship_Fusedship/js/cart/shipping-estimation": true
            },

            // 'Magento_Checkout/js/model/checkout-data-resolver': {
            //     'Machship_Fusedship/js/cart/checkout-data-resolver-mixin': true
            // },

            // 'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
            //     'Machship_Fusedship/js/shipping-rate-processor/new-address-mixin': true
            // },

            // 'Magento_Checkout/js/model/shipping-rate-processor/customer-address': {
            //     'Machship_Fusedship/js/shipping-rate-processor/customer-address-mixin': true
            // }
        }
    }
};
