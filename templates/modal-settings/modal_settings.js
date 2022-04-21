jQuery(document).on('ready', init_modal_settings);
function init_modal_settings() {
    
    var _dialog = jQuery('#modal-settings');
    
    if(!modalSettings.isFormDefined) {
        _dialog.children('.message').show();
    }
    
    _dialog.dialog({
        autoOpen: !modalSettings.isFormDefined,
        modal: true,
        minWidth: 450,
        close: function(){
            if(!modalSettings.isFormDefined) {
                window.location.reload();
            }
        },
        buttons: {
            'Save Settings': function() {
                
                var params = {
                    action: 'save_settings',
                    form_id: jQuery('#list_forms').val(),
                    radio: jQuery('#search_radio').val()
                };
                jQuery('#ajax_loading').show();
                jQuery.ajax({
                    data: params,
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    success: function(responseJson) {
                        if(responseJson.status == 'OK') {
                            window.location.reload();
                        }
                    }
                });
            },
            Cancel: function() {
                _dialog.dialog('close');
            }
        }
    });

    jQuery('.modal-dialog').parent().addClass('dialog-editor');
    
    jQuery('#'+modalSettings.idDialog).on('click', function(){
        _dialog.dialog('open');
    });
    
    
}
