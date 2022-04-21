<?php
/**
 * Class load plugin search users javascript
 *****/
class ApiSearch {
	
	public static function load() {
		wp_register_script('maps-geocoder', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), false, true);

		wp_register_script('apijs-search', plugins_url('/usersSearch.js', __FILE__), array('jquery', 'maps-geocoder'), false, true);

		wp_enqueue_script('jquery');
		wp_enqueue_script('maps-geocoder');
		wp_enqueue_script('apijs-search');
	}
}

?>