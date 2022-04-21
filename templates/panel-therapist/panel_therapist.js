
pg_action_ajax = pTpst.pgAjaxAction;
id_panel_pg_ajax = '#panel-jobs-therapist';
scAttrs = pTpst.attrs;
function acceptJob(btn, loc_id, loc_date_id) {
    jQuery(btn).button('loading');
    
    jQuery(document).on('ajaxEndResponseJob', function(){
        window.location.reload();
    });
    responseJob('yes', loc_id, loc_date_id);
    
}
function responseJob(status, loc_id, loc_date_id) {
    var url_ajax = window.location.protocol+'//'+window.location.host+'/wp-admin/admin-ajax.php';
    var params = {
        action: 'therapist_action_job',
        response: status,
        location_id: loc_id,
        location_date_id: loc_date_id,
        attrs: pTpst.attrs
    };
    
    //jQuery('#actions_'+loc_date_id+' button').button('loading');
    var msg = jQuery('#msg-therapist');
    if(msg.length <= 0) {
        msg = jQuery('<div id="msg-therapist" class="alert fade in">');
    } else {
        msg.attr('class','alert fade in');
    }
    msg.hide();

    jQuery.ajax({
        type: 'post',
        data: params,
        url: url_ajax,
        success: function(response) {
            var result = jQuery.parseJSON(response);
            
            jQuery('#location_'+loc_date_id).before(msg);
            
            if(result.status == 'OK') {
                var objThis = jQuery('#actions_'+loc_date_id);
                if(status == 'yes') {
                    //objThis.html('<div class="alert alert-info message">'+sM.textCancelJob.replace(/%s/g,pTpst.attrs.name)+'</div>');
                    objThis.html('<div class="banner-accepted alert alert-success sm-tooltip" data-placement="left" data-original-title="Event Accepted">'+
                                '<i class="icon-info-sign"></i>'+
                                '<span class="text">Event Accepted</span>'+
                            '</div>');
                    objThis.find('.sm-tooltip').tooltip();
                } else {
                    var _class = 'button-primary btn btn-success btn-small';
                    var _new_status = 'yes';
                    objThis.html('<button class="'+_class+'" onclick="responseJob(\''+_new_status+'\','+loc_id+','+loc_date_id+');" data-loading-text="">Event Accepted</button>');
                }
                msg.addClass('alert-info');
            } else {
                msg.addClass('alert-error');
                jQuery('#actions_'+loc_date_id+' button').button('reset');
            }
            msg.html('<button type="button" class="close" data-dismiss="alert">&times;</button>'+result.message).slideDown('slow').alert();
            jQuery(document).trigger('ajaxEndResponseJob');
        }
    });
}

jQuery(document).on('ready', init_panel_therapist);
var base_path = window.location.pathname;
function init_panel_therapist() {
    base_path = window.location.pathname;
    
    var test_view_job = /^.*#location_\d+.*$/;
    if(test_view_job.test(window.location.hash)) {
        jQuery(document).trigger('search_location');
    } else {
        sm_is_found = true;
    }
}
jQuery(document).on('search_location',search_location);
var sm_is_found = false;
var sm_is_not_found = false;
function search_location() {
    var id_location = window.location.hash;
    var location_div = jQuery(id_location);
    if(location_div.length <= 0) {
        jQuery(document.body).smdisable();
        nextPage();
    } else {
        sm_is_found = true;
        location_div.addClass('location-search');
        jQuery(document.body).smenable();
        var cbox = jQuery(id_location+'_more_details');
        cbox.colorbox({transition:"fade",width:false,height:false,innerWidth:false,innerHeight:false,initialWidth:"600px",initialHeight:"600px",maxWidth:"900px",maxHeight:"600px",scalePhotos:false,opacity:0.3,preloading:false,current:" {current}  {total}",previous:"",next:"",close:"",overlayClose:false,loop:false,escKey:false,arrowKey:false,top:false,right:false,bottom:false,left:false});
        cbox.trigger('click');
    }
}

function nextPage() {
    
    if(sm_is_not_found) {
        return;
    }
    var link_next = jQuery('.pagination .items-pages .arrow-right').find('a');

    if(link_next.length <= 0) {
        sm_is_not_found = true;
        alert(sM.textLocationNotFound);
        window.location.href = window.location.origin+window.location.pathname;
        jQuery(document.body).smenable();
        return;
    } else {
        //link_next.trigger('click');
        window.location.href = link_next.attr('href')+window.location.hash;
    }
}
var tg_smenable = function() {
    if(!sm_is_found)
        jQuery(document).trigger('search_location');
};
jQuery(document).on('tg_smenable', tg_smenable);jQuery(document).on('ready',function(){            /*scAttrs.filter_search = new Object();*/            var objMonth = jQuery('#search_month');            var change_search_year = function() {                var vl = jQuery(this).val();                objMonth.attr('disabled', vl == 'all');                scAttrs.filter_search.year = (vl != 'all' ? vl : '');                goPage(1);            };            jQuery('#search_year').on('change', change_search_year);                        var change_search_month = function() {                var vl = jQuery(this).val();                scAttrs.filter_search.month = (vl != 'all' ? vl : '');                goPage(1);            };            objMonth.on('change', change_search_month);        });
