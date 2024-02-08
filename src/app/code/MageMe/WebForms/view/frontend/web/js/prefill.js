define(["jquery", "Magento_Customer/js/customer-data"], function ($, customerData) {

    function prefill(options, node) {
        var o = {
            attribute: ''
        };
        for (var k in options) {
            if (options.hasOwnProperty(k)) o[k] = options[k];
        }

        var data = customerData.get("webforms");
        data.subscribe(function (data) {
            if (data[o.attribute] && !$(node).val()) {
                $(node).val(data[o.attribute]);
            }
        });
        if (data()[o.attribute] && !$(node).val()) {
            $(node).val(data()[o.attribute]);
        }
    }

    return prefill;
});
