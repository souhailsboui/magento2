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
        'jquery',
        'jquery/jquery-storageapi'
    ],
    function($) {
        return function(config){
            var customerGroupId = $("#att_footer_customer_id").val();
            var storeId = $("#att_footer_store_id").val();
            if (customerGroupId != undefined && storeId != undefined) {
                $.ajax({
                        url: config.postUrl,
                        type: 'POST',
                        dataType:'json',
                        data: {
                            customer_groupId: customerGroupId,
                            store_id: storeId
                        },
                    success: function(res){
                        $("span.bss.productattachment").css('display', 'block');
                        $('.productattachment_footer').append(res.content);
                    }
                });
            }
        }
    }
);
