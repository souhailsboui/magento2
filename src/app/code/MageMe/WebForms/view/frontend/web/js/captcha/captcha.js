define(
    [
        'uiComponent',
        'jquery',
        'ko'
    ], function (Component, $, ko) {
        'use strict';

        return Component.extend({

            defaults: {
                template: '',
                globalOnLoadCallback: 'webformsCaptchaOnload',
                captchaReadyEvent: 'webformsCaptchaApiReady',
                languageCode: '',
                publicKey: '',
                scriptId: 'mm_captcha',
                scriptExists: false,
                responseFieldName: ''
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                this._loadApi();
            },

            /**
             * Loads captcha API and triggers event, when loaded
             * @private
             */
            _loadApi: function () {
                if (this._isApiRegistered !== undefined) {
                    if (this._isApiRegistered === true) {
                        $(window).trigger(this.captchaReadyEvent);
                    }

                    return;
                }
                this._isApiRegistered = false;

                window[this.globalOnLoadCallback] = function () {
                    this._isApiRegistered = true;
                    $(window).trigger(this.captchaReadyEvent);
                }.bind(this);

                if (!this.scriptExists) {
                    this.addCaptchaScriptTag();
                }
            },

            renderCaptcha: function () {
            },

            addCaptchaScriptTag: function () {
                this.scriptExists = true;
            }
        });
    });