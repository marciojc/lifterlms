jQuery(document).ready(function($){$("#llms_country_options").chosen(),get_current_price(),$("#show-coupon").on("click",display_coupon_form),$(".llms-payment-options input[type=radio]").change(display_current_price),$(".llms-payment-methods input[type=radio]").length&&(console.log($(".llms-payment-methods input[type=radio]").data("payment-type")),"creditcard"==$(".llms-payment-methods input[type=radio]:checked").data("payment-type")&&$(".llms-creditcard-fields").show()),$(".llms-payment-methods input[type=radio]").change(display_credit_card_fields)}),function($){display_credit_card_fields=function(){"creditcard"==$(this).data("payment-type")?$(".llms-creditcard-fields").slideDown("fast"):$(".llms-creditcard-fields").slideUp("fast")}}(jQuery),function($){display_coupon_form=function(){return $(this).hide(),$("#llms-checkout-coupon").show().slideDown("slow"),!1}}(jQuery),function($){display_current_price=function(){var t=$(this).attr("id"),e=$("#"+t).parent().find("label").text();$(".llms-final-price").text(e)}}(jQuery),function($){get_current_price=function(){var t=$(".llms-payment-options input[type=radio]:checked"),e=$(t).attr("id"),t=$("#"+e).parent().find("label").text();$(".llms-final-price").text(t)}}(jQuery);