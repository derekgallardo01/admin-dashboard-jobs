<?php $states = GFCommon::get_us_states(); 

  wp_enqueue_script('js-box-therapist1', plugins_url('/admin-dashboard-jobs/templates/box-details/therapist_registry.js'), array('jquery'), false, true);
  
$fields = array('state'=>'15.4','city'=>'15.3','keyword'=>'15.5','name'=>4,'last_name'=>5);

			
			$search_criteria['field_filters'] = array( );
			
			if(isset($_GET["state"]) && $_GET["state"]!=''){
				$search_criteria['field_filters'][0]['key'] = $fields["state"];
				$search_criteria['field_filters'][0]['value'] = $_GET["state"];
				$search_criteria['field_filters'][0]['operator'] = 'contains';
				
			}
			
			if(isset($_GET["city"]) && $_GET["city"]!=''){
			
				$search_criteria['field_filters'][1]['key'] = $fields["city"];
				$search_criteria['field_filters'][1]['value'] = $_GET["city"];
				$search_criteria['field_filters'][1]['operator'] = 'contains';
			}
			
			if(isset($_GET["keyword"]) && $_GET["keyword"]!=''){
			
				/* $search_criteria['field_filters'][2]['key'] = $fields["keyword"];
				$search_criteria['field_filters'][2]['value'] = $_GET["keyword"];
				$search_criteria['field_filters'][2]['operator'] = 'contains'; */
				
				$radius = get_option( 'mjobs_radio_search' );
				
				/* $data = file_get_contents('http://www.zipcodeapi.com/rest/Tj6YT3eJGd1f9fVQuyajTFGqMpqAIv7COdAIgPrX2tdbI82dHcwARd0EDrelWlXL/radius.json/'.$_GET["keyword"].'/'.$radius.'/mile');
				//echo '<pre>';
				//print_r(json_decode($data)->zip_codes);
				$zipcodess = array();
				if(!empty(json_decode($data)->zip_codes) ){
					foreach(json_decode($data)->zip_codes as $nearzip ){
					$zipcodess[] = $nearzip->zip_code;
					}
				 
				} */
				
				$data = json_decode(file_get_contents('https://www.zipwise.com/webservices/radius.php?key=8wco13x6obx2z73g&zip='.$_GET["keyword"].'&format=json&radius='.$radius));
				
				$zipcodess = array();
				if(!empty($data->results)){
					foreach($data->results as $rac){
					$zipcodess[] = $rac->zip;
					}
				}
				
				$search_criteria['field_filters'][2]['key'] = $fields["keyword"];
				$search_criteria['field_filters'][2]['value'] = $zipcodess;
				$search_criteria['field_filters'][2]['operator'] = 'in';
				
				
				
				//$search_criteria['field_filters'][] = array( 'key' => '1', 'operator' => 'not in', 'value' => array( 'Alex', 'David', 'Dana' );
				
			}
			
			if(isset($_GET["name"]) && $_GET["name"]!=''){
				
				
				/* $fullname= explode(' ',$_GET["name"]);
			
			$firstname = $fullname[0];
			$lastname = $fullname[1];
				$search_criteria['field_filters'][3]['key'] = $fields["name"];
				$search_criteria['field_filters'][3]['value'] = trim($firstname);
				$search_criteria['field_filters'][3]['operator'] = 'contains';
				
				$search_criteria['field_filters'][4]['key'] = $fields["last_name"];
				$search_criteria['field_filters'][4]['value'] = trim($firstname);
				$search_criteria['field_filters'][4]['operator'] = 'contains';
				
				
				if(!empty($lastname)){
					
					$search_criteria['field_filters'][5]['key'] = $fields["last_name"];
					$search_criteria['field_filters'][5]['value'] = trim($lastname);
					$search_criteria['field_filters'][5]['operator'] = 'contains';
					
					$search_criteria['field_filters'][6]['key'] = $fields["last_name"];
					$search_criteria['field_filters'][6]['value'] = trim($lastname);
					$search_criteria['field_filters'][6]['operator'] = 'contains';
					
				} */
				
				
				
				$fullname= $_GET["name"];
			
				$search_criteria['field_filters'][3]['key'] = $fields["name"];
				$search_criteria['field_filters'][3]['value'] = ($fullname);
				$search_criteria['field_filters'][3]['operator'] = 'contains'; 
				
				
				
				/*$search_criteria = array(
					'status'        => 'active',
					'field_filters' => array(
						'mode' => 'any',
						array('3'=>array(
							'key'   => '4',
							'value' => $_GET["name"],
							'operator' => 'contains'
						)),
						array('3'=> array(
							'key'   => '5',
							'value' => $_GET["name"],
							'operator' => 'contains'
						))
					) 
				);*/
				
			}
			
			if(isset($_GET["last_name"]) && $_GET["last_name"]!=''){
				
				$search_criteria['field_filters'][4]['key'] = $fields["last_name"];
				$search_criteria['field_filters'][4]['value'] = ($_GET["last_name"]);
				$search_criteria['field_filters'][4]['operator'] = 'contains';
					
				}
				
				
			$form_id = 3;
	
	$total_count = GFAPI::count_entries($form_ids, $search_criteria);
	
	$pagenum = (isset($_GET["pagenum"]) && $_GET["pagenum"]>0)?$_GET["pagenum"]:1;		
			
	$sorting = array('key' => 'id', 'direction' => "DESC", "is_numeric" => true);
	$numberofrecord = 20;
	
	$offset = ($pagenum-1)*$numberofrecord;
	$paging = array("offset"=> $offset, "page_size"=>$numberofrecord);
	

?>

<input type="hidden" name="therapist_search_criteria" id="therapist_search_criteria" value='<?php echo serialize($search_criteria);?>' />

<input type="hidden" name="therapist_state" id="therapist_state" value='<?php echo $_GET["state"];?>' />
<input type="hidden" name="therapist_city" id="therapist_city" value='<?php echo $_GET["city"];?>' />
<input type="hidden" name="therapist_keyword" id="therapist_keyword" value='<?php echo $_GET["keyword"];?>' />
<input type="hidden" name="therapist_name" id="therapist_name" value='<?php echo $_GET["name"];?>' />
<input type="hidden" name="therapist_records" id="therapist_records" value='<?php echo $total_count;?>' />


<div id="search-therapist" class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php _e('Therapist Registry', $lang); ?></h2>
    <div class="form-ajax psearch">
       <form method="get" name="search" action="admin.php?page=seach_therapist">
       
        <div>
            <select id="search_state" name="state" class="inpt-searchs">
                <option value=""><?php _e('Select a State', $lang); ?></option>
                <?php foreach($states as $code=>$st): ?>
                    <option value="<?php echo $st; ?>" <?php if($_GET["state"]==$st){?> selected="selected" <?php } ?> ><?php echo $st; ?></option>
                <?php endforeach; ?>
            </select>
            <input id="search_city" type="text" name="city" value="<?php echo $_GET["city"];?>" placeholder="<?php echo __('City', $lang); ?>" class="inpt-searchs" />
            <input id="search_keyword" type="text" name="keyword" value="<?php echo $_GET["keyword"];?>" placeholder="<?php echo __('Code Zip or Keyword', $lang); ?>" class="inpt-searchs" />
            <input id="search_name" type="text" name="name" value="<?php echo $_GET["name"];?>" placeholder="<?php echo __('First Name', $lang); ?>" class="inpt-searchs" />
			<input id="search_last_name" type="text" name="last_name" value="<?php echo $_GET["last_name"];?>" placeholder="<?php echo __('Last Name', $lang); ?>" class="inpt-searchs" />
            
            <?php /*?><a id="search-therapists" class="button-secondary" href="#"><?php _e('Search', $lang); ?></a><?php */?>
            <input type="submit" name="search" class="button-secondary" value="<?php _e('Search', $lang); ?>" />
            
            <input type="hidden" name="page" id="page" value="seach_therapist"  />
            <img id="loading-search-therapist" style="display: none;" src="<?php echo plugins_url('/box-details/images/ajax-loader.gif',dirname(__FILE__)); ?>" />
            <p id="show-settings-form" class="search-box"><a id="link-show-modal-settings" class="button-primary" href="javascript:void(0);"><?php _e('Change Search Radius', $lang); ?></a></p>
        </div>
        
        </form>
        <p id="msg-result" style="display: none;" class="message-info fsize16"></p>
    </div>
        <div id="map-canvas" style="display: none;"></div>
        <div id="count-results" style="display: none;" class="fsize16"><?php _e('Therapists Found:', $lang); ?>&nbsp;<span class="count"></span></div>
        <p class="btn-send">
            <a class="show-modal-message button-primary" href="javascript:void(0);"><?php _e('Send Message', $lang); ?></a>
            <a class="show-modal-message button-secondary" href="javascript:void(0);"><?php _e('Send to all users', $lang); ?></a>
        </p>
		<?php
		if(!empty($search_criteria['field_filters'])){
			
		$page_links = paginate_links( array(
				'base' => add_query_arg( 'pagenum', '%#%' ),
				'format' => '',
				'prev_text' => __( '&laquo;', 'text-domain' ),
				'next_text' => __( '&raquo;', 'text-domain' ),
				'total' => $total_count/20,
				'current' => $pagenum
			) );

			if ( $page_links ) {
				echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
			}
			
			?>
		<table id="results-therapists" class="widefat">
            <thead>
                <tr>
                	<th class="select-all manage-column column-cb check-column"><label class="lbl-all"><input type="checkbox" /> </label></th>
                    <th class="manage-column column-username"><?php _e('Username', $lang); ?></th>
                    <th class="manage-column column-name"><?php _e('Name', $lang); ?></th>
                    <th class="manage-column column-email"><?php _e('Email', $lang); ?></th>
                    <th class="manage-column"><?php _e('Company Info', $lang); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                	<th class="select-all manage-column column-cb check-column"><label class="lbl-all"><input type="checkbox" /></label></th>
                    <th class="manage-column column-username"><?php _e('Username', $lang); ?></th>
                    <th class="manage-column column-name"><?php _e('Name', $lang); ?></th>
                    <th class="manage-column column-email"><?php _e('Email', $lang); ?></th>
                    <th class="manage-column"><?php _e('Company Info', $lang); ?></th>
                </tr>
            </tfoot>
            <tbody id="list-result">
            
            <?php
			
			//echo $total_count;
			
			$leads = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );
			
			
			//print_r($leads);
			
			//echo count($leads).'ttestst';
			//echo count($leads).'ttestst';
			
			//echo '<pre>';
			//print_r($leads);
			foreach($leads as $data ){
			$userdetail = get_users(array('meta_key' => 'entry_id', 'meta_value' => $data["id"]));
			
			$substext = '';
			$unsubs = get_user_meta($userdetail[0]->data->ID, 'unsubscribeEmail', true);
				if($unsubs=='yes'){
					$substext = '( Unsubscribed for Email )';
				}
			$unsubsms = get_user_meta($userdetail[0]->data->ID, 'unsubscribeSMS', true);
				if($unsubsms=='yes'){
					$substext .= '( Unsubscribed for SMS )';
				}	
				
			?>
            <tr>
                	<td><label class="lbl-all"><input type="checkbox" name="therapists[]" id="user_<?php echo $data["id"]; ?>" class="therapist" value="<?php echo $userdetail[0]->data->ID; ?>"></label></th>
                    <td> <?php //echo $data[4]; ?></td>
                    <td> <?php echo $data[4].' '.$data[5].' '.$substext; ?> </td>
                    <td> <?php echo $data[3]; ?></td>
                    <td> <?php //echo $data[4]; ?></td>
                    
                </tr>
                
                <?php
				}
				 ?>
                 
            </tbody>
        </table>
		
		<?php
		
			if ( $page_links  ) {
				echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
			}
			}else{
				?>
				<table class="widefat">
                 <tr>
                   <td colspan="5"><?php _e('Not has been searched a therapist', $lang); ?></td>
                 </tr>
				</table>
                 <?php }
				 
			?>
        <p class="btn-send">
            <a class="show-modal-message button-primary" href="javascript:void(0);"><?php _e('Send Message', $lang); ?></a>
            <a class="show-modal-message button-secondary" href="javascript:void(0);"><?php _e('Send to all users', $lang); ?></a>
        </p>
        <div id="dialog-send-message" class="wrap modal-dialog" title="<?php _e('Send Message', $lang); ?>">
            <img id="ajax_loading" class="ajax-loading-dialog" src="<?php echo plugins_url('/modal-settings/images/ajax-loader-black.gif', dirname(__FILE__)); ?>" alt="" title="" style="display: none;" />
			
	<div id="popup-editor-notifications">
        <ul>
            <li><a href="#editortab-1"><?php _e('Email Notification', $lang); ?> </a> <input type="checkbox" name="is_send_mail_notify" id="is_send_mail_notify" value="1" style="    margin: 6px;"  /> </li>
            <li><a href="#editortab-2"><?php _e('SMS Notification', $lang); ?></a> <input type="checkbox" name="is_send_sms_notify" id="is_send_sms_notify" value="1" style="    margin: 6px;"  /> </li>
        </ul>
		
		<div id="editortab-1" >
		
            <div style="width:51%; height:auto; float:left">
                <label for="subject"><?php _e('Subject', $lang); ?><span><?php echo $required; ?></span></label>
                <input id="subject" type="text" name="subject" value="" />
            </div>
			
            <div style="width:49%; height:auto; float:left">
                <label for="emails"><?php _e('Emails', $lang); ?>&nbsp;<span><?php //echo $required; ?></span></label>
                <input id="emails" type="text" name="emails" value="" />
            </div>
			
			<div style="clear:both">Please enter multiple emails (,) Separated</div>
            <div><?php echo wp_editor( stripcslashes(get_option('content_msg_email_users', '')), 'message-content', array('media_buttons' => false, 'textarea_rows' => 4) ); ?></div>
            
		</div>
			
		<div id="editortab-2"><textarea id="sms-message" rows="4" cols="56" ><?php echo stripcslashes(get_option('content_sms_invitation', '')) ?></textarea></div>
		
		<div>
			<p>On the email content you can use some placeholders to replace them for the real content with the data of each user:</p>
			<ul>
				<li><b>{user}</b> : Replace the Full name of the user</li>
				<li><b>{userlogin}</b> : Replace the login or username of the user</li>
				<li><b>{userpass}</b> : Replace the password of the user generated in the CSV uploader</li>
			</ul>
		</div>
		<p id="message-result" style="display: none;"></p>
			
    </div>
	
	
	
        </div>
</div>