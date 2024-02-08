/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttachment
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
        'jquery'
    ],
    function($) {
        return function(config){
            $(document).on('click', 'a.attachment_download', function(event){
                let id = $(this).attr('id');
                let attachmentTag = $(this);
                let dowtimeTagID = ".attDownloadTime" + id;
                let limitedID = "#attLimited" + id;
                $.ajax({
                        url: config.postUrl,
                        type: 'POST',
                        data: {
                            file_id: id
                        },
                    success : function(res){
                        JSON.stringify(res);
                        $(dowtimeTagID).html("( "+res.downloadtime + ' downloads )');
                        if (res.limited === 'limited') {
                            attachmentTag.prop('disabled',true);
                            attachmentTag.removeAttr('href');
                            let selector = 'a#' + id + '.attachment_download';
                            let selectorFooter = 'a#' + id + '.attachment_footer';
                            $(limitedID).html("(limited)");
                            let stringLabelAttachmentTab = '#tab-label-attachment-tab';
                            $(stringLabelAttachmentTab).removeClass('active');
                            $(stringLabelAttachmentTab).remove();
                            $('#attachment-tab').remove();
                            $('.item:first-child').addClass('active');
                            $('#bss.attachment.tab').css('display', 'block');
                            $(selector).css('display', 'none');
                            $(selectorFooter).css('display', 'none');
                        }
                    }
                });
            });
        }
    }
);
