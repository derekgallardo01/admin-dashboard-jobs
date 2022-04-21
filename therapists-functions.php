<?php 

/**
 * Ajax callback when the "View Details" page for an entry is being
 */
function getAllLatLngTherapists() {

    $state = isset($_REQUEST['state'])?$_REQUEST['state']:'';
    $city = isset($_REQUEST['city'])?$_REQUEST['city']:'';
    $zip = isset($_REQUEST['keyword'])?trim($_REQUEST['keyword']):'';
    $firstname = isset($_REQUEST['first_name'])?$_REQUEST['first_name']:'';
    $lastname = isset($_REQUEST['last_name'])?$_REQUEST['last_name']:'';
    $nickname = isset($_REQUEST['nickname'])?$_REQUEST['nickname']:'';
	
	global $wpdb;
	
	/* error_reporting(E_ALL);
	ini_set('display_errors',1); */
	
	if(($zip)==''){
	$zip = (isset($_REQUEST['zip']) && $_REQUEST['zip']!='')?$_REQUEST['zip']:'';
	}
	
    $meta_keys = '';
	$metawhere = '';
    if(!empty($state)) {
    	$abbr_state = query_state_abbreviature($state);

    	$meta_keys .= $metawhere." ( {$wpdb->prefix}usermeta.meta_value LIKE '%".$abbr_state.", %' OR {$wpdb->prefix}usermeta.meta_value LIKE '%".$state."%' )";
		$metawhere = ' and ';
    }
    if(!empty($city)) {
        $meta_keys .= $metawhere." {$wpdb->prefix}usermeta.meta_value LIKE '%".$city."%'";
		$metawhere = ' and ';
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
			$emailquery = '';
			foreach($nicknamearr as $nick){
				$nicknamequery .= $wherecouse."mt3.meta_value LIKE '%".trim($nick)."%'";
				$wherecouse = ' or ';
				
				$emailquery .= " or wp_spot_users.user_email LIKE '%".trim($nick)."%'";
				
			}
			$multiwhere .= " and ((mt3.meta_key = 'nickname' and (".$nicknamequery.")) ".$emailquery." )";
		}
		
		$multijoin .= "INNER JOIN {$wpdb->prefix}usermeta AS mt3 ON ( {$wpdb->prefix}users.ID = mt3.user_id )";
    }
	
	
    if(!empty($zip)) {
		$radius = get_option( 'mjobs_radio_search' );
		$data = json_decode(file_get_contents('https://www.zipwise.com/webservices/radius.php?key=8wco13x6obx2z73g&zip='.$zip.'&format=json&radius='.$radius));
		
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
		
		
	
    /* $query = <<<EOF
	SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}users.* FROM wp_spot_users INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id ) {$multijoin} WHERE 1=1 $multiwhere group by {$wpdb->prefix}users.id ORDER BY user_login ASC;
EOF; */
	
	$query = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}users.* FROM wp_spot_users INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id ) {$multijoin} WHERE 1=1 $multiwhere group by {$wpdb->prefix}users.id ORDER BY user_login ASC limit 1000";
	
	///echo $query;die();
	
	/* error_reporting(E_ALL);
	ini_set('display_errors',1); */
	$users = $wpdb->get_results( $query );
	//print_r($users); die();
    $all = array();
	//print_r(count($users));die();
	//echo count($users);
    foreach($users as $u) {
        
		$udata = get_user_meta($u->ID);
		$data_latlng = unserialize(isset($udata["address_latlng"])?$udata["address_latlng"][0]:'');
//print_r($data_latlng);die('ll');
			$address = isset($udata["mailing_address"])?$udata["mailing_address"][0]:'';
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
            if(!empty($data_latlng['latitude'])) {}
            	$new_data->ID = $u->ID;
	            $new_data->avatar_img = null;
	            if(function_exists('get_simple_local_avatar')) {
	                //$new_data->avatar_img = get_simple_local_avatar($u->ID,32);
	            }
	            $new_data->user_login = $u->user_login;
	            $new_data->display_name = $u->display_name;
	            $new_data->unsubscribeEmail = isset($udata["unsubscribeEmail"])?$udata["unsubscribeEmail"][0]:'';
	            $new_data->unsubscribeSMS = (isset($udata["unsubscribeEmail"]) && isset($udata["unsubscribeEmail"][0]))?$udata["unsubscribeEmail"][0]:'';
	            $new_data->user_email = $u->user_email;
	            $new_data->full_name = (isset($udata["first_name"])?$udata["first_name"][0]:'').' '.(isset($udata["last_name"])?$udata["last_name"][0]:'');
	            $new_data->user_position = isset($udata["user_position"])?$udata["user_position"][0]:'';
	            $new_data->user_department = isset($udata["user_department"])?$udata["user_department"][0]:'';
	            $new_data->latidude = isset($data_latlng['latitude'])?$data_latlng['latitude']:'';
	            $new_data->longitude = isset($data_latlng['longitude'])?$data_latlng['longitude']:'';
	            

	            if($address) {
	                $new_data->user_address = $address;
	            }
				
	            $all[] = $new_data;
            
        //}
    }
//print(count($users));
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



function invoice_html( $data = array(), $post = array(), $invoicenumber ){
	
	$invoice_data = '<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Therapist Invoice</title>
    
    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif;
        color: #555;
    }
    
    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }
    
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }
    
    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }
    
    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }
    
    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }
    
    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }
    
    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }
    
    .invoice-box table tr.item.last td {
        border-bottom: none;
    }
    
    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }
        
        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }
    
    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif;
    }
    
    .rtl table {
        text-align: right;
    }
    
    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    
	</style>
	
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="src="https://ahhthatsthespotmassage.com/wp-content/uploads/2015/06/cropped-tts-newlogo.jpg"" style="width:100%; max-width:300px;">
                            </td>
                            
                            <td>
                                Invoice #: '.$invoicenumber.'<br>
                                Created: '.date('M d, Y').'<br>
                                Due: '.date("M d, Y", strtotime("-1 years")).'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                '.$data[8].'<br>
                                '.$data[9].' '.$data[51].'<br>
                                '.$data[10].'
                            </td>
                            
                            <td>
                                '.(isset($data[52]) && $data[52]!='')?$data[52]:$data[38].'<br>
                                '.$data[33].' '.$data[49].'<br>
                                '.$data[44].'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>
                    Payment Method
                </td>
                
                <td>
                    '.$post["method_payment"].'
                </td>
            </tr>
           
        </table>
			
			
			
		<table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Item Name</td>
                <td>Hourly Rate</td>
                <td>#hours</td>
                <td>Total</td>
				
            </tr>';
		$totalamt = 0;
		if(isset($data[7]) && $data[7]>0){
        for($i=1; $i<=$data[7]; $i++){
            $invoice_data .= '<tr class="item">
                <td> Massage Therapy Service</td>
                <td> '.$data[53].'</td>
                <td> '.$data[30].'</td>
                <td> '.$data[53]*$data[30].' </td>
            </tr>';
			
		$totalamt+=	$data[53]*$data[30];

        }
		
		}
		
        $invoice_data .= '
            
            <tr class="total">
                <td></td>
                <td></td>
                <td></td>
                
                <td>
                   Total: $'.number_format($totalamt,2).'
                </td>
            </tr>
			
			<tr>
                <td colspan="4"></td>
			</tr>
			
			<tr >
                <td></td>
                <td></td>
                <td></td>
                
                <td>
                   Note:
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
';
	
return $invoice_data;

}

