<?php

$states = GFCommon::get_us_states();


?>

<div class="wrap">

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyCdXqqURRJ8PdP16qnCVXnnKHFER1L_To8&sensor=false"></script>

   <?php /* <script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js"></script> */ ?>
   <script src="https://ahhthatsthespotmassage.com/wp-content/themes/spotmassage/js/markerclusterer.js"></script>
   
   

    <script type="text/javascript">

    /** Map render **/

    var msg_result_search = '<?php _e('It has established the following address as search center: "{address}", with a radius of {radius} mi.', $lang); ?>';

    google.maps.event.addDomListener(window, 'load', initialize_map);

    function initialize_map() {

        

      geocoder = new google.maps.Geocoder();

      var latlng = new google.maps.LatLng(39.707187,-102.304687);

      var mapOptions = {

        zoom: 4,

        center: latlng,

        mapTypeId: google.maps.MapTypeId.ROADMAP

      }

      map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

    }

    </script>

    <div class="form-ajax">

        <div class="controls">

            <select id="search_state" name="state" class="inpt-searchs">

                <option value=""><?php _e('Select a State', $lang); ?></option>

                <?php foreach($states as $code=>$st): ?>

                    <option value="<?php echo $st; ?>"><?php echo $st; ?></option>

                <?php endforeach; ?>

            </select>

            <input id="search_city" type="text" name="city" value="" placeholder="<?php echo __('City', $lang); ?>" class="inpt-searchs" />

            <input id="search_keyword" type="text" name="keyword" value="" placeholder="<?php echo __('Code Zip or Keyword', $lang); ?>" class="inpt-searchs" />
			<input id="search_first_name" type="text" name="first_name" value="" placeholder="<?php echo __('First Name', $lang); ?>" class="inpt-searchs" />
			
			<input id="search_last_name" type="text" name="last_name" value="" placeholder="<?php echo __('Last Name', $lang); ?>" class="inpt-searchs" />
			<input id="search_nickname" type="text" name="nickname" value="" placeholder="<?php echo __('Email', $lang); ?>" class="inpt-searchs" />

            <a id="search-therapists" class="button-primary" href="#"><?php _e('Search', $lang); ?></a>

            <img id="loading-search-therapist" class="ajax-loading" src="<?php echo plugins_url('/images/ajax-loader.gif',__FILE__); ?>" />
		<br />
		<p>Enter multiple value with comma separate.</p>
        </div>

        <a id="link-show-modal-settings" class="button-secondary" href="javascript:void(0);"><?php _e('Search Radius Settings...', $lang); ?></a>

        <p id="msg-result" style="display: none;"></p>

    </div>

    

    <div>

        <div id="results-therapists">

            <div class="count-results hidden-block"><?php _e('Therapists Found:', $lang); ?>&nbsp;<span class="count"></span></div>

            <p class="select-all hidden-block">

                <label><input id="select-all-therapists" class="found_therapists" type="checkbox" /><?php _e('Select all', $lang); ?></label>

                <span id="therapist-select-validation"><?php _e('Please, select at least one Therapist'); ?></span>

            </p>

            <div class="box-results hidden-block"></div>
			<p> <textarea name="assign_email_content" id="assign_email_content" placeholder="Enter message regarding location, it will add in email content" ></textarea> <p>
            <div class="btn-send hidden-block">

                <img class="ajax-loading" src="<?php echo plugins_url('/images/ajax-loader.gif',__FILE__); ?>" />

                <div id="wrapper_asgn_location">

                    <select id="asgn_location" name="location">

                        <option value=""><?php _e('Select a Location', $lang); ?></option>

                        <?php foreach($locations as $location) { 

                                $fields =  maybe_unserialize($location->data);

                                $text = empty($fields[Admin_Jobs::$field_id_zipcode]) ? sprintf(__('Location #%s', $lang), $location->ID): sprintf(__('Location #%s (ZIP Code %s)', $lang), $location->ID, $fields[Admin_Jobs::$field_id_zipcode]);

                            ?>

                            <option value="<?php echo $location->ID; ?>"><?php echo $text; ?></option>

                        <?php } ?>

                    </select>

                    <a id="view_location" href="#tab-1"><?php _e('View location', $lang); ?></a>

                    <span id="dropdown-location-message"></span>

                </div>

                <a class="button-primary" href="javascript:void(0);" onclick="customizeSMSBeforeSend();"><?php _e('Send Notification', $lang); ?></a>

            </div>

        </div>

        <div id="map-canvas"></div>

        <div class="clear"></div>

    </div>

</div>



<div id="dialog-send-sms" class="wrap modal-dialog" title="<?php _e('Customize SMS message'); ?>" style="display:none">

    <img id="ajax_loading-dialog" class="ajax-loading-dialog" src="<?php echo plugins_url('/modal-settings/images/ajax-loader-black.gif', dirname(__FILE__)); ?>" alt="" title="" style="display: none;" />

    <div id="popup-editor-notifications">
        <ul>
            <li><a href="#editortab-1"><?php _e('Email Notification', $lang); ?> </a> <input type="checkbox" name="is_send_mail_notify" id="is_send_mail_notify" value="1" style="    margin: 6px;"  /> </li>
            <li><a href="#editortab-2"><?php _e('SMS Notification', $lang); ?></a> <input type="checkbox" name="is_send_sms_notify" id="is_send_sms_notify" value="1" style="    margin: 6px;"  /> </li>
        </ul>

        <div id="editortab-1">
		Template Name: <input type="text" name="template_name" id="template_name" value="" /> 
		<input type="button" name="save_template" id="save_template" value="Save Template" onclick="save_email_template()" /> 
		
			<?php
			$list = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'email_templates '));
			
			?>
			<p> List All templates:
			<select name="template_name_option" id="template_name_option" onchange="update_editor_content(this.value)">
			<option value="">Select Template</option>
			<?php
			foreach($list as $data){
			
			?>
			<option value="<?php echo $data->id; ?>"><?php echo $data->name; ?></option>
			<?php
			}
			?>
			</select>
			
            <div id="editor_container">
			<!--<textarea name="email-invitation-content" id="email-invitation-content"></textarea>-->
			
			<?php echo wp_editor( stripcslashes(get_option('content_email_invitation', '')),'email-invitation-content', array('media_buttons' => false, 'textarea_rows' => 12) ); ?></div>
			
			<p>
			Use below shortcode to send respective details
			<br />
			{user_name}
			<br />
			{invitation_link}
			</p>
        </div>

        <div id="editortab-2">

            <textarea id="sms-message"

                rows="4" cols="50"><?php echo stripcslashes(get_option('content_sms_invitation', '')) ?></textarea>

        </div>

    </div>

    <p id="message-result" style="display: none;"></p>

</div>