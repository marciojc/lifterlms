jQuery(document).ready(function($){$("#associated_course").chosen(),$("#associated_section").chosen(),$("#trigger-select").chosen(),$(".question-select").chosen(),$("#lifterlms_gateway_is_accepted_cards").chosen({width:"350px"})}),jQuery(".metabox_submit").click(function(e){e.preventDefault(),jQuery("#publish").click()}),get_all_lessons=function(){var e=new Ajax("post",{action:"get_lessons"},!0);e.get_all_posts()},get_all_sections=function(){var e=new Ajax("post",{action:"get_sections"},!0);e.get_all_posts()},get_all_courses=function(){var e=new Ajax("post",{action:"get_courses"},!0);e.get_all_posts()},get_all_emails=function(){var e=new Ajax("post",{action:"get_emails"},!0);e.get_all_engagements()},get_all_achievements=function(){var e=new Ajax("post",{action:"get_achievements"},!0);e.get_all_engagements()},get_all_certificates=function(){var e=new Ajax("post",{action:"get_certificates"},!0);e.get_all_engagements()};