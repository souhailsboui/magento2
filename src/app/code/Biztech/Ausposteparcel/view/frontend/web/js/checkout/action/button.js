/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function (Element, $, modal, alert) {

    return Element.extend({
        /*
         * Performs configured actions
         */
        
        initialize: function () {
            this._super();
            
                    jQuery(document).on('click', '.OpenValidatePopup', function (event) {
                        var options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: true,
                            modalClass: 'Show-Suggetion-popupbox',
                            title: 'City Suggestions',
                            buttons: []
                        };
                        var popup = modal(options, $('.ApplySuggetoinsPopup'));
                        $('.ApplySuggetoinsPopup').modal('openModal');
                        if ($('.ShowErrorMessage').length != 0) {
                            $('.ShowErrorMessage').remove();
                        }
                        event.preventDefault(); 
                    });

                    jQuery(document).on('click', '.ApplySuggetoinbtn', function (event) {
                        var selectedcity = $("input[name='ApplySuggetoinsFormData']:checked").val();
                        if (selectedcity) {
                            $("input[name=city]").val(selectedcity);
                            if ($('.ShowErrorMessage').length != 0) {
                                $('.ShowErrorMessage').remove();}
                            $(".ApplySuggetoinsPopup").modal("closeModal");
                            if ($('.ErrorMessageContainer').length != 0) {
                                $('.ErrorMessageContainer').fadeOut("normal", function () {
                                    $(this).remove();
                                });
                            }
                            if ($('.OpenValidatePopup').length != 0) {
                                $('.OpenValidatePopup').fadeOut("normal", function () {
                                    $(this).remove();
                                });
                            }
                            $("input[name=city]").trigger("change");
                        } else {
                            if ($('.ShowErrorMessage').length == 0) {
                                $(".CanelSuggetoinbtn").after("<p style='color:red' class='ShowErrorMessage'><br> * Please select Suggestion</p>");
                            }
                        }
                        event.preventDefault();
                    });
                    jQuery(document).on('click', '.CanelSuggetoinbtn', function (event) {
                        $(".ApplySuggetoinsPopup").modal("closeModal");
                        event.preventDefault();
                    });

            return this;
        },
        
        action: function () {
            var actionurl = this.url;
            var city = $("input[name=city]").val();
            var postcode = $("input[name=postcode]").val();
            var regionId = $("select[name=region_id] option:selected").val();
            var regionName = $("select[name=region_id] option:selected").text();
            var country_id = $("select[name=country_id] option:selected").val();
            var postcodeLength = $("input[name=postcode]").val().length;
            if ($('.ErrorMessageContainer').length != 0) {
                $('.ErrorMessageContainer').remove();
            }
            if(country_id == "AU"){
                if(regionId =='' || regionId == undefined){
                    $("select[name=region_id]").after("<div class='ErrorMessageContainer message warning'><p style='margin-bottom: 0rem;' class='ErrorMessage'>Please Select the State/Province.</p></div>");
                }
                if(postcode =='' || postcode == undefined){
                    $("input[name=postcode]").after("<div class='ErrorMessageContainer message warning'><p style='margin-bottom: 0rem;' class='ErrorMessage'>Please Enter the Zip/Postal Code.</p></div>");
                }
            }else{
                if(country_id == '' || country_id == undefined){
                    $("select[name=country_id]").after("<div class='ErrorMessageContainer message warning'><p style='margin-bottom: 0rem;' class='ErrorMessage'>Please Select the Country.</p></div>");
                }
                if(regionId =='' || regionId == undefined){
                    $("select[name=region_id]").after("<div class='ErrorMessageContainer message warning'><p style='margin-bottom: 0rem;' class='ErrorMessage'>Please Select the State/Province.</p></div>");
                }
                if(postcode =='' || postcode == undefined){
                    $("input[name=postcode]").after("<div class='ErrorMessageContainer message warning'><p style='margin-bottom: 0rem;' class='ErrorMessage'>Please Enter the Zip/Postal Code.</p></div>");
                }
            }
                
            if (postcode!='' && regionId!='' &&  city!='' && postcodeLength>=4 && country_id=='AU') {
                $.ajax({
                    url: actionurl,
                    type: "POST",
                    showLoader: true,
                    data: {city : city, postcode:postcode, regionId:regionId, regionName:regionName, country_id:country_id},
                    success: function (result) {
                        
                        /* var obj = jQuery.parseJSON(result); */
                        var obj = result;
                        var suggesstions = obj.suggesstions;
                        var message = obj.message;
                        
                        if (message=='ERROR') {
                            if ($('.ErrorMessageContainer').length != 0) {
                                $('.ErrorMessageContainer').remove();}
                            if ($('.OpenValidatePopup').length != 0) {
                                $('.OpenValidatePopup').remove();}
                            if ($('.ApplySuggetoinsPopup').length != 0) {
                                $('.ApplySuggetoinsPopup').remove();}
                            alert({
                                title: "Address is correct!",
                                content: "Address validated successfully",
                                autoOpen: true,
                                clickableOverlay: false,
                                focus: "",
                                actions: {
                                    always: function () {
                                        console.log("modal closed");
                                    }
                                }
                            });
                        } else if (message!='' && suggesstions!='') {
                            var sugesstoinarray = suggesstions.split(', ');
                            var sugarr = '<br>';
                            for (i = 0; i < sugesstoinarray.length; ++i) {
                                sugarr+= '<input type="radio" value="'+sugesstoinarray[i]+'" name="ApplySuggetoinsFormData" id="sgt_'+i+'"><label for="sgt_'+i+'" >'+sugesstoinarray[i]+'</label><br>';
                            }
                            
                            if ($('.ErrorMessageContainer').length != 0) {
                                $('.ErrorMessageContainer').remove();}
                            if ($('.OpenValidatePopup').length != 0) {
                                $('.OpenValidatePopup').remove();}
                            if ($('.ApplySuggetoinsPopup').length != 0) {
                                $('.ApplySuggetoinsPopup').remove();}
                            
                            $("input[name=postcode]").after("<div class='ErrorMessageContainer message warning'><p class='ErrorMessage'>"+message+"</p><p style='font-weight:bold' class='ErrorSuggessions'>"+suggesstions+"</p></div>");
                            $('.action-secondary').after("&nbsp;&nbsp;<button name='OpenValidatePopup' title='Apply Auspost Suggested City' class='OpenValidatePopup'><span>Apply Auspost Suggested City</span></button>");
                            $('.action-secondary').after("<div style='display:none' class='ApplySuggetoinsPopup'></div>");
                            $(".ApplySuggetoinsPopup").append('<form action="" method="POST" id="ApplySuggetoinsForm">'+sugarr+'<br><button title="Apply Suggestion" name="ApplySuggetoin" class="ApplySuggetoinbtn">Apply</button>&nbsp;&nbsp;<button name="CanelSuggetoin" title="Close" class="CanelSuggetoinbtn">Close</button>');
                        } else if (message!='' && suggesstions=='') {
                            if ($('.ErrorMessageContainer').length != 0) {
                                $('.ErrorMessageContainer').remove();}
                            if ($('.OpenValidatePopup').length != 0) {
                                $('.OpenValidatePopup').remove();}
                            if ($('.ApplySuggetoinsPopup').length != 0) {
                                $('.ApplySuggetoinsPopup').remove();}
                            
                            $("input[name=postcode]").after("<div class='ErrorMessageContainer message warning'><p class='ErrorMessage'>"+message+"</p></div>");
                        }
                    
                    }
                });
            }
            //event.preventDefault();
        }
    });
});