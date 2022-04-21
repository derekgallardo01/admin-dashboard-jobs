jQuery(document).on('uspgl_zero_results', function(){
    jQuery('#list-result').html('<tr><td colspan="5">Not found a customer user</td></tr>');
});

jQuery(document).on('ready', init_customer_registry);

function init_customer_registry() {
    /**
     * Init modal window to send message
     *****/
    var btnShowModalMessage = jQuery('.show-modal-message');
    var _dialog = jQuery('#dialog-send-message');
    
    var openDialog = function() {
        _dialog.dialog('open');
    };

    btnShowModalMessage.on('click', openDialog);
    
    var close_modal = function() {
        _dialog.dialog('close');
        jQuery('#message-result').attr('class', '').html('').slideUp('fast');
    };

    var send_message_users = function(type) {

        return function() {
            var listUsers;
            if(type === 'all') {
                listUsers = _configUSPgl.allUsers;
            } else if(type === 'select') {

                var list = jQuery('#list-result .customer_users:checked');
                
                listUsers = [];
                var each_list_users = function(idx, domE) {
                    listUsers.push({ ID: jQuery(domE).val() });
                };
                list.each(each_list_users);

            } else {
                console.log('Error: The param type is not valid');
                return;
            }

            var txt_subject = jQuery('#subject').val();

			
			if(jQuery("#message-content_ifr").length){
				var ifr = document.getElementById('message-content_ifr');
				var txt_message = ifr.contentDocument.body.innerHTML;
			}else{
				var txt_message = jQuery('#message-content').val();
				
			}
	

            if(jQuery.trim(txt_subject).length <= 0) {
                alert('The content subject is empty');
                return;
            }

            if(jQuery.trim(txt_message).length <= 0) {
                alert('The content message is empty');
                return;
            }

            var params = {
                action: 'send_message_users',
                users: listUsers,
                subject: txt_subject,
                message: txt_message
            };

            var box = jQuery('#message-result').attr('class', '').html('').slideUp('fast');
            var loading = jQuery('#ajax_loading');
            loading.show();
            jQuery.ajax({
                data: params,
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                success: function(responseJson) {
                    if(responseJson.status == 'OK') {
                        box.addClass('box-success').html(responseJson.message).slideDown('fast');
                    } else {
                        box.addClass('box-error').html(responseJson.message).slideDown('fast');
                    }
                    loading.hide();
                }
            });
        };
    };

    var send_message_to_users = send_message_users('select');
    var send_message_all_users = send_message_users('all');
    
    _dialog.dialog({
        autoOpen: false,
        modal: true,
        minWidth: 650,
        height: 'auto',
        minHeight: '500px',
        close: close_modal,
        buttons: {
            'Send Message': send_message_to_users,
            'Send to all users': send_message_all_users,
            Cancel: close_modal
        }
    });
}

if(typeof(USPgl_showResult) !== 'function') {
    var USPgl_showResult = function(users) {
        var wrapperResult = jQuery('#results-users');
        var lbl_count_result = jQuery('#count-results');
        var tbody = jQuery('#list-result');
        
        var admin_url = window.location.origin+'/wp-admin/';
        if(users.length > 0) {
            

            lbl_count_result.show().addClass('USPgl_islook').children('.count').html(users.length);
            tbody.html('');
            for(var i in users) {
                user = users[i];
                var num = i%2 === 0 ? 'alternate' : '';
                var img = user.avatar_img === null ? '' : user.avatar_img;
                tbody.append(
                '<tr class="'+num+'">'+
                '<th scope="row" class="check-column"><input type="checkbox" name="customer_users[]" id="user_'+user.ID+'" class="customer_users" value="'+user.ID+'"></th>'+
                '<td class="username column-username">'+img+'<strong><a target="_blank" href="'+admin_url+'user-edit.php?user_id='+user.ID+'">'+user.user_login+'</a></strong></td>'+
                '<td class="name column-name">'+( user.full_name ? user.full_name : user.display_name)+'</td>'+
                '<td class="email column-email">'+user.user_email+'</td>'+
                '<td><div><b>Department:&nbsp;</b>'+user.user_department+'</div><div><b>Position:&nbsp;</b>'+user.user_position+'</div></td>'+
                '</tr>');
            }
        } else {
            lbl_count_result.show().addClass('USPgl_islook').children('.count').html('0');
            tbody.html('<tr><td colspan="5">Not found a customer user</td></tr>');
        }
    };
}