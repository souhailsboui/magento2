define([
    'Magento_Ui/js/grid/columns/column',
    'ko'
], function (Column, ko) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMe_WebForms/grid/columns/result_id'
        },

        getResultClass: function (record) {
            if (typeof record !== "object") {
                return {};
            }
            const isUnread = !~~record.is_read;
            const isReplied = !!~~record.is_replied;
            const isUnreadReply = !!~~record.is_unread_reply;
            return {
                "unread": isUnread,
                "replied": isReplied && !isUnreadReply,
                "unread-reply": isUnreadReply,
            };
        }
    });
});
