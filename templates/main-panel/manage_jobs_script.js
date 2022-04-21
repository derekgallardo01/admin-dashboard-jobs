jQuery(document).on('ready', function(){
    
    /** search filter **/
    var change_filter_year = function() {
        var vl = jQuery(this).val();
        jQuery('#filter_month').attr('disabled', vl == 'all');
    };
    jQuery('#filter_year').on('change', change_filter_year);
    /** end search filter ******************/
    
});
