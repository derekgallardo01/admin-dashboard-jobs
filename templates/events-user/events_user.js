pg_action_ajax = evUser.pgAjaxAction;
id_panel_pg_ajax = '#panel-events-user';
scAttrs = evUser2.attrs;

jQuery(document).on('ready', init_event_user);
function init_event_user() {
	var filter = true;
	var filter_spored = jQuery('#only_spored');
	var fcs_tg_smenable = function() {
		filter = true;
	};
	jQuery(document).on('tg_smenable', fcs_tg_smenable);

	var fcs_tg_smdisable = function() {
		filter = false;
	};
	jQuery(document).on('tg_smdisable', fcs_tg_smdisable);

	var change_filter_spored = function() {
		var isCheck = filter_spored.is(':checked');
		if(filter) {
			if(typeof(scAttrs.only_spored) != 'undefined') {
				scAttrs.only_spored = isCheck;
				goPage(1);
			}
		} else {
			filter_spored.prop('checked', !isCheck);
		}
	};
	filter_spored.on('change', change_filter_spored);
}