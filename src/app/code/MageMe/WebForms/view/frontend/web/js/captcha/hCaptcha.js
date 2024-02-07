define(
    [
        'webformsCaptcha',
        'jquery',
        'ko'
    ], function (Captcha, $, ko) {
        'use strict';

        return Captcha.extend({

            defaults: {
                template: 'MageMe_WebForms/captcha/hCaptcha',
                size: 'normal',
                theme: 'light',
                render: 'explicit'
            },

            isInvisible: function () {
                return this.size === 'invisible';
            },

            renderCaptcha: function () {
                if (window.hcaptcha && window.hcaptcha.render) {
                    this.initCaptcha();
                } else {
                    $(window).on(this.captchaReadyEvent, function () {
                        this.initCaptcha();
                    }.bind(this));
                }
            },

            /**
             * Initialize CAPTCHA after first rendering
             */
            initCaptcha: function () {
                const params = {theme: this.theme, sitekey: this.publicKey, size: this.size};
                if (!this.isInvisible()) {
                    params.callback = this.saveToken.bind(this);
                }
                const captchaDivs = document.querySelectorAll('[class="hcaptcha-container"]');
                const widgetIds = [];
                for (let i = 0; i < captchaDivs.length; i++) {
                    try {
                        widgetIds.push(hcaptcha.render(captchaDivs[i], params));
                    } catch (e) { /* captcha was already rendered */ }
                }
                if (this.isInvisible()) {
                    const self = this;
                    function getCaptchaToken() {
                        for (let i = 0; i < widgetIds.length; i++) {
                            hcaptcha.execute(widgetIds[i], { async: true }).then(self.saveToken.bind(self)).catch(err => {
                                //console.log(err);
                            });
                        }
                    }

                    getCaptchaToken();
                    setInterval(getCaptchaToken, 60000);
                }
            },

            saveToken: function (response) {
                let token = response;
                if (typeof response === 'object' && response !== null) {
                    token = response.response;
                }
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

                    script.src = `https://js.hcaptcha.com/1/api.js?onload=${this.globalOnLoadCallback}&render=${this.render}`;
                    if (this.languageCode) {
                        script.src += `&hl=${this.languageCode}`
                    }
                    firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
                }
                this.scriptExists = true;
            }
        });
    });