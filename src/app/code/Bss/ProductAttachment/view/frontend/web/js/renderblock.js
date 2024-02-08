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
            var productId = $("#attachment_block_productId").val();
            var customerGroupId = $("#attachment_block_customerId").val();
            var storeId = $("#attachment_block_storeId").val();

            if (productId != undefined && customerGroupId != undefined && storeId != undefined) {
                $.ajax({
                        url: config.postUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            product_id: productId,
                            customer_groupId: customerGroupId,
                            store_id: storeId

                        },
                    success : function(res){
                        $('.productattachment_listattachment_block').append(res.content);
                    }
                });
            }

        }
    }
);
