define(
    [],
    function () {
        'use strict';
        return {
            getRules: function() {
                return {
                    'postcode': {
                        'required': true
                    },
                    'city' : {
                        'required' : true
                    },
                    'country_id': {
                        'required': true
                    },
                    'region_id' : {
                        'required': true
                    },
                };
            }
        };
    }
)