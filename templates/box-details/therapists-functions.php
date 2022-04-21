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
    $nickname = $_REQUEST['nickname'];
	
	global $wpdb;
	
	/* error_reporting(E_ALL);
	ini_set('display_errors',1); */
	
	if(trim($zip)==''){
	$zip = $_REQUEST['zip']!=''?$_REQUEST['zip']:'';
	}
    $meta_keys = '';
	$metawhere = '';
    if(!empty($state)) {
    	$abbr_state = query_state_abbreviature($state);

    	$meta_keys .= $metawhere." ( {$wpdb->prefix}usermeta.meta_value LIKE '%".$abbr_state.", %' OR {$wpdb->prefix}usermeta.meta_value LIKE '%".$state."%' )";
		$metawhere = ' or ';
    }
    if(!empty($city)) {
        $meta_keys .= $metawhere." {$wpdb->prefix}usermeta.meta_value LIKE '%".$city."%'";
		$metawhere = ' or ';
    }
	$multijoin = '';
	$multiwhere = '';
	
	if(!empty($firstname)) {
		$firstnamearr = explode(',',$firstname);
		if(!empty($firstnamearr)){
			$fnamequery = '';
			$wherecouse = '';
			foreach($firstnamearr as $fname){
				$fnamequery .= $wherecouse."mt1.meta_value LIKE '%".trim($fname)."%'";
				$wherecouse = ' or ';
			}
			$multiwhere .= " and (mt1.meta_key = 'first_name' and (".$fnamequery."))";
			
		}
		
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt1 ON ( {$wpdb->prefix}users.ID = mt1.user_id )";
    }
	
	if(!empty($lastname)) {
		
		$lastnamearr = explode(',',$lastname);
		if(!empty($lastnamearr)){
			$lnamequery = '';
			$wherecouse = '';
			foreach($lastnamearr as $lname){
				$lnamequery .= $wherecouse."mt2.meta_value LIKE '%".trim($lname)."%'";
				$wherecouse = ' or ';
			}
			$multiwhere .= " and (mt2.meta_key = 'last_name' and (".$lnamequery."))";
			
		}
		
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt2 ON ( {$wpdb->prefix}users.ID = mt2.user_id )";
    }
	
	if(!empty($nickname)) {
		$nicknamearr = explode(',',$nickname);
		if(!empty($nicknamearr)){
			$nicknamequery = '';
			$wherecouse = '';
			foreach($nicknamearr as $nick){
				$nicknamequery .= $wherecouse."mt3.meta_value LIKE '%".trim($nick)."%'";
				$wherecouse = ' or ';
			}
			$multiwhere .= " and (mt3.meta_key = 'nickname' and (".$nicknamequery."))";
		}
		
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt3 ON ( {$wpdb->prefix}users.ID = mt3.user_id )";
    }
	
	
    if(!empty($zip)) {
		$radius = get_option( 'mjobs_radio_search' );
		
		//$data = json_decode(file_get_contents('https://www.zipwise.com/webservices/radius.php?key=8wco13x6obx2z73g&zip='.$zip.'&format=json&radius='.$radius));
		$zipcodewhere = '';		
		$zipcodess = '';
		$zipscode = array();
	
		$users2 = array();
		//print_r($data);die();
		$ziparr = array();
		if(!empty($data->results)){
			foreach($data->results as $rac){
			$ziparr[] = "{$wpdb->prefix}usermeta.meta_value LIKE '%".$rac->zip."%'";
			
			
		}}
		if(!empty($ziparr)){
			$meta_keys .= $metawhere.' '.implode(' or ',$ziparr);
		}
	}
		if($meta_keys!=''){
			#$meta_address = implode(" AND ", $meta_keys);
			$meta_address =  $meta_keys;
			
			$multiwhere .= "and ({$wpdb->prefix}usermeta.meta_key = 'mailing_address' and ($meta_address) )";
		}
		
		
	
    $query = <<<EOF
	SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}users.* FROM wp_spot_users INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id ) {$multijoin} WHERE 1=1 $multiwhere group by {$wpdb->prefix}users.id ORDER BY user_login ASC;
EOF;
	
	echo $query;die();
	
	/* error_reporting(E_ALL);
	ini_set('display_errors',1); */
	$users = $wpdb->get_results( $query );

    $all = array();
	//print_r(count($users));die();
		
    foreach($users as $u) {
        
		$udata = get_user_meta($u->ID);
		$data_latlng = $udata["address_latlng"][0];
//print_r();die();
			$address = $udata["mailing_address"][0];
            $new_data = new stdClass;
if( empty($data_latlng) ) {
            if(empty($data_latlng['latitude']) ) {
            if(!empty($address) ) {
            	$apiKey = 'AIzaSyDf_JSmGHC4OWU6ABOxvyrci8sBx0qMqEQ';
				$url_base = "https://maps.googleapis.com/maps/api/geocode/xml?sensor=false&oe=UTF-8&key=".$apiKey."&address=";
				$context = stream_context_create( array('http' => array( 'timeout' => 10 ) ) );

				$mailing_address = $udata["mailing_address"][0];
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
            }
}
            if(!empty($data_latlng['latitude'])) {
            	$new_data->ID = $u->ID;
	            $new_data->avatar_img = null;
	            if(function_exists('get_simple_local_avatar')) {
	                //$new_data->avatar_img = get_simple_local_avatar($u->ID,32);
	            }
	            $new_data->user_login = $u->user_login;
	            $new_data->display_name = $u->display_name;
	            $new_data->unsubscribeEmail = $udata["unsubscribeEmail"][0];
	            $new_data->unsubscribeSMS = $udata["unsubscribeSMS"][0];
	            $new_data->user_email = $u->user_email;
	            $new_data->full_name = $udata["first_name"][0].' '.$udata["last_name"][0];
	            $new_data->user_position = $udata["user_position"][0];
	            $new_data->user_department = $udata["user_department"][0];
	            $new_data->latidude = $data_latlng['latitude'];
	            $new_data->longitude = $data_latlng['longitude'];

	            

	            if($address) {
	                $new_data->user_address = $address;
	            }
				
	            $all[] = $new_data;
            }
        //}
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