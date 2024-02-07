define(['jquery','jquerySteps', 'mage/validation'], function ($) {
    function multistep(options) {
        var formId = '#webform_' + options.uid;
        var form = $(formId);
        const wizard = document.getElementById(`wizard` + options.uid);

        var config = {
            startAt: 0,
            showBackButton: true,
            showFooterButtons: true,
            onInit: $.noop,
            onDestroy: $.noop,
            onFinish: function () {
                if (validate(form)) {
                    form.submit();
                }
            },
            onChange: function (currentIndex, newIndex, stepDirection) {
                if (stepDirection === 'forward') {
                    const isValid = validate(form);
                    if (isValid && wizard) {
                        isIntersecting(wizard).then(result => {
                            if (!result) {
                                window.scrollTo({top: wizard.offsetTop - 20, behavior: 'smooth'});
                            }
                        });
                    }
                    return isValid;
                }
                return true;
            },
            onChanged: function(currentIndex, newIndex, stepDirection) {
                $('.step-tab-panel.form-step.active').children('fieldset').each(function (i, element) {
                    if ($(this).css("display") === "none") {
                        stepDirection === 'forward' ? steps_api.next() : steps_api.prev();
                        return false;
                    }
                });
            },
            stepSelector: '.step-steps > li',
            contentSelector: '.step-content > .step-tab-panel',
            footerSelector: '.step-footer',
            buttonSelector: 'button',
            activeClass: 'active',
            doneClass: 'done',
            errorClass: 'error'
        };

        var steps = $("#wizard"+ options.uid).steps(config);
        var steps_api = steps.data('plugin_Steps');
    }

    function validate(form) {
        return form.validation() && form.validation('isValid');
    }

    function isIntersecting(el) {
        return new Promise(resolve => {
            const o = new IntersectionObserver(([entry]) => {
                resolve(entry.intersectionRatio === 1);
                o.disconnect();
            });
            o.observe(el);
        });
    }

    return multistep;
});
