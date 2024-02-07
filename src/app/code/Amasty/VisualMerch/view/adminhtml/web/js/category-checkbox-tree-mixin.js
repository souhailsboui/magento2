define([
    'jquery',
    'mage/translate',
    'mage/utils/wrapper'
], function ($, $t, wrapper) {
    'use strict';

    return function (mixin) {
        return wrapper.wrap(mixin, function (originFunc, config) {
            var targetTree = $('#' + config.divId),
                ul = $('<ul>', { class: 'ammerchui-comments-block' }),
                commentsList = [
                    $t('Automatic Categories will not participate in condition applying.'),
                    $t('For Anchor categories only physically assigned products will be considered.')
                ]

            originFunc(config);

            if (!targetTree.closest('.ammerchui-condition-wrap').length) {
                return;
            }

            commentsList.forEach(function (comment) {
                $('<li>', { class: 'ammerchui-item', text: comment }).appendTo(ul);
            });

            targetTree.prepend(ul);
        });
    };
});
