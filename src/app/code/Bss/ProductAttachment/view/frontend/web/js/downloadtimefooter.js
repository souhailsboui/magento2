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
            $(document).on('click', 'a.attachment_footer', function(event){
                var id = $(this).attr('id');
                var attachmentTag = $(this);
                var dowtimeTagID = ".attDownloadTime" + id;
                var limitedID = "#attLimited" + id;
                $.ajax({
                        url: config.postUrl,
                        type: 'POST',
                        data: {
                            file_id: id
                        },
                    success : function(res){
                        JSON.stringify(res);
                        $(dowtimeTagID).html("( "+res.downloadtime + ' downloads )');
                        console.log(res.limited + " - "+ res.downloadtime);
                        if (res.limited == 'limited') {
                            attachmentTag.prop('disabled',true);
                            attachmentTag.removeAttr('href');
                            $(limitedID).html("(limited)");
                            $('#tab-label-attachment-tab').removeClass('active');
                            $('#tab-label-attachment-tab').remove();
                            $('.productattachment').remove();
                            $('#attachment-tab').remove();
                            $('.item:first-child').addClass('active');
                            $('.content').css('display', 'block');
                        }
                    }
                });
            });
        }
    }
);
