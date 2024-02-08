var config = {
    map: {
        '*': {
            webformsCaptcha: 'MageMe_WebForms/js/captcha/captcha',
            webformsReCaptcha: 'MageMe_WebForms/js/captcha/reCaptcha',
            webformsHCaptcha: 'MageMe_WebForms/js/captcha/hCaptcha',
            webformsTurnstile: 'MageMe_WebForms/js/captcha/turnstile',
            accessibleDatePicker: 'MageMe_WebForms/js/pickadate/picker.date',
            accessibleTranslation: 'MageMe_WebForms/js/pickadate/translation',
        }
    },
    paths: {
        jquerySteps: 'MageMe_WebForms/js/jquery.steps',
        jqueryBarRating: 'MageMe_WebForms/js/jquery.barrating',
    },
    shim: {
        jquerySteps: {
            deps: ['jquery']
        },
        jqueryBarRating: {
            deps: ['jquery']
        },
    }
};
