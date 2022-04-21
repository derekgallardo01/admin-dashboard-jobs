<?php 

/**
 * Ajax callback when the "View Details" page for an entry is being
 */
function getAllLatLngTherapists() {

    $state = $_REQUEST['state'];
    $city = $_REQUEST['city'];
    $zip = $_REQUEST['keyword'];
    $firstname = $_REQUEST['first_name'];
    $lastname = $_REQUEST['last_name'];
	
	global $wpdb;
	
	if(trim($zip)==''){
	$zip = $_REQUEST['zip']!=''?$_REQUEST['zip']:'';
	}
    $meta_keys = array();
    if(!empty($state)) {
    	$abbr_state = query_state_abbreviature($state);

    	$meta_keys[] = "( {$wpdb->prefix}usermeta.meta_value LIKE '%".$abbr_state.", %' OR {$wpdb->prefix}usermeta.meta_value LIKE '%".$state."%' )";
    }
    if(!empty($city)) {
        $meta_keys[] = "{$wpdb->prefix}usermeta.meta_value LIKE '%".$city."%'";
    }
	$multijoin = '';
	$multiwhere = '';
	
	if(!empty($firstname)) {
        $multiwhere .= " and (mt1.meta_key = 'first_name' and mt1.meta_value LIKE '%".$firstname."%')";
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt1 ON ( {$wpdb->prefix}users.ID = mt1.user_id )";
    }
	
	if(!empty($lastname)) {
        $multiwhere .= " and (mt2.meta_key = 'last_name' and mt2.meta_value LIKE '%".$lastname."%')";
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt2 ON ( {$wpdb->prefix}users.ID = mt2.user_id )";
    }
	
    if(!empty($zip)) {
		$radius = get_option( 'mjobs_radio_search' );
		
		$data = json_decode(file_get_contents('https://www.zipwise.com/webservices/radius.php?key=8wco13x6obx2z73g&zip='.$zip.'&format=json&radius='.$radius));
		$zipcodewhere = '';		
		$zipcodess = '';
		$zipscode = array();
	
		$users = array();
		//print_r($data);die();
		if(!empty($data->results)){
			foreach($data->results as $rac){
			
			$meta_keys["zipkey"] = "{$wpdb->prefix}usermeta.meta_value LIKE '%".$rac->zip."%'";
			
		}}
	}
		if(!empty($meta_keys)){
			$meta_address = implode(" AND ", $meta_keys);
			
			$multiwhere .= "and ({$wpdb->prefix}usermeta.meta_key = 'last_name' $meta_address )";
		}
		
		
	
    $query = <<<EOF
	SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}users.* FROM wp_spot_users INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id ) {$multijoin} WHERE 1=1 $multiwhere group by {$wpdb->prefix}users.id ORDER BY user_login ASC;
EOF;
	//echo $query;die();
	$users1 = $wpdb->get_results( $query );

	$users = array_merge($users,$users1);
	//print_r($users1); die('as');

    $all = array();
		
    foreach($users as $u) {
        $data_latlng = get_user_meta($u->ID, 'address_latlng', true);
		
        if( !empty($data_latlng) ) {
			
            $new_data = new stdClass;

            if(empty($data_latlng['latitude'])) {
            	$apiKey = 'AIzaSyDf_JSmGHC4OWU6ABOxvyrci8sBx0qMqEQ';
				$url_base = "https://maps.googleapis.com/maps/api/geocode/xml?sensor=false&oe=UTF-8&key=".$apiKey."&address=";
				$context = stream_context_create( array('http' => array( 'timeout' => 10 ) ) );

				$mailing_address = get_user_meta($u->ID, 'mailing_address', true);
            	$url = $url_base.urlencode($mailing_address);
		        $contents = file_get_contents( $url, false, $context );
		        $xml = new SimpleXMLElement( $contents );

		        if( $xml ) {
		        	$location = $xml->result->geometry->location;
			        $data_latlng = array(
		                'latitude' => is_object($location->lat) ? $location->lat->__toString() : $location->lat,
		                'longitude' => is_object($location->lng) ? $location->lng->__toString() : $location->lng
		            );

		            update_user_meta($u->ID, 'address_latlng', $data_latlng);
		        }
            }

            if(!empty($data_latlng['latitude'])) {
            	$new_data->ID = $u->ID;
	            $new_data->avatar_img = null;
	            if(function_exists('get_simple_local_avatar')) {
	                $new_data->avatar_img = get_simple_local_avatar($u->ID,32);
	            }
	            $new_data->user_login = $u->user_login;
	            $new_data->display_name = $u->display_name;
	            $new_data->unsubscribeEmail = get_user_meta($u->ID, 'unsubscribeEmail', true);
	            $new_data->unsubscribeSMS = get_user_meta($u->ID, 'unsubscribeSMS', true);
	            $new_data->user_email = $u->user_email;
	            $new_data->full_name = get_user_meta($u->ID, 'first_name', true).' '.get_user_meta($u->ID, 'last_name', true);
	            $new_data->user_position = get_user_meta($u->ID, 'user_position', true);
	            $new_data->user_department = get_user_meta($u->ID, 'user_department', true);
	            $new_data->latidude = $data_latlng['latitude'];
	            $new_data->longitude = $data_latlng['longitude'];

	            $address = get_user_meta($u->ID, 'mailing_address', true);

	            if($address) {
	                $new_data->user_address = $address;
	            }
				
	            $all[] = $new_data;
            }
        }
    }

    if( !empty($_REQUEST['action']) ) {
        die(json_encode($all));
    }
    
    return $users;
}



function query_state_abbreviature($state) {
	global $wpdb;

	$query = <<<EOF
            SELECT abbreviation
            	FROM {$wpdb->prefix}states s  
                WHERE s.name LIKE '{$state}';
EOF;

    return $wpdb->get_var($query);
}