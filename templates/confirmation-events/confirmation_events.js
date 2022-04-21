pg_action_ajax = evUser.pgAjaxAction;
id_panel_pg_ajax = '#panel-confirmation-events';
scAttrs = evClientEmployee.attrs;
function convert_time(time) {
	var d = new Date("2014/01/01 "+time);
	var h = d.getHours();
	var m = d.getMinutes();
	return ( h<10 ? '0'+h : h)+':'+( m < 10 ? '0'+m: m)+':00';
}
function convert_minutes(st_hour) {
    var parts = st_hour.split(':');
    return window.parseInt(parts[0])*60+window.parseInt(parts[1]);
}

function create_slider() {
	
	var events = evClientEmployee.attrs.confirmEvents;
	console.log(evClientEmployee);

	var i, rg, reserved, steping, sli_t1, sli_t2;

	steping = 15;//minutes

	for(i in events) {
		ev = events[i];
		
		var sli = jQuery('#slirange_'+ev['ID']);
		if(sli.length > 0) {
			rg = {
				start: convert_time(ev['tstart']),//'10:15:00',
				end: convert_time(ev['tend'])//'14:45:00'
			};

			if( typeof(ev['myrange'].start) != 'undefined' ) {
				myrang = {
					start: convert_minutes(ev['myrange'].start),
					end: convert_minutes(ev['myrange'].end)
				};
			} else { myrang = {}; }
			
			myrang.date = ev['date'];
			reserved = ev['reserved'];

			sli_t1 = jQuery('#slistart_'+ev['ID']);
			sli_t2 = jQuery('#sliend_'+ev['ID']);
			smslider(myrang,rg,reserved,sli,steping,sli_t1,sli_t2);
		}
	}

	/**
	 *
	 * Update profile form
	 *
	 *****/
	var btn_update_profile = jQuery('#update_profile');
	var click_update_profile = function() {
		var valid = true;

		var obj_first_name = jQuery('#first_name');
		var first_name = jQuery.trim( obj_first_name.val() );
		if(first_name.length <= 0) {
			obj_first_name.parent().parent().addClass('error');
			valid = false; 
		} else {
			obj_first_name.parent().parent().removeClass('error');
		}

		var obj_last_name = jQuery('#last_name');
		var last_name = jQuery.trim( obj_last_name.val() );
		if(last_name.length <= 0) {
			obj_last_name.parent().parent().addClass('error');
			valid = false; 
		} else {
			obj_last_name.parent().parent().removeClass('error');
		}

		if(!valid) {
			return;
		}

		var department = jQuery.trim( jQuery('#user_department').val() );
		var position = jQuery.trim( jQuery('#user_position').val() );

		var old_text = btn_update_profile.text();

		var params = {
			'action': 'ajax_update_profile',
			'first_name': first_name,
			'last_name': last_name,
			'user_department': department,
			'user_position': position
		}
		btn_update_profile.text(sM.textUpdating).prop('disabled', true);
		jQuery.ajax({
			url: window.location.protocol+'//'+window.location.host+'/wp-admin/admin-ajax.php',
			data: params,
			type: 'post',
			success: function(response) {
				window.location.reload();
			},
			error: function() {
				smalert(sM.txtCheckConnection);
				window.setTimeout(function(){
					window.location.reload();
				}, 3000);
			}
		});
	};
	btn_update_profile.on('click', click_update_profile);
	
}
jQuery(document).on('ready', create_slider);
jQuery(document).on('tg_smenable', create_slider);



function confirm_event(lead_id,location_date_id) {

	if(!evClientEmployee.attrs.upProfile) {
		smalert(txtNotUpdateInfo);
		window.setTimeout(smreload,3000);
		return;
	}

	var tstart, tend, select_start, select_end;
	var get = function() {
		var i, ev;
		var events = evClientEmployee.attrs.confirmEvents;
		for(i in events) {
			ev = events[i];
			if(ev['ID'] == location_date_id) {
				return ev;
			}
		}
		return null;
	};
	

    var convert_minutes_to_time = function(minutes) {
		var h = Math.floor(minutes/60);
		h = h < 10 ? '0'+h : h;
		var m = minutes%60;
		m = m < 10 ? '0'+m : m;
		return h+':'+m+':'+':00';
	};

	var is_range_valid = function(rang) {
		

		var ms = convert_minutes(rang.start);
		var me = convert_minutes(rang.end);

		return !(select_start == select_end || select_start > ms && select_start < me || select_end > ms && select_end < me || ms > select_start && ms < select_end || me > select_start && me < select_end);

	};

	var lead = get();

	if(lead) {
		var values = jQuery( '#slirange_'+location_date_id ).slider('values');

		select_start = values[0];
		select_end = values[1];

		tstart = convert_time(lead['tstart']);
		tend = convert_time(lead['tend']);

		var reserves = lead['reserved'];
		

		var isvalid = true;
		var i, reserv;

		for(i in reserves) {
			reserv = reserves[i];
			if( !is_range_valid(reserv) ) {
				isvalid = false;
				break;
			}
		}

		if(!isvalid) {
			smalert(sM.textRangeNotValid);
			return;
		}


		select_start = convert_minutes_to_time(select_start);
		select_end = convert_minutes_to_time(select_end);

		request_confirm(select_start,select_end,lead);

	} else {
		/**
		 * Not found location_date_id
		 ****/
	}

}

function request_confirm(tstart, tend, location_date) {

	var params = {
		action: 'confirm_event',
		time_start: tstart,
		time_end: tend,
		lead_id: location_date['lead_id'],
		location_date_id: location_date['ID'],
		date: location_date['date']
	};

	var btn = jQuery('#btn_confirm_'+params.location_date_id);
	
	if(btn.prop('disabled')) {
		return;
	}

	btn.prop('disabled', true);
	
	jQuery.ajax({
		data: params,
		type: 'post',
		dataType: 'json',
		url: window.location.protocol+'//'+window.location.host+'/wp-admin/admin-ajax.php',
		success: function(response) {
			if(response.status == 'OK') {
				window.setTimeout(smreload,3000);
			}
			smalert(response.message);
			btn.prop('disabled', false);
		},
		error: function() {
			smalert(sM.txtCheckConnection);
			window.setTimeout(smreload,3000);
		}
	});

}