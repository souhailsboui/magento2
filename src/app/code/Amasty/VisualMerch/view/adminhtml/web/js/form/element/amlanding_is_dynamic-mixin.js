define([], function () {
    return function (amlanding_is_dynamic) {
        return amlanding_is_dynamic.extend({
            merchFieldNamesToDisable: [
                'category_form.category_form.assign_products',
                'category_form.category_form.general.amlanding_page_id'
            ],

            initialize: function () {
                this.prepareDependentFieldNames();
                this._super();
            },

            prepareDependentFieldNames: function () {
                _.each(this.merchFieldNamesToDisable, function (nameToDisable, index) {
                    delete this.dependentFieldNames[nameToDisable];
                }, this);
            }
        });
    };
});
