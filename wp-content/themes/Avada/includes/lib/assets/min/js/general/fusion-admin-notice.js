jQuery,jQuery(function(){jQuery(".notice.fusion-is-dismissible button.notice-dismiss").click(function(i){var n=jQuery(this).parent().data();i.preventDefault(),jQuery.post(ajaxurl,{data:n,action:"fusion_dismiss_admin_notice",nonce:fusionAdminNoticesNonce})})});