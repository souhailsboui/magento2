define(
    [
        'webformsCaptcha',
        'jquery',
        'ko'
    ], function (Captcha, $, ko) {
        'use strict';

        return Captcha.extend({

            defaults: {
                template: 'MageMe_WebForms/captcha/reCaptcha',
                version: '3',
                position: 'inline',
                theme: 'standard',
                render: 'explicit'
            },

            /**
             * Checking that reCAPTCHA is invisible type
             * @returns {Boolean}
             */
            getIsInvisibleRecaptcha: function () {
                return this.version === '3';
            },

            /**
             * Render reCAPTCHA
             */
            renderCaptcha: function () {
                if (window.grecaptcha && window.grecaptcha.render) { // Check if reCAPTCHA is already loaded
                    this.initCaptcha();
                } else { // Wait for reCAPTCHA to be loaded
                    $(window).on(this.captchaReadyEvent, function () {
                        this.initCaptcha();
                    }.bind(this));
                }
            },

            getPositionClass: function () {
                return `recaptcha-position-${this.position}`;
            },

            /**
             * Initialize reCAPTCHA after first rendering
             */
            initCaptcha: function () {
                const params = {theme: this.theme, sitekey: this.publicKey};
                if (this.getIsInvisibleRecaptcha()) {
                    params.size = 'invisible';
                } else {
                    params.callback = this.saveToken.bind(this);
                }
                const self = this;
                grecaptcha.ready(function () {
                    const captchaDivs = document.querySelectorAll('[class="recaptcha-container"]');
                    const widgetIds = [];
                    for (let i = 0; i < captchaDivs.length; i++) {
                        try {
                            widgetIds.push(grecaptcha.render(captchaDivs[i], params));
                        } catch (e) { /* recaptcha was already rendered */ }
                    }
                    if (self.getIsInvisibleRecaptcha()) {
                        function getCaptchaToken() {
                            for (let i = 0; i < widgetIds.length; i++) {
                                grecaptcha.execute(widgetIds[i]).then(self.saveToken.bind(self));
                            }
                        }

                        getCaptchaToken();
                        setInterval(getCaptchaToken, 60000);
                    }
                })
            },

            saveToken: function (token) {
                const rFields = document.querySelectorAll(`[name="${this.responseFieldName}"]`);
                for (let i = 0; i < rFields.length; i++) {
                    rFields[i].value = token;
                }
            },

            addCaptchaScriptTag: function () {
                if (!document.getElementById(this.scriptId)) {
                    const script = document.createElement('script');
                    const firstScriptTag = document.getElementsByTagName('script')[0];

                    script.async = true;
                    script.defer = true;

                    script.src = `https://www.google.com/recaptcha/api.js?onload=${this.globalOnLoadCallback}&render=${this.render}`;
                    if (this.languageCode) {
                        script.src += `&hl=${this.languageCode}`
                    }
                    if (this.position) {
                        script.src += `&badge=${this.position}`
                    }
                    firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
                }
                this.scriptExists = true;
            }
        });
    });