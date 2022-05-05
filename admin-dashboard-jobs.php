<?php

/*

  Plugin Name: Manage Jobs Massage

  Plugin URI: http://smadit.com

  Description: 

  Version: 1.0.0

  Author: Manuel Lopez Lara - smadit.com

  Author URI: http://smadit.com/

 */


define('ADMJOBS_DIR', dirname(__FILE__));


define("ENCRYPTION_KEY", "kshfkjdsgfjkhdsgj");

include_once('custom_fnc.php');

	/**
	 * Returns an encrypted & utf8-encoded
	 */
	function syn_encrypt($text,$salt)
    {  
		$salt = 'ahhthe';
		return openssl_encrypt($text,"AES-128-ECB",$salt);

    }

	/**
	 * Returns decrypted original string
	 */
	function syn_decrypt($text,$salt)
    {  
		$salt = 'ahhthe';
        return openssl_decrypt($text,"AES-128-ECB",$salt);
    }

class Admin_Jobs {

    

    const LANG = 'admjobs';

    // ID Secundary form

    static $job_form_id = null;

    static $field_id_locations = null;

	static $addscriptemail = 0;

    static $confirm_events = null;



    static $symbol_price = null;



    static $field_id_dates_and_events =  50;



    static $field_id_number_therapist = 7;

    

    static $field_id_phone = 34;
    static $field_id_onsite_person_email = 44;



    static $field_id_aproximate_total_of = 30;



    static $field_id_corporate_massage = '36.1';



    static $field_id_participants = 37;



    static $field_id_refer_friend = 12;



    static $field_id_first_name = 39;



    static $field_id_last_name = 49;



    static $field_id_email = 3;



    static $field_id_time_zone = 28;



    static $field_id_zipcode = 51;



    static $color_link_email = 'rgb(89, 126, 153)';

    

    

    var $number_locations_per_page = null;



    var $pag_count_after = null;

    var $pag_count_before = null;



    var $enabled_pagination_ajax = null;

    

    var $service_review_form_id = null;

    var $need_quote_form_id = null;



    function __construct() {
		
        $this->init();
		
    }

    function init() {

		/*$from = '+19897419543';
		$to = '919610730117';
		$sms = 'this is twilio sms';
		echo 'sasahfkjaskfhsakjfhs';
		$result = ThatsSpotMassage_Twilio::send_sms($from, $to, $sms);
		print_r($result);
		die();*/
        //jquery- ui-dialog
		add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'load_front_scripts'));
		
		
        /**
         * Add Twilio Class
         * */

        require_once(ADMJOBS_DIR.'/class.twilio.php');

        /**
         * Add Freshbooks Class

         * */

        require_once(ADMJOBS_DIR.'/class.freshbooks.php');

	

        /**

         * Add Shortcode List Invoices

         * */

        require_once(ADMJOBS_DIR.'/templates/sc-listinvoice-freshbooks/sc-listinvoice-freshbooks.php');



        /**

         * Search users radio js plugin

         * */

        require_once(ADMJOBS_DIR.'/apis/search-users/search-users.php');



        /**

         * Customer Users Registry page

         * */

        require_once(ADMJOBS_DIR.'/templates/customer-registry/customer-registry.php');



        /** static helper **/

        require_once(ADMJOBS_DIR.'/static-helper.php');





        /** Therapists Functions **/

        require_once(ADMJOBS_DIR.'/therapists-functions.php');


		if(isset($_GET["gopi"]) && $_GET["gopi"]!=''){
			global $current_user;
			
			$user_roles = $current_user->roles;
			$user_role = array_shift($user_roles);
			print_r($user_role);
			
		}
        
	
        self::$symbol_price = '$';



        self::$field_id_number_therapist = 7;

        

        //34 is the field id of the phone

        self::$field_id_phone = 34;



        //30 is the field id of the aproximate total # of

        self::$field_id_aproximate_total_of = 30;

        

        self::$field_id_first_name = 33;



        self::$field_id_last_name = 49;



        self::$field_id_time_zone = 28;



        self::$field_id_zipcode = 51;



        //33 is the field id of the first and last name

        //$this->field_id_first_last_name = 33;





        //36.1 is the Field id of the corporate massages

        self::$field_id_corporate_massage = '36.1';



        //37 is the Field id of the emails for the participants in corporate massages

        self::$field_id_participants = 37;
        self::$field_id_onsite_person_email = 44;

        

        // 12 is the field id of the rerefed friend in the form employment application

        self::$field_id_refer_friend = 12;



        //

        $this->number_locations_per_page = 4;

        

        //

        $this->pag_count_before = 2;

        

        //

        $this->pag_count_after = 2;

        

        $this->enabled_pagination_ajax = false;

        

        $this->service_review_form_id = 5;//Form: http://spotmassage.smadit.com/service-review/

		

        $this->need_quote_form_id = 1;//Form: http://spotmassage.smadit.com/



        if(empty(self::$job_form_id)) {

            $this->updateFormId();

        }

        $isFormDefined = !empty(self::$job_form_id);


		add_action('wp_ajax_addmorelocation', array('AJ_Location', 'addmorelocation'));
		add_action('wp_ajax_updatelocation', array('AJ_Location', 'updatelocation'));
		
        add_filter('gform_confirmation', array($this,'filter_submit_confirmation'), 11, 4);

        add_filter('gform_pre_render', array($this,'gform_pre_render'));

        add_filter('gform_submit_button', array($this, 'form_submit_button'), 10, 2);



        //jqueryui-admin-style

        //wp_register_style('jqueryui-admin-style','//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/smoothness/jquery-ui.css');

        

        

        /** Action Register Therapist **/

        add_action('gform_user_registered', array($this, 'sm_registration_save'),10,4);

        add_action('gform_entry_created', array($this, 'gform_entry_created'),10,2);

        

        add_action("gform_after_submission", array($this, 'gform_finalize_entry'), 10, 2);
        add_action("gform_before_submission", array($this, 'gform_prepare_entry'), 10, 2);
        

        //edit entry from admin dashboard

        add_action("gform_after_update_entry", array($this, 'gform_afterupdate_entry'), 10, 2);



        

        add_action('admin_menu', array($this, 'register_submenu_page'));

        add_action('wp_ajax_save_settings', array($this, 'save_settings'));

        add_action('wp_ajax_nopriv_save_settings', array($this, 'save_settings'));
		
		add_action('wp_ajax_editlocation', array($this, 'editlocation'));
		add_action('wp_ajax_nopriv_editlocation', array($this, 'editlocation'));
        

        add_filter('gform_replace_merge_tags', array($this, 'add_tag_encrypt_entry_id'), 10, 4);

        add_action('sm_secundary_form_page', array($this, 'validate_access_page'));

        

        // filter content to replace tag {invoice_pdf}

        add_filter( 'the_content', array($this, 'content_filter_link_appointment'));

        

        /**

         * Add Box in the Entrys Gravity Form

         * */

        if( $isFormDefined && isset($_GET['page']) && $_GET['page'] == 'gf_entries' 

            && isset($_GET['id']) && $_GET['id'] == self::$job_form_id) {

            add_action('gform_entry_detail_content_after', array($this, 'add_custom_box'), 10, 2);

        }


		if(isset($_GET["delete_invited"])){
			 global $wpdb;
			$invited_id = $_GET["invited_id"];
			$location_id = $_GET["location_id"];
			$lid = $_GET["lid"];
			
			$sql_loc = "SELECT * FROM ".$wpdb->prefix."job_location WHERE ID=".$location_id;

            $locations = $wpdb->get_results($sql_loc);
			
			$users_inviteds = maybe_unserialize($locations[0]->users_invited);
			unset($users_inviteds[$invited_id]);
            $status_accept = maybe_unserialize($locations[0]->accept_job);
			unset($status_accept[$invited_id]);

			$wpdb->query("update ".$wpdb->prefix."job_location set users_invited = '".serialize($users_inviteds)."', accept_job = '".serialize($status_accept)."' WHERE ID=".$location_id);
			header('location:admin.php?page=gf_entries&view=entry&id=2&lid='.$lid.'&filter&paged=1&pos=0');

            die();
			
		}
		
		if(isset($_GET["location_listing"]) && isset($_GET["key"])){
			include_once(ABSPATH . 'wp-includes/pluggable.php');  
			include_once(ABSPATH . 'wp-includes/class-wp-rewrite.php');  
			global $wpdb;
			
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			
			$username = base64_decode($_GET["key"]);

			if($user=get_user_by('login',$username)){

				clean_user_cache($user->ID);

				wp_clear_auth_cookie();
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID , true, false);

				update_user_caches($user);

				if(is_user_logged_in()){
					
					$current_url = sprintf(
						'%s://%s/%s',
						isset($_SERVER['HTTPS']) ? 'https' : 'http',
						$_SERVER['HTTP_HOST'],
						$_SERVER['REQUEST_URI']
					);
					$new_url = preg_replace('/&?key=[^&]*/', '', $current_url);
					wp_safe_redirect( $new_url );
					exit;
					$post = get_page_by_path('my-events', OBJECT, 'page');
					
					$event_page_link = get_permalink($post->ID);
		  
					$job_url = $event_page_link.'/?location_listing='.$_GET["location_listing"];
					wp_safe_redirect( $job_url );
					exit;
				}
			}
						
		}

        /**

         * Add text editor to the notes in the Entrys Gravity Form

         * */

        if( $isFormDefined && isset($_GET['page']) && $_GET['page'] == 'gf_entries' && isset($_GET['id']) ) {

            add_action('gform_entry_detail_content_after', array($this, 'add_notes_texteditor'), 10, 2);
			
			add_action("gform_admin_pre_render_1", function(){
				add_action('admin_print_footer_scripts',  array($this, 'load_inline_admin_script'), 10, 2);
				
			});
				
				add_action("gform_admin_pre_render_2", function(){
				add_action('admin_print_footer_scripts',  array($this, 'load_inline_admin_script'), 10, 2);
				
			});
			
        }






        

        if(!is_admin()) {

            add_action('init', array($this, 'start_init'),5);

            add_action('wp', array($this, 'validate_access_page_payment'),10);

        }

        add_action('wp_ajax_assign_therapist', array($this, 'assign_therapist'));
        add_action('wp_ajax_nopriv_assign_therapist', array($this, 'assign_therapist'));
		
		
		add_action('wp_ajax_save_email_template', array($this, 'save_email_template'));
        add_action('wp_ajax_nopriv_save_email_template', array($this, 'save_email_template'));
		
		add_action('wp_ajax_get_email_template', array($this, 'get_email_template'));
        add_action('wp_ajax_nopriv_get_email_template', array($this, 'get_email_template'));

        add_filter('get_user_option_default_password_nag', array($this, 'stop_password_nag'), 10);

        

        add_action('wp_ajax_therapist_action_job', array($this, 'therapist_action_job'));
        add_action('wp_ajax_nopriv_therapist_action_job', array($this, 'therapist_action_job'));

        

        

        add_action('wp_ajax_modal_view_lead', array($this, 'modal_view_lead'));
        add_action('wp_ajax_nopriv_modal_view_lead', array($this, 'modal_view_lead'));

        

        add_action('gform_after_save_form', array($this, 'gf_after_save_form'));

        

        /**

         * Hooks for the secondary form (Submit-Job Form)

         **/

        SpotMassagesHelper::secondaryFormHooks();





        /**

         * Frontend Events pages for public, massage client,  and Therapist user

         **/

        add_shortcode('events-jobs', array($this, 'render_events'));

        /**

         * Actions Front end section therapist (my jobs)

         * */

        $pagination_ajax_action = 'pagination_jobs';

		
        add_action('wp_ajax_'.$pagination_ajax_action, array($this, 'pagination_jobs'));

        add_action('wp_ajax_nopriv_'.$pagination_ajax_action, array($this, 'pagination_jobs'));



        

        /**

         * Actions Front end section user (my events)

         * */        

        $pagination_ajax_action = 'pagination_events';

        add_action('wp_ajax_'.$pagination_ajax_action, array($this, 'func_pagination_events'));

        add_action('wp_ajax_nopriv_'.$pagination_ajax_action, array($this, 'func_pagination_events'));

        

        /**

         * Function tab 2

         * */

        add_action('wp_ajax_send_invoice_to_client', array($this, 'send_invoice_to_client'));

        add_action('wp_ajax_nopriv_send_invoice_to_client', array($this, 'send_invoice_to_client'));



        add_action('wp_ajax_generated_invoices', array($this, 'generated_invoices'));

        add_action('wp_ajax_nopriv_generated_invoices', array($this, 'generated_invoices'));

       

        /**

         * Shortcode que renderiza los eventos(masages) para confirmar que se les asignaron a un usuario tipo: Client-employee

         * */

        add_shortcode('confirmation-events', array($this, 'render_confirmation_events_client_employee'));

        

        /**

         * Actions Front end section user (confimartion events) and pagination ajax

         * */        

        $pagination_ajax_action = 'pagination_confirmation_events';


        add_action('wp_ajax_'.$pagination_ajax_action, array($this, 'func_pagination_confirmation_events'));

        add_action('wp_ajax_nopriv_'.$pagination_ajax_action, array($this, 'func_pagination_confirmation_events'));

        

        //action para la peticion ajax donde el usuario tipo client-employee confirma el evento

        add_action('wp_ajax_confirm_event', array($this, 'ajax_confirm_event'));

        add_action('wp_ajax_nopriv_confirm_event', array($this, 'func_pagination_confirmation_events'));



        add_action('wp_ajax_ajax_update_profile', array($this, 'ajax_update_profile'));

        add_action('wp_ajax_nopriv_ajax_update_profile', array($this, 'ajax_update_profile'));



        /**

         * Action to the function that send the messages to especifiqued users

         *******/

        add_action('wp_ajax_send_message_users', array($this, 'ajax_send_message_users'));

        add_action('wp_ajax_nopriv_send_message_users', array($this, 'ajax_send_message_users'));



        // therapists.functions.php

        //add_action('wp_ajax_alltherapists', array($this, 'getAllLatLngTherapists'));

        //add_action('wp_ajax_nopriv_alltherapists', array($this, 'getAllLatLngTherapists'));

		$args = array(
			'meta_query' => array(
				'relation' => 'AND',
					array(
						'key'     => 'mailing_address',
						'value'   => '302031',
						 'compare' => 'LIKE'
					),
					array(
						'key'     => 'first_name',
						'value'   => 'Vikram',
						'compare' => 'LIKE' // any value within that range of 30-60
					)
			)
		);
 
		/* $user_query = new WP_User_Query( $args );
		print_r($user_query->get_results());
		echo '<br />'.$user_query->request;
		die('sadsad'); */ 

		add_action('wp_ajax_alltherapists', 'getAllLatLngTherapists');

		add_action('wp_ajax_nopriv_alltherapists', 'getAllLatLngTherapists');



        add_action('wp_ajax_getlocationdates', array('AJ_Location', 'getDatesEventByLocation'));

        add_action('wp_ajax_nopriv_getlocationdates', array('AJ_Location', 'getDatesEventByLocation'));



        add_filter('gform_get_field_value', array($this,'fvalue'),10,3);



        /** Action to delete the events if any user is deleted **/

        add_action( 'delete_user', array($this,'delete_therapist_user') );
		
		
		
		////update locations status and bulk delete as well
		//print_r($_POST);die();
		global $wpdb;
		
		if(isset($_GET["archive_all_location"]) && !empty($_GET["archive_all_location"]) && $_GET["archive_all_location"]=='yes'){
			
			$sql = "UPDATE ".$wpdb->prefix."job_location set status = 'archive' WHERE status = '' and lead_id=".$_GET["lid"];
			$wpdb->query( $sql );
			
		echo("<script>location.href = 'admin.php?page=gf_entries&view=entry&id=2&lid=".$_GET["lid"]."&dir=DESC&filter&paged=1&pos=3';</script>");
		die();
		//wp_redirect('admin.php?page=gf_entries&view=entry&id=2&lid='.$_GET["lid"].'&dir=DESC&filter&paged=1&pos=1&field_id&operator');
			//return;
		}
		
		if(isset($_GET["archive_one_location"]) && !empty($_GET["archive_one_location"]) && $_GET["archive_one_location"]=='yes'){
			
			$sql = "UPDATE ".$wpdb->prefix."job_location set status = 'archive' WHERE id=".$_GET["loid"];
			$wpdb->query( $sql );
			
		}
		if(isset($_GET["delete_location"]) && !empty($_GET["delete_location"]) && $_GET["delete_location"]=='yes'){
			
			$sql = "UPDATE ".$wpdb->prefix."job_location set status = 'delete' WHERE id=".$_GET["loid"];
			$wpdb->query( $sql );
			
		}
		
		
		if(isset($_POST["submit_status"]) && !empty($_POST["locationnumber"])){
			foreach($_POST["locationnumber"] as $id=>$status){
			
			$sql = "UPDATE ".$wpdb->prefix."job_location set status = '".$_POST["archive_note"]."' WHERE ID=".$id;
			$wpdb->query( $sql );
			
			/* if(isset($_POST["archive_note"]) && $_POST["archive_note"]=='activate'){
				$sql = "DELETE FROM ".$wpdb->prefix."location_date_event WHERE location_id=".$loc;
				$wpdb->query( $sql );
				
			} */
			
			
			
				
			}
			
		}
		

    }
	
	

	function load_inline_admin_script(){
	
		 $isFormDefined = !empty(self::$job_form_id);
		if( $isFormDefined && isset($_GET['page']) && $_GET['page'] == 'gf_entries' && isset($_GET['id']) ) {
			$this->footer_emailnotes_change();
			
		}
		
	}
	
	public function load_front_scripts() {
		$pagination_ajax_action = 'pagination_invoices';
		wp_register_script('panel-therapist-js_front', plugins_url('/templates/panel-therapist/panel_therapist.js', __FILE__), array(), '1.0' ); 
		
		wp_localize_script( 'panel-therapist-js_front', 'pTpst', array('attrs' => $_POST["attrs"], 'pgAjaxAction' => $pagination_ajax_action) );
		
		wp_enqueue_script( 'panel-therapist-js_front' );
	}
	

	public function load_admin_scripts() {
		
		
		$deps = array('jquery', 'spotmassage-base-js');
		$pagination_ajax_action = 'pagination_invoices';
		wp_register_style('jqueryui-local-style',plugins_url('/jquery-ui/css/jquery.ui.all.min.css', __FILE__));

		wp_enqueue_style('jqueryui-local-style');
		wp_register_script('jquery-ui-all', plugins_url('/jquery-ui/jquery-ui-1.9.2.custom.min.js', __FILE__), array('jquery'), false, true);
		wp_enqueue_script('jquery-ui-all');
		
        wp_enqueue_script('js-search-therapist', plugins_url('/templates/search-therapist/search_therapist.js', __FILE__), array('jquery', 'jquery-ui-all'), false, true);
		
        wp_register_script('sc-listinvoices-js', plugins_url('/sc_invoices.js', __FILE__), $deps, false, true);
        wp_localize_script( 'sc-listinvoices-js', 'scLInv', array('pgAjaxAction' => $pagination_ajax_action) );
		
		
		wp_register_script('panel-therapist-js', plugins_url('/templates/panel-therapist/panel_therapist.js', __FILE__), $deps, false, true);
		wp_enqueue_script('panel-therapist-js');
        wp_localize_script( 'panel-therapist-js', 'pTpst', array('pgAjaxAction' => $pagination_ajax_action) );
		
		$pagination_ajax_action = 'pagination_events';

        wp_register_script('events-user-js', plugins_url('/templates/events-user/events_user.js', __FILE__), $deps, false, true);

        wp_localize_script( 'events-user-js', 'evUser', array('pgAjaxAction' => $pagination_ajax_action) );
		
		
		$pagination_ajax_action = 'pagination_confirmation_events';

        wp_register_script('confirmation-events-js', plugins_url('/templates/confirmation-events/confirmation_events.js', __FILE__), $deps, false, true);

        wp_localize_script( 'confirmation-events-js', 'evUser', array('pgAjaxAction' => $pagination_ajax_action) );
		
		wp_register_style('admin-jobs-style',plugins_url('/admin.css', __FILE__));
		wp_enqueue_style('admin-jobs-style');
		wp_enqueue_script('admin-jobs-js', plugins_url('/templates/main-panel/manage_jobs_script.js', __FILE__), array(),false,true);
		$vars_script = array(
			'isFormDefined' => isset($isFormDefined)?$isFormDefined:'',
			'url_admin_home' => admin_url()
		);
		wp_localize_script( 'admin-jobs-js', 'adminJob', $vars_script );
        if(is_admin() && isset($_GET['page']) && ($_GET['page'] == 'admin_jobs' or $_GET['page'] == 'view_location' or $_GET['page'] == 'archive_location' or $_GET['page'] == 'view_archive_notes' or $_GET['page'] == 'edit_location' or $_GET['page'] == 'edit_note'   )) {

        }
		
		wp_enqueue_script('js-box-therapist', plugins_url('/templates/box-details/search_therapist_script.js', __FILE__), array('jquery'), false, true);

        $vars_script = array(

            'radius' => get_option('mjobs_radio_search', 2),

            //'therapists' => $all_therapists,

            'ABSPATH' => ABSPATH

        );
        
		wp_localize_script( 'js-box-therapist', 'mjobs', $vars_script );
		
	}
	

    /**

     * Esta funcion se creo para cambiar los valores que estan en formato json

     * y que para el caso de este proyecto corresponde a un listado de fechas.

     ****/

    public function fvalue($value, $lead, $field) {

        $nVal = null;

        if( is_serialized($value) ) {

            $nVal = maybe_unserialize($value);

            if(is_array($nVal)) {

                foreach($nVal as &$val) {

                    if(is_array($val)) {

                        foreach($val as &$v) {

                            if( isJson($v) ) {

                                /*

                                $nv = json_decode($v);

                                $new_val = '';

                                if(is_array($nv)) {

                                    foreach($nv as $item) {



                                        $d = strtotime($item->year.'-'.$item->month.'-'.$item->day);



                                        $new_val .= date('F d, Y',$d).' '.$item->stime.' - '.$item->etime.'<br/>';

                                    }

                                }

                                

                                $v = $new_val;*/

                                $v = '<a onclick=\'blViewDatesEvents(this,'.json_encode($v).');\' class="button-secondary" href="javascript:void(0);">'.__('View dates',self::LANG).'</a>';

                            }

                        }

                    }

                }

            }

            $value = maybe_serialize($nVal);

        }

        return $value;

    }





    function sendSMS($to,$sms) {

        //wp_mail('julianchoss@gmail.com', 'sms test', 'Message send to '.var_export($to, true)." \n with content: ".var_export($sms, true));

        if( empty($to) ) {

            file_put_contents(ADMJOBS_DIR."/logs-sms.log",var_export($to,true).':not send:'.date('D, d M Y H:i:s')."\n",FILE_APPEND);

            return false;

        }

        //$from = '+19897419543';
		//'+15005550006'; - old number

        //$to = '+573004803333';
		//$from = '+19549474746'; // taken from derek account numbet
        $from = '+13312003051';
        //$from = '+18654091381';
		
		$to = '+1'.substr($to, -10);
        $result = ThatsSpotMassage_Twilio::send_sms($from, $to, $sms);
		/*print_r($result);
        die();*/

        if( !empty($result) ) {

            file_put_contents(ADMJOBS_DIR."/logs-sms.log",$to.':'.$result->sid.':'.$result->date_created."\n",FILE_APPEND);

        } else {

            file_put_contents(ADMJOBS_DIR."/logs-sms.log",$to.':not response:'.date('D, d M Y H:i:s')."\n",FILE_APPEND);

        }

        return $result;

    }



    /**

     * Function to send a message to a list of users, this function is used into via ajax request in the seccion "Therapist Registry" (http://spotmassage.smadit.com/wp-admin/admin.php?page=seach_therapist) 

     **/

    function ajax_send_message_users() {

		
        if( (is_array($_POST['users']) && count($_POST['users']) > 0 ) || strlen($_POST['users'])>5 || !empty($_POST["emails"]) ) {
            if($_POST['is_send_mail']==1 && empty($_POST['subject'])) {
                die(json_encode(array('status' => 'notsend', 'message' => __('The content subject is empty', self::LANG))));
            }

            if(empty($_POST['message'])) {

                die(json_encode(array('status' => 'notsend', 'message' => __('The content message is empty', self::LANG))));

            }

            update_option('content_msg_email_users', $_POST['message']);
            $result = array('status' => 'OK', 'message' => __('The messages has been sent with success', self::LANG));

            $noSendUsers = array();
			//$userslectdata = $_POST['users'];
			$userdataSelect = $_POST['users'];
			$userslectdata = array();
			foreach($userdataSelect as $udata){
				
				$udatas = get_userdata($udata["ID"]);
				
				//$umdata = get_user_meta($udatas->data->ID, 'unsubscribeEmail',true);
				
				
				$userslectdata[] = $udatas;
				
			}
			
			//print_r($userslectdata);
			//print_r($userslectdata); die();
			//print_r($userslectdata);
			if(!empty($_POST["emails"]) ){
				
				wp_mail(explode(',', $_POST["emails"]) , $_POST['subject'], $_POST['message'].'<a href="'.site_url().'/unsubscribe.php?uid='.syn_encrypt($user->ID, ENCRYPTION_KEY).'&unsubscribefield=unsubscribeEmail">Click here</a> to safely unsubscribe from '.get_bloginfo( 'name' ) );
				
				$result['nosendusers'] = count(explode(',', $_POST["emails"]));

                $result['message'] = __('The messages has been sent with success to filled emails', self::LANG);
				
			}
			
			if(1){
			
			if(isset($_POST['type']) && $_POST['type']=='all'){
			
				$search_criteria = unserialize(stripcslashes($_POST["users"]));
				
				$form_ids = $form_id = 3;//$form_ids;
				$listarr = array();
				$total_count = GFAPI::count_entries($form_ids, $search_criteria);
				$sorting = array('key' => 'id', 'direction' => "DESC", "is_numeric" => true);
				$paging = array("offset"=> 0, "page_size"=>$total_count);
			
				$leads = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );
				//print_r($leads);
				foreach($leads as $leaddata){
					
					$listarr[]["ID"] = $leaddata["id"];
				}
				
				$userslectdata	= (array)$listarr;
				
				if($_POST['therapist_records']!=count($userslectdata)){
					die(json_encode(array('status' => 'notsend', 'message' => __('The list is not correct', self::LANG))));

				}
				
			}
			
			///print_r($userslectdata);
			
			//die();
            foreach($userslectdata as $u) {

                $user = is_array($u) ? json_decode(json_encode($u), FALSE) : $u;
				$user = $user->data;
				//print_r($user);die();
                $email = '';

				$empty_email = !empty($user->user_email)?$user->user_email:'';
				
                if( $empty_email && !empty($user->ID)) {

                    $user = get_user_by('id', $user->ID);

                    $email = $user->user_email;
					
                    $user->full_name = $user->first_name ? $user->first_name.($user->last_name ? ' '.$user->last_name : '') : null;

                } elseif( !$empty_email ) {

                    $email = $user->user_email;

                } else {
                    $noSendUsers[] = json_encode($user);
                    continue;
					
                }
				
				$unsubs = get_user_meta($user->ID, 'unsubscribeEmail', true);
				$unsubsms = get_user_meta($user->ID, 'unsubscribeSMS', true);
				
				
                $name = empty($user->full_name) ? ( empty($user->display_name) ? $email : $user->display_name) : $user->full_name;

                //dev:lamprea: Added placeholders for userlogina dna userpass
                $userlogin = $user->user_login;

                $userpass = get_user_meta($user->ID, 'generated_random', true);

                $content = stripcslashes( str_replace(array('{user}', '{userlogin}', '{userpass}'), array($name, $userlogin, $userpass), $_POST['message']) );
				
				$content .= '<a href="'.site_url().'/unsubscribe.php?uid='.syn_encrypt($user->ID, ENCRYPTION_KEY).'&unsubscribefield=unsubscribeEmail">Click here</a> to safely unsubscribe from '.get_bloginfo( 'name' );
	
				$subject = str_replace('{user}', $name, $_POST['subject']);

//	print_r($content);
				$sendsomething = 0;
				if($_POST['is_send_mail']==1 && $unsubs!='yes') {
				$emails = array();
				if( !empty($_POST["emails"] ) && $_POST["emails"]!='' )
				//$emails = explode(',' , $_POST["emails"] );

				$emails[] = $email;
				/* echo $email;
				print_r($emails);
				die(); */
				
                $isSend = wp_mail($email, $subject, $content);
				
				$sendsomething = 1;
				
				
                if(!$isSend) {

                    $noSendUsers[] = json_encode($user);

                }
				}
				$message_sms = $_POST['txt_sms'];//.' Open this url for unsubscribe '.site_url().'/unsubscribe.php?uid='.syn_encrypt($user->ID, ENCRYPTION_KEY).'&unsubscribefield=unsubscribeSMS';
				update_option('content_sms_invitation', $message_sms);
				$message_sms = str_replace(array('{user_name}','{user}', '{userlogin}', '{userpass}'), array($name,$name, $userlogin, $userpass), $message_sms);
				
				 $to = get_user_meta($user->ID, 'mobile_phone_number', true);
				
				 if($_POST['is_send_sms']==1 && $unsubsms!='yes') {
					 

                    if( !empty($to) && substr($to,0,1) != '+') {

                        $to = '+1'.$to;

                    }
					//echo $to;
					//echo $message_sms;die();
                    $this->sendSMS($to,$message_sms);    
					$sendsomething = 1;
                }
				
				
				
				

            }
		}
		
            if( count($noSendUsers) > 0) {

                $result['nosendusers'] = $noSendUsers;

                $result['message'] = __('The messages has been sent with success, but could not send the email to all users', self::LANG);

            }

            if($sendsomething == 0){
				die(json_encode(array('status' => 'notsend', 'message' => __('Please select atleast one checkbox, email or sms', self::LANG))));
			}else{
				die(json_encode($result));
			}

        } else {

            die(json_encode(array('status' => 'notsend', 'message' => __('The list of users is empty', self::LANG))));

        }



    }



    /**

     * Function to filter de confirmation message to form home

     ******/

    function filter_submit_confirmation($confirmation, $form, $lead, $ajax) {

        if(!$ajax && $form['id'] == $this->need_quote_form_id && !is_array($confirmation) ) {

            $this->set_session_var('message_needquote_form', $confirmation );

            $confirmation = array('redirect' => site_url('?#gform_widget-2'));

        }

        return $confirmation;

    }

    /**

     * Function to clean the form rendering, this function is implemented to the function call "filter_submit_confirmation"

     ******/

    function gform_pre_render($form) {

        if($form['id'] == $this->need_quote_form_id) {

            $msg = $this->get_session_var('message_needquote_form', '');

            if($msg) {

                $form['fields'] = array();

            }

        }

        return $form;

    }



    /**

     * Function to change the button rendering submit to the form by the message confirmation

     *******/

    function form_submit_button($button, $form){

        if($form['id'] == $this->need_quote_form_id) {

            $msg = $this->get_session_var('message_needquote_form');

            if($msg) {

                $this->delete_session_var('message_needquote_form');

                return $msg;

            }

        } elseif($form['id'] == $this->service_review_form_id) {

            return $button.'&nbsp;'.__('or',self::LANG).'&nbsp;<input type="submit" class="btn btn-success" name="got_to_page_'.$form['id'].'" value="'.__('Go to pay',self::LANG).'">';

        }

        return $button;

    }



    

    function render_events($attrs) {

        $default = array(

    	   'name' => 'Event',

           'plural_name' => 'Events',

           'public' => 'false',

           'enabled_pagination_ajax' => var_export($this->enabled_pagination_ajax, true)

        );

        $atbts = wp_parse_args($attrs,$default);

        $atbts['only_spored'] = 'false';

        $search = '';

        if( !empty($atbts['search']) && $atbts['search'] == 'true') {

            $search = $this->loadTemplate( dirname( __FILE__ ) . '/templates/form-search-events/search-events.php', $args, true);

        }

        global $post;

        $atbts['post_id'] = $post->ID;

        

        if( $atbts['public']==='true' ) {

            return $search.$this->front_events_user($atbts);

        }

        else if(current_user_can('manage_jobs')) {

            return $this->front_therapist_panel($atbts).$search;

        } else {

            return $this->front_events_user($atbts).$search;

        }

    }

    

    /**

     * Renderiza los eventos(masages) para confirmar que se les asignaron a un usuario tipo: Client-employee

     * */

    function render_confirmation_events_client_employee($attrs) {



        $default = array(

    	   'name' => 'Event',

           'plural_name' => 'Events',

           'number_per_page' => $this->number_locations_per_page,

           'enabled_pagination_ajax' => var_export($this->enabled_pagination_ajax, true)

        );

        $atbts = wp_parse_args($attrs,$default);



        if( empty(self::$confirm_events) ) {

            $user = get_user_by('id', get_current_user_id());



            self::$confirm_events = AJ_Location::get_confirmation_events( $user->user_email );

        }

        global $post, $current_user;

        $atbts['post_id'] = $post->ID;

        $atbts['confirmEvents'] = array();



        $atbts['upProfile'] = !empty($current_user->user_firstname) && !empty($current_user->user_lastname);

        $atbts['uid'] = $current_user->ID;



        foreach(self::$confirm_events as $ev) {

            /* dev:lamprea > Added conditional to load only the current user reserve */

            $myrange = array();

            foreach ($ev->reserved as $rs) {

                if($rs['user_id']==$current_user->ID) {

                    $myrange = (array)$rs;

                }

            }



            $atbts['confirmEvents'][] = array(

                'ID' => $ev->ID,

                'lead_id' => $ev->lead_id,

                'date' => $ev->year.'-'.($ev->month < 10 ? '0'.$ev->month : $ev->month).'-'.($ev->day < 10 ? '0'.$ev->day : $ev->day),

                'tstart' => $ev->stime,

                'tend' => $ev->etime,

                'reserved' => $ev->reserved,

                'myrange' => $myrange

            );

        }



        $search = $this->loadTemplate( dirname( __FILE__ ) . '/templates/form-search-events/search-events.php', $args, true);

        if( !empty($atbts['search']) && $atbts['search'] == 'true') {

            $search = $this->loadTemplate( dirname( __FILE__ ) . '/templates/form-search-events/search-events.php', $args, true);

        }



        wp_enqueue_style('jquery-styles-sm', '//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');

        wp_enqueue_script('jquery-ui-core2','//code.jquery.com/ui/1.10.3/jquery-ui.js',array('jquery'), false, true);

        //wp_enqueue_script('jquery-effects-slide');



        wp_localize_script( 'confirmation-events-js', 'evClientEmployee', array('attrs' => $atbts) );

        

        wp_enqueue_script('confirmation-events-js');

        



        return $this->func_pagination_confirmation_events(true,$atbts).$search;

    }



    /**

     * Render de confirmations events via Ajax use: render_confirmation_events_client_employee()

     * */

    function func_pagination_confirmation_events($return = false, $atbts = array()) {

        

        $date = '';

        if( !empty($_REQUEST['search']) && is_array($_REQUEST['search']) && !empty($_REQUEST['search']['year']) ) {

            $date = $_REQUEST['search']['year'].'{month}';



            $date = str_replace('{month}',

                        (!empty($_REQUEST['search']['month']) ? '-'.$_REQUEST['search']['month'] : ''), $date);

        }

        $attrs = empty($_REQUEST['attrs']) ? $atbts : $_REQUEST['attrs'];

        

        if( empty(self::$confirm_events) ) {

            $user = get_user_by('id', get_current_user_id());

            self::$confirm_events = AJ_Location::get_confirmation_events( $user->user_email , $date );

        }

        $confirmation_events = self::$confirm_events;





        $total = count($confirmation_events);



        

        $num_page = 1;

        $limit_start = 0;

        $number_per_page = intval($attrs['number_per_page']);

        $end = $number_per_page;

        global $paged;

        $temp_paged = $paged; 

        if( !empty($_POST['paged']) ) {

            $paged = $_POST['paged'];

        }

        if( $paged > 0 ) {

            $num_page = $paged;

            $limit_start = ($num_page - 1)*$number_per_page;

        }

        $confirmation_events = array_slice($confirmation_events,$limit_start,$end);

        $count_confirmation_events = count($confirmation_events);

        

        $exists_pages = $total > $number_per_page;

        $items_pages = array();

        if($exists_pages) {

            $total_pages = ($total%$number_per_page == 0) ? intval($total/$number_per_page): intval($total/$number_per_page) + 1;



            $stpage = $num_page - $this->pag_count_before;

            $stpage = ( $stpage > 0) ? $stpage : 1;

            

            $endpage = ($this->pag_count_after+1)+$num_page;

            $endpage = $endpage > $total_pages ? $total_pages+1 : $endpage;



            $limit_pages = $endpage - $stpage;

            

            $items_pages = array_fill( $stpage, $limit_pages, '');

            if($attrs['enabled_pagination_ajax'] == 'true') {

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="javascript:void(0);" onclick="goPage('.$page.')">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="javascript:void(0);" onclick="goPage('.($num_page-1).')"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="javascript:void(0);" onclick="goPage('.($num_page+1).')"><i class="icon-chevron-right"></i></a></li>';

            } else {

                $post_id = $atbts['post_id'];

                $permalink_structure = get_option('permalink_structure', '');

                if( empty($permalink_structure) ) {

                    $url = get_permalink($post_id).'&paged=%s';

                } else {

                    $url = get_permalink($post_id);

                    $url = substr($url,-1) == '/' ? $url.'page/%s/' : $url.'/page/%s/';

                }                

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="'.sprintf($url,$page).'">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="'.sprintf($url,($num_page-1)).'"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="'.sprintf($url,($num_page+1)).'"><i class="icon-chevron-right"></i></a></li>';

            }

            

            $items_pages[$num_page] = '<li class="paged active"><a><b>'.$num_page.'</b></a></li>';

            ksort($items_pages);

        }

        $labels = array();

        if(count($confirmation_events) > 0) {

            $labels = get_option('gf_front_labels_location', array());

        }

        $args = array(

            'lang' => self::LANG,

            'confirmation_events' => $confirmation_events,

            'labels' => $labels,

            'exclude_fields' => array(

                self::$field_id_number_therapist,

                self::$field_id_phone,

                self::$field_id_aproximate_total_of

            ),

            'pages' => $items_pages,

            'showing' => $count_confirmation_events,

            'total_confirmation_events' => $total,

            'is_request_ajax' => !$return,

            'attrs' => array(

                'plural_name' => __('Events Massage', self::LANG),

                'name' => __('Employee Sponsored', self::LANG)

            )

        );

        if($return) {

            return $this->loadTemplate( dirname( __FILE__ ) . '/templates/confirmation-events/confirmation-events.php', $args, true);

        }

        echo $this->loadTemplate( dirname( __FILE__ ) . '/templates/confirmation-events/confirmation-events.php', $args, true);

        $paged = $temp_paged;

        die();

        

    }

    

    function convert_minutes($st_hour) {

        list($h,$m,$s) = explode(':', $st_hour);

        return intval($h)*60+intval($m);

    }



    /**

     * Funcion que recibe la peticion ajax para almacenar en la tabla confirm_events_employee

     * que es donde estan las confirmaciones de los eventos de masage de 

     * los usuarios tipo client-employee

     ***/

    function ajax_confirm_event() {

        $result = array(

            'status' => 'error',

            'message' => __('Confirmation not saved on this event massage', self::LANG)

        );



        



        $user_id = get_current_user_id();

        if( empty($user_id) ) {

            $result['message'] = __('Please login before use this feature!', self::LANG);

        } else {

            

            $select_start = $this->convert_minutes($_POST['time_start']);

            $select_end = $this->convert_minutes($_POST['time_end']);



            if($select_start == $select_end) {

                global $smglobal_vars;

                $result['message'] = $smglobal_vars['textRangeNotValid'];

                die( json_encode($result) );

            }



            $is_range_valid = true;



            global $wpdb;



            $sql = 'SELECT hour_start,hour_end FROM '.$wpdb->prefix.'confirm_events_employee WHERE lead_id=%d AND location_date_id=%d AND user_id!=%d;';



            $rows = $wpdb->get_results( $wpdb->prepare($sql,$_POST['lead_id'],$_POST['location_date_id'],$user_id) );



            foreach ($rows as $row) {

                $ms = $this->convert_minutes($row->hour_start);

                $me = $this->convert_minutes($row->hour_end);



                if($select_start > $ms && $select_start < $me || $select_end > $ms && $select_end < $me || $ms > $select_start && $ms < $select_end || $me > $select_start && $me < $select_end) {

                    $is_range_valid = false;

                    break;

                }

            }



            if(!$is_range_valid) {

                $result['message'] = __('This time slot is already reserved', self::LANG);

                die( json_encode($result) );

            }

            

            $table = $wpdb->prefix.'confirm_events_employee';



            $data = array(

                'user_id' => $user_id,

                'lead_id' => $_POST['lead_id'],

                'location_date_id' => $_POST['location_date_id'],

                'date_event' => $_POST['date'],

                'hour_start' => $_POST['time_start'],

                'hour_end' => $_POST['time_end']

            );



            $is_insert = $wpdb->replace($table, $data, array('%d','%d','%d','%s','%s','%s'));



            if($is_insert) {

                update_user_meta( $user_id, 'event-invited', '0' );

                

                $result = array(

                    'status' => 'OK',

                    'message' => __('This event has been confirmed', self::LANG)

                );

            } else {

                $result['message'] = __('Can\'t confirmed the massage event. Verify you information event.', self::LANG);

            }

        }

        die( json_encode($result) );

    }





    /**

     * Function execute in the tab 2 for send invoice to the client

     * */

    function send_invoice_to_client() {
		
		global $wpdb;
		
        $response = array(

            'status' => 'error',

            'message' => __('Not send invoice to the client', self::LANG)

        );

        $items = $_POST['items'];
		$manual_email = $_POST['items'][0]['manual_email'];
        $asgn_location = $_POST['items'][0]['asgn_location'];
        $setuserid = $_POST['items'][0]['user'];
        $price = $_POST['items'][0]['items'][0]["price"];

		
		$sql_loc = "SELECT * FROM ".$wpdb->prefix."job_location WHERE ID=".$asgn_location;

        $locations = $wpdb->get_results($sql_loc);
		
		$jobdata = maybe_unserialize($locations[0]->data);
		
		$invoicenumber = time().rand(11111,99999);
		$htmldata = invoice_html($jobdata, $_POST, $invoicenumber);
		
		
		//require ADMJOBS_DIR . '/vendor/autoload.php';
		
		//$mpdf = new \Mpdf\Mpdf();
		//$mpdf->WriteHTML($htmldata);

		//$mpdf->WriteHTML("<img src='https://ibidsmsfrontend-dev.azurewebsites.net/assets/img/ibid.png' class='img-content'>");
		$invoicefilename = time().'_'.rand(1111,9999).'.pdf';
		
		$wpdb->query( sprintf( "INSERT INTO %s ( invoice_number, location_id, invoice_mails, invoice_date, invoice_amount, calculated_amount, number_of_therapist, number_of_hours, hourlypay, invoice_userid, file_name ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );",

                $wpdb->prefix.'location_invoice',
				$invoicenumber, 
                $asgn_location, $manual_email, date('Y-m-d H:i:s'),$price, $totalamt, $jobdata[7], $jobdata[30], $jobdata[53], $setuserid, $invoicefilename )

            );

		//print_r($htmldata);
		//print_r($jobdata);
		//print_r($_POST);
		//die();
		


        if( empty($items) ) {

            die(json_encode($response));

        }



        $permalink_structure = get_option('permalink_structure', '');

        $entry_id_encrypt = encrypt($_POST['entry_id']);



        $msg_email = $_POST['body_message'];



        $link_invoice = get_permalink(ID_PAGE_INVOICE);



        if(count($items) > 0) {

            $response = array(

                'status' => 'OK',

                'message' => __('The invoices has been sent',self::LANG),

                'resultsStatus' => array()

            );

        }

        

        $subject = __('New Invoice',self::LANG);



        foreach($items as $data) {



            if(is_object($data)) {

                $data = get_object_vars($data);

            }

            $path_file = !empty($_POST['path_full']) ? $data['file'] : substr(ABSPATH,0,-1).$data['file'];

            $user = get_user_by('id',$data['user']);



            //if( file_exists($path_file) ) {

                

                $subItems = $data['items'];



                $total_price = 0;

                $price_message = '';

                $lines = array();

                $messageItem = 'Event Massage #{num}:<br/>Total: {simbol} {price} USD<br/>{description}<br/>------------------------------------------------';



                $messageItem = __($messageItem,self::LANG);

                $items = array();



                foreach ($subItems as $i=>$subItem) {

                    if( is_object($subItem) ) {

                        $subItem = get_object_vars($subItem);

                    }



                    $lines[] = array(

                        'name' => __('Event Massage',self::LANG),

                        'description' => str_replace('<br/>', "\n", $subItem['description']),

                        'unitCost' => $subItem['price'],

                        'quantity' => 1

                    );



                    $items[] = array(

                        'name' => __('Event Massage',self::LANG),

                        'description' => str_replace('<br/>', "\n", $subItem['description']),

                        'unitCost' => $subItem['price'],

                        'quantity' => 1,

                        'location_date_id' => $subItem['location_date_id']

                    );



                    $price_message .= str_replace(array('{num}','{simbol}','{price}','{description}'), array($i+1,Admin_Jobs::$symbol_price,$subItem['price'],$subItem['description']), $messageItem)."\n";





                    $total_price += $subItem['price'];

                }



                $price_message .= str_replace(array('{simbol}','{price}'), array(self::$symbol_price,$total_price), __('Total: {simbol} {price} USD'))."\n";

                

                update_option('content_body_message_email_invoice', $msg_email);

                

                $attachments = array($path_file);

                if( !( empty($user->user_email) /*|| empty($user->first_name)*/ || empty($_POST['entry_id']) ) ) {

                    

                    $email_client = $user->user_email;

                    $name_client = empty($user->first_name) ? $email_client : $user->first_name;

                                

                    if( empty($permalink_structure) ) {

                        $link_invoice2 = $link_invoice.'&entry='.$entry_id_encrypt;

                    } else {

                        $link_invoice2 = $link_invoice.'?entry='.$entry_id_encrypt;

                    }

                    gform_update_meta($_POST['entry_id'], 'info_invoice_event', array('items' => $items,'currency' => 'USD'));

                    $html_link_invoice = '<a style="color: '.self::$color_link_email.';" href="'.$link_invoice2.'" target="_blank">'.$link_invoice.'</a>';

                    

                    

                    

                    $payment = 'spotmassage';

                    if( !empty($_POST['method_payment']) ) {

                        $payment = $_POST['method_payment'];

                    }

                    $isSend = false;
					
					//echo $html_link_invoice;
					//echo ::invoice link::;

                    switch ($payment) {

                        case 'freshbooks':



                            $message = str_replace(array('{user_name}','{price}','{link_invoice}'), array($name_client,str_replace('<br/>', "\n", $price_message),'::invoice link::'), $msg_email);

                        

                            $apiFreshBooks = new ThatsSpotMassage_FreshBooks();



                            

                            //$apiFreshBooks->deleteAllInvoices($user->ID);

                            

                            $isSend = true;

                            /*

                            if(class_exists('WP_Better_Emails')) {

                                $wpbe = new WP_Better_Emails();

                                $message = $wpbe->set_email_template($message);

                            }

                            */



                            $isSend = $apiFreshBooks->sendEmail($user->ID,$lines,$subject,$message,$_POST['entry_id']);



                            break;

						case 'converge':
							
                            $message = str_replace(array('{user_name}','{price}','{link_invoice}'), array($name_client,nl2br($price_message),site_url().'/invoice.php?number='.$invoicenumber), $msg_email);
							
							if(!empty($manual_email)){
								$email_client.=','.$manual_email;
							}
							
							$adminemail = get_option('admin_email');
							if(!empty($adminemail)){
								$email_client.=','.$adminemail;
							}
							
						$headers = array('Content-Type: text/html; charset=UTF-8');
						//$attachments = array( WP_CONTENT_DIR . '/uploads/pdf/'.$invoicefilename );
                            $isSend = wp_mail($email_client, $subject, $message, $headers);

                            break;

                        default:

                            $message = str_replace(array('{user_name}','{price}','{link_invoice}'), array($name_client,nl2br($price_message),$html_link_invoice), $msg_email);

                            if(!empty($manual_email)){
								$email_client.=','.$manual_email;
							}
							
							$adminemail = get_option('admin_email');
							if(!empty($adminemail)){
								$email_client.=','.$adminemail;
							}
							
						$headers = array('Content-Type: text/html; charset=UTF-8');
						$attachments = array( WP_CONTENT_DIR . '/uploads/pdf/'.$invoicefilename );
                            $isSend = wp_mail($email_client, $subject, $message, $headers, $attachments);
                            

                            break;
							
							
							
							
							
							
							

                    }





                    

                    if($isSend) {

                        $response['resultsStatus'][] = array(

                            'status' => 'OK',

                            'message' => __('The invoices has been sent',self::LANG),

                            'user_id' => $user->ID,

                            'user_email' => $user->user_email

                        );

                    } else {

                        $response['resultsStatus'][] = array(

                            'status' => 'error',

                            'message' => __('Invoice Not sent  to the client', self::LANG),

                            'user_id' => $user->ID,

                            'user_email' => $user->user_email

                        );

                    }

                } else {

                    $response['resultsStatus'][] = array(

                        'status' => 'error',

                        'message' => __('Can\'t has been sent the invoice to the client, not found vars: email, first_name or entry_id',self::LANG),

                        'user_id' => $user->ID,

                        'user_email' => $user->user_email

                    );

                }

            /* } else {

                $response['resultsStatus'][] = array(

                    'status' => 'error',

                    'message' => __('Can\'t has been sent the invoice to the client, because the file not found.',self::LANG),

                    'user_id' => $user->ID,

                    'user_email' => $user->user_email

                );

            } */

        }// end foreach -------------------

        

        echo json_encode($response);

        die();

    }



    /**

     * Function execute in the tab 2 for send invoice to the client with automatic method

     * */

    function generated_invoices() {

        require('fpdf/fpdf.php');

        require('templates/invoice-pdf/template.php');

        $response = generatePDFInvoice();

        echo json_encode($response);

        die();

    }





    /**

     * calling: add_action('wp', array($this, 'validate_access_page_payment'),10);

     * used: http://spotmassage.smadit.com/service-review/

     * */

    function validate_access_page_payment() {

        

        if(is_page(ID_PAGE_INVOICE)) {

            $aux_entry_id = false;

            if(isset($_GET['entry'])) {

                $entry_id = decrypt($_GET['entry']);

                $this->delete_session_var('assoc_entry_invoice');

            } else {

                $aux_entry_id = $this->get_session_var('assoc_entry_invoice'); 

                if( $aux_entry_id ) {

                    $entry_id = $aux_entry_id;

                }

            }



            $user_id = get_current_user_id();



            if( Admin_Jobs::checkIfPayService($entry_id, $user_id) ) {// verify if this job (entry) has been paid

                wp_redirect(get_permalink(get_option('paypal_success_page')));

                return;

            }

            

            

            $list_invoices = get_user_meta($user_id,'invoices_service_review',true);

            $list_invoices = empty($list_invoices) ? array() : $list_invoices;



            if( (!isset($_GET['entry']) && !$aux_entry_id) || empty($entry_id) || !$this->exists_entry($entry_id,$user_id) ) {

                wp_redirect(site_url());

                return;

            } else {

                

                if(!$aux_entry_id) {

                    $this->set_session_var('assoc_entry_invoice',$entry_id);

                }

                if(in_array($entry_id,$list_invoices)) {

                    wp_redirect(get_permalink(ID_PAGE_PAYMENT_OPTIONS));

                    return;

                }

            }

        }

    }

    /**

     * use: wp-content/plugins/paypal-express-checkout/classes/shortcode.php

     * **/

    function cancel_url_payment($default = '') {

        $aux_entry_id = $this->get_session_var('assoc_entry_invoice');

        $link = empty($default) ? site_url() : $default;

        if($aux_entry_id) {

            $permalink_structure = get_option('permalink_structure', '');

            if( empty($permalink_structure) ) {

                $link = get_permalink(ID_PAGE_INVOICE).'&entry='.encrypt($aux_entry_id);

            } else {

                $link = get_permalink(ID_PAGE_INVOICE).'?entry='.encrypt($aux_entry_id);

            }

        }

        return $link;

    }

    

    /*

     * Used after save the Secondary Form (Submit Job) to update the locations of the service requested

     */

    function gf_after_save_form($form_meta) {

        

        if($form_meta['id'] == self::$job_form_id) {

            if( !empty($form_meta['fields']) ) {

                $start = false;

                $end = false;

                $labels_fields = array();

                foreach($form_meta['fields'] as $field) {

                    $start = $start || (!empty($field['cssClass']) && preg_match('/^(.*)start_gf_multi_row(.*)$/', $field['cssClass']));

                    

                    $end = $end || (!empty($field['cssClass']) && preg_match('/^(.*)finish_gf_multi_row(.*)$/', $field['cssClass']));

                    

                    if($start) {

                        $labels_fields[$field['id']] = $field['label'];

                    }

                    

                    if($end) {

                        break;

                    }

                }

                update_option('gf_labels_location', $labels_fields);

                update_option('gf_front_labels_location', $labels_fields);

                $this->update_field_id_locations($form_meta['fields']);

            }

        }

    }

    



    function update_field_id_locations($fields) {

        if( !empty($fields) ) {

            foreach($fields as $field) {

                if( !empty($field['cssClass']) && preg_match('/^(.*)gf_multi_row_reference(.*)$/', $field['cssClass']) ) {

                    update_option('gf_field_id_locations', $field['id']);

                }

            }

        }

    }

    

    /** Function Add Menus Admin **/

    function register_submenu_page() {

        add_menu_page(__('Manage Jobs Massage', self::LANG), __('Manage Jobs Massage', self::LANG), 'activate_plugins', 'admin_jobs', array($this, 'render'));

        

        add_menu_page(__('Therapist Registry', self::LANG),__('Therapist Registry', self::LANG),'activate_plugins','seach_therapist', array($this, 'render_search_therapist'));

		
		//add_submenu_page( null, 'View Notes', null, 'manage_options', 'view_location', array($this,'view_location') );
		//add_submenu_page( null, 'Archive Location', null, 'manage_options', 'archive_location', array($this,'archive_location') );
		
		
		add_submenu_page( null, 'Archive Location', null, 'manage_options', 'archive_location', array($this,'show_archive_location') );
		
		
		
		

    }



    /**

     * Function to render the content of menu "Therapist Registry" (http://spotmassage.smadit.com/wp-admin/admin.php?page=seach_therapist)

     *******/

    function render_search_therapist() {

	

       



        //$all_therapists = $this->getAllLatLngTherapists();

        $all_therapists = getAllLatLngTherapists();

        $vars_script = array(

            'radius' => get_option('mjobs_radio_search', 2),

            'messageResult' => __('It has established the following address as search center: "{address}", with a radius of {radius} mi.',self::LANG),

            'allUsers' => $all_therapists,

            'btnTriggerActionSearch' => 'search-therapists',

            'eLoading' => 'loading-search-therapist'

            /*

            ,'geocoder' => null,

            'urlGetUsers' => null,

            'requestParams' => null,

            'eMessageResult' => 'msg-result',

            'inputKeyword' => 'search_keyword',

            'inputCity' => 'search_city',

            'inputState' => 'search_state',

            'usersFound' => array()*/

            

        );

        ApiSearch::load();

        wp_localize_script( 'apijs-search', '_configUSPgl', $vars_script );



        

        $locations = array();

        $args = array(

            'lang' => self::LANG,

            'required' => '*'

        );



        $this->the_modal_settings(true);

        

        $this->loadTemplate( dirname( __FILE__ ) . '/templates/search-therapist/search-therapist.php', $args );

    }



    /**

     * Function to render the modal settings

     *****/

    function the_modal_settings($hide_select_form = false, $html_id_dialog = 'link-show-modal-settings') {

        /**

         * Init modal settings

         ****/

        wp_enqueue_style('jqueryui-local-style');

       

        wp_enqueue_script('modal-settings-mjobs', plugins_url('/templates/modal-settings/modal_settings.js', __FILE__), array(/*'jquery-ui-all'*/), false, true); 

        $vars_script = array(

            'isFormDefined' => !empty(self::$job_form_id) || $hide_select_form,

            'idDialog' => $html_id_dialog

        );

        wp_localize_script( 'modal-settings-mjobs', 'modalSettings', $vars_script );

        $forms = Admin_Jobs::getForms();

        Admin_Jobs::loadTemplate( ADMJOBS_DIR.'/templates/modal-settings/modal-settings.php', array(

            'hide_select_form' => $hide_select_form,

            'lang' => self::LANG,

            'form_id' => $vars_script['isFormDefined'] ? self::$job_form_id : '',

            'forms' => $forms,

            'radio' => get_option('mjobs_radio_search', '')

        ));

        // End modal settings ------------------------------------------

    }

    

    function stop_password_nag( $val ){

        return 0;

    }

    

    /**

     * Assing Job for Therapist and send SMS and Email notification 

     * */

    function assign_therapist() {
		global $wpdb;
		
        $response = array(

            'message' => __('The Jobs has not been assigned to the therapist(s), because the Job was not found', self::LANG),

            'status' => 'error'

        );

        

        $therapists_inviteds = $_POST['therapists_asgn'];

        $therapists_ids = array_keys($therapists_inviteds);

        $location = $this->load_location($_POST['location_id']);

        

        $therapists = $accept_job = array();

        $data = maybe_unserialize($location->data);

        $amount_vacantes = $data[self::$field_id_number_therapist];

        $min = min($amount_vacantes,count($therapists_ids));


        if( !empty($location->users_invited) ) {

            $therapists = maybe_unserialize($location->users_invited);

            $accept_job = maybe_unserialize($location->accept_job);

            //$amount_vacantes = $amount_vacantes-count($therapists);

            //$min = min($amount_vacantes,count($therapists_ids));

            /*if($min <= 0) {

                $response = array(

                    'message' => __('Vacantes not found.', self::LANG),

                    'status' => 'error'

                );

                echo json_encode($response);

                die();

            }*/

        }
		//print_r($therapists);
        //for($i = 0; $i < $min; $i++) {}
		foreach($therapists_ids as $id) {
           
            $therapists[$id] = $therapists_inviteds[$id];
			//print_r($therapists[$id]);
            //$accept_job[$key] = '';
			
        }
		//$therapists[$key] = $therapists_inviteds[$key];
		
        $therapists_inviteds = maybe_serialize($therapists);

        $status_accept_job = maybe_serialize($accept_job);

		//isset($location)
				
        if( $location ) {
	
            $data_columns = array(

                'primary_location' => $location->primary_location,

                'created_by' => $location->created_by,

                'users_invited' => $therapists_inviteds,

                //'accept_job' => $status_accept_job,

                'id' => $location->location_id

            );


			// no need to same location again by gps
            //AJ_Location::store_location($location->lead_id,$location->data,$data_columns);
			AJ_Location::store_location($location->lead_id,$location->data,$data_columns);
            $response['message'] = __('The Jobs Invitation has been sent to the therapist(s)', self::LANG);

            $response['status'] = 'OK';
			
            $permalink_structure = get_option('permalink_structure', '');

            if( empty($permalink_structure) ) {

                //$job_url = get_permalink(ID_PAGE_MY_JOBS).'&#location_'.$location->ID;
				
            } else {
			
               // $job_url = get_permalink(ID_PAGE_MY_JOBS).'?location_listing='.$location->ID;

            }
			
			
			
			//$post = get_page_by_path('confirm-events', OBJECT, 'page');
			$post = get_page_by_path('my-events', OBJECT, 'page');
			$event_page_link = get_permalink($post->ID);
  
			$job_url = $event_page_link.'/?location_listing='.$_POST['location_id'].'&key=[loginkey]';
            $link = '<a style="color: '.self::$color_link_email.';" href="'.$job_url.'" target="_blank">'.__('View Job', self::LANG).'</a>';

            $link2 = $job_url;
			
			//$datetime = AJ_Location::getDatesEventByLocation($location->ID);
			
			$datetime = $wpdb->get_results( $wpdb->prepare( 'SELECT lde.* FROM '.$wpdb->prefix.'location_date_event lde WHERE lde.location_id=%d ORDER BY lde.year, lde.month, lde.day, lde.stime, lde.etime;', $location->ID ) );
			
			$details = $data;
			
			$link .= '<div > Company Name: '. $details[52].'</div>';
					
					$sep = '';
					foreach($datetime as $dt){
					$link .= '<div >
					  Date : 
					 '.
						date('M d, Y',strtotime($dt->month.'/'.$dt->day.'/'.$dt->year))
					.'
					  Time : '.
					  $dt->stime;
					  $sep = ', ';
					  $link .= 'To '. $dt->etime.' <br /></div>';
					 
					 }
					
					$link .= '<div > Address:  '. $details[8].'</div>
					<div > City:  '. $details[9].'</div>
					<div > State:  '. $details[10].'</div>
					<div > Zipcode:  '. $details[51].'</div>
					<div > Hourly Rate:  '. $details[53].'</div>
					<div > Total # of Therapist:  '. $details[7].'</div>
					<div > Total # of Massage Hours:  '. $details[30].'</div>
				</div>';


            $message_email = $_POST['txt_email'];

            $message_sms = $_POST['txt_sms'];//.' Open this url for unsubscribe '.site_url().'/unsubscribe.php?uid='.syn_encrypt($user->ID, ENCRYPTION_KEY).'&unsubscribefield=unsubscribeSMS';

            update_option('content_email_invitation', $message_email);

            update_option('content_sms_invitation', $message_sms);
            $message_email = str_replace("{invitation_link}", $link, $message_email);
			
            $message_sms = str_replace("{invitation_link}", $link2, $message_sms);

			
            foreach($therapists_ids as $id) {

                $user = get_user_by('id', $id);
				$unsubs = get_user_meta($id, 'unsubscribeEmail', true);
				$unsubsms = get_user_meta($id, 'unsubscribeSMS', true);
				
				
				$message_email = str_replace("[loginkey]", base64_encode($user->user_login), $message_email);
                $message_email = str_replace("{user_name}", $user->first_name, $message_email).'<a href="'.site_url().'/unsubscribe.php?uid='.syn_encrypt($user->ID, ENCRYPTION_KEY).'&unsubscribefield=unsubscribeEmail">Click here</a> to safely unsubscribe from '.get_bloginfo( 'name' );
				$message_sms = str_replace("{user_name}", $user->first_name, $message_sms);

                if($user->user_email == 'l.tom.perry@test.com') {

                    $user->user_email = 'julianchoss@gmail.com';

                }

                $to = get_user_meta($id,'mobile_phone_number',true);
				//echo '<pre>';print_r($_POST);
				
				/// && $unsubs!='yes'
				if($_POST['is_send_mail']==1 ) {
					//echo $user->user_email.' '.$message_email;
                	$result = wp_mail($user->user_email, __('New Job at That\'s the Spot Massage', self::LANG), $message_email );
				}
                

                if($_POST['is_send_sms']==1 && $unsubsms!='yes') {

                    if( !empty($to) && substr($to,0,1) != '+') {

                        $to = '+'.$to;

                    }

                    $this->sendSMS($to,$message_sms);    

                }

            }

        }


        echo json_encode($response);
        die();

    }
	
	function get_email_template(){
		
		global $wpdb;
		$id = $_POST["id"];
		$locationdata =  $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'email_templates  WHERE id="'.$id.'";');
		
		$result = array('status'=>'OK');
		/*ob_start();
		wp_editor( stripcslashes($locationdata->content),'email-invitation-content', array('media_buttons' => false, 'textarea_rows' => 12) );
	
		$result["data"] =  ob_get_clean();*/
		$result["data"] =  stripcslashes($locationdata->content);
		
		
		echo json_encode($result);
		die();
	}
	
	
	
	function save_email_template(){
		
		global $wpdb;
		$template_name = $_POST["template_name"];
		$email_content = $_POST["txt_email"];
		$locationdata =  $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'email_templates  WHERE name="'.$template_name.'";');
		
		if(isset($locationdata->id) && $locationdata->id!=''){
			$wpdb->query('UPDATE '.$wpdb->prefix.'email_templates set content = "'.$email_content.'" WHERE id="'.$locationdata->id.'";');
			
		}else{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'email_templates set content = "'.$email_content.'", name = "'.$template_name.'" ;');
		}
		
		$result = array('status'=>'OK');
		$options = '<option value="">Select Template</option>';
		$list = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'email_templates '));
		foreach($list as $data){
			$options .= '<option value="'. $data->id . '">'. $data->name . '</option>';
		}
		$result["options"] = $options;
		echo json_encode($result);
		die();
	}
	
	
	function editlocation(){
		
	$lid = $_POST["lid"];
	
	global $wpdb;

    $locationdata =  $wpdb->get_row('SELECT l.lead_id,l.data,l.created_by,l.users_invited,l.accept_job,l.primary_location FROM '.$wpdb->prefix.AJ_Location::$tblLocation.' l  WHERE l.ID='.$lid.';');
	
    $ltimedata =  $wpdb->get_results('SELECT day as Day, month as Month, year as Year, stime, etime FROM '.$wpdb->prefix.AJ_Location::$tblDateLocation.' d WHERE d.location_id='.$lid.';',ARRAY_A );
	
	
	$narray = array();
	$ty = 0;
	foreach($ltimedata as $dvi){
		
		$narray[$ty] = $dvi;
		$narray[$ty]["Start Time"] = date("h:i a", strtotime($dvi["stime"]));
		$narray[$ty]["End Time"] = date("h:i a", strtotime($dvi["etime"]));
		
		$ty++;
	}
	//print_r($narray);
	$datasou = $locationdata->data;
	//print_r($locationdata);
	
	$datasou = stripcslashes($datasou);
	$datasou = unserialize($datasou);
	/* print_r(unserialize($datasou));
	print_r(json_decode(stripcslashes($locationdata->data)));
	print_r(json_decode($locationdata->data)); */
	
	?>
	<tr>
	<td colspan="15">
	
	<form name="location_edit" id="location_edit" >
	<table>
	<tr>
	<?php
	$form = RGFormsModel::get_form_meta(2);
    $post_fields = array_keys(get_option('gf_labels_location', array()));
	$i=0;
	if(is_array($form["fields"])){
		foreach($form["fields"] as $field){
		if(in_array($field->id, $post_fields)){
			if($field->id==50){
			echo '</tr><tr><td colspan="4">';	
			$value = serialize($narray);
			}else{
				echo '<td>';
				$value = $datasou[$field->id];
			}
		
	?>			
	<label><?php echo GFCommon::get_label($field);?></label><div class="dynamic_fields"><?php echo (GFCommon::get_field_input( $field, $value, 0, $form_id, $form ));?></div></td>
	<?php
	$i++;
	if($i%4==0 ){
		?>
		</tr><tr>
		<?php
	}
	
				}
            }
        } 
	?>
	</tr>
	<tr>
	<td><a href="javascript:void(0)" onclick="updatelocation(this)" class="button button-large button-primary">Update Location</a> <input type="hidden" name="action" value="updatelocation" />  
	<input type="hidden" name="lid" id="lid" value="<?php echo $locationdata->lead_id;?>" /> 
	<input type="hidden" name="locid" id="locid" value="<?php echo $lid;?>" /> 
	
	</td></tr></table></form>
	
	</td>
	</tr>
		
		
		
		<?php
		die();
	}
    

    function save_settings() {

        $response = array(

            'message' => __('The Settings has been saved', self::LANG),

            'status' => 'OK'

        );

        

        if( !empty($_POST['form_id']) ) {

            update_option('mjobs_form_id', $_POST['form_id']);

        }

        if( !empty($_POST['radio']) ) {

            update_option('mjobs_radio_search', $_POST['radio']);

        }

        echo json_encode($response);

        die();

    }

    

    

    

    function render() {

        

        

        if(empty(self::$job_form_id)) {

            $this->the_modal_settings();

            return;

        }

        

        $form = RGFormsModel::get_form(self::$job_form_id);

        

        $columns = RGFormsModel::get_grid_columns(self::$job_form_id, true);

        

        $args = array(

            'class' => $this,

            'lang' => self::LANG,

            'form' => $form,

            'columns' => $columns,

            'select_month' => 'all',

            'select_year' => 'all'

        );

        

        if(isset($_POST['filter_year']) && $_POST['filter_year'] != 'all') {

            $star_date = $_POST['filter_year'].'-01-01 00:00:00';

            $end_month = 12;

            if(isset($_POST['filter_month']) && $_POST['filter_month'] != 'all') {

                $star_date = $_POST['filter_year'].'-'.$_POST['filter_month'].'-01 00:00:00';

                $end_month = intVal($_POST['filter_month'])+1;

            }

            if($end_month > 12) {

                $end_month = '01';

            } elseif($end_month < 10) {

                $end_month = '0'.$end_month;

            }

            

            $end_date = $_POST['filter_year'].'-'.$end_month.'-01 00:00:00';

            

            $leads = RGFormsModel::get_leads(self::$job_form_id, 0, 'DESC', '', 0, 30, null, null, false, $star_date, $end_date);

            

            $args['select_month'] = $_POST['filter_month'];

            $args['select_year'] = $_POST['filter_year'];

            

        } else {

            //$leads = RGFormsModel::get_leads(self::$job_form_id);
            $leads = RGFormsModel::get_leads(self::$job_form_id, 0, 'DESC', '', 0, 500);

        }
		//echo count($leads);
        $args['leads'] = $leads;

        

        $this->loadTemplate( ADMJOBS_DIR.'/templates/main-panel/manage-jobs.php', $args);

        

        $this->the_modal_settings();

    }

    public static function loadTemplate($path_file, $vars = array(), $return = false) {

        global $wp_query;

        $wp_query->query_vars = $vars;

        if($return) {

            ob_start();

            load_template( $path_file );

            return ob_get_clean();

        }

        load_template( $path_file );

    }

    

    public static function getForms($is_active = null, $sort = 'title ASC') {

        global $wpdb;

        $form_table_name =  RGFormsModel::get_form_table_name();



        $active_clause = $is_active !== null ? $wpdb->prepare("WHERE is_active=%d", $is_active) : "";

        $order_by = !empty($sort) ? "ORDER BY $sort" : "";



        $sql = 'SELECT f.id,f.title,f.date_created,f.is_active FROM '.$form_table_name.' f '.$active_clause.' '.$order_by.';';

        $forms = $wpdb->get_results($sql);

        return $forms;

    }

    

    /** Load the ID of the Submit Jobs form from the database **/

    function updateFormId() {

        if(!defined('ID_SECUNDARY_FORM')) {

            define('ID_SECUNDARY_FORM', 0);

        }

        self::$job_form_id = get_option('mjobs_form_id', ID_SECUNDARY_FORM);

    }

    

    function getCountVacantes($lead) {

        

        $lead = RGFormsModel::get_lead($lead["id"]);

        $_field_id_multiple = $this->getIdFieldMultiple();

        $field = maybe_unserialize($lead[$_field_id_multiple]);

        $count = 0;

        

        if( !empty($lead[self::$field_id_number_therapist]) && is_numeric($lead[self::$field_id_number_therapist])) {

            $count += $lead[self::$field_id_number_therapist];

        }

        

        if(is_array($field) && !array_key_exists('Number of Therapist', $field)) {

            foreach($field as $data) {

                if(array_key_exists('Number of Therapist', $data)) {

                    $count += intVal($data['Number of Therapist']);

                }

            }

        }

        return $count;

    }

    

    function getIdFieldMultiple() {

        if(!empty(self::$field_id_locations)) {

            return self::$field_id_locations;

        }

        self::$field_id_locations = get_option('gf_field_id_locations', 0);

        return self::$field_id_locations;

    }

    

    function add_tag_encrypt_entry_id($text, $form, $lead, $url_encode) {

        $entry_id = $url_encode ? urlencode(rgar($lead, 'id')) : rgar($lead, 'id');

        $encrypt_entry_id = encrypt($entry_id);        

        $text = str_replace("{encrypt_entry_id}", $encrypt_entry_id, $text);

        return $text;

    }

    

    /**

     * Function that validate the access to the page with Submit-job / Secondary Form

     * Section: http://spotmassage.smadit.com/submit-job/

     ***/

    function validate_access_page() {

        if( !isset($_GET['quote'])) {

            wp_redirect(site_url());

            return;

        }

        if($_GET['quote'] == 'appointment' && is_user_logged_in()) {

            $this->set_session_var('pform_lead_id', 0);

            return;

        }

        $entry_id = decrypt($_GET['quote']);

        if( (empty($entry_id) || !$this->exists_entry($entry_id) ) ) {

            if(isset($_POST['gform_submit'])) {

                return;

            }

            wp_redirect(site_url());

            return;

        }



        if( $this->exists_entry_associate($entry_id) ) {

            if(isset($_POST['gform_submit'])) {

                return;

            }

            wp_redirect(site_url());

            return;

        }



        $this->set_session_var('pform_lead_id', $entry_id);

    }

    /**

     * Verifica que el segundo formulario (submit job) no pueda ser llenado mas de una vez con un mismo enlace el primer formulario

     *****/

    function exists_entry_associate($lead_id) {

        global $wpdb;



        if( empty($lead_id) ) {

            return false;

        }



        $result = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'rg_lead_meta WHERE meta_key=\'pform_lead_id\' AND meta_value=%s;', $lead_id) );



        return !empty($result);

    }

    function exists_entry($entry_id, $created_by = null) {

        global $wpdb;

        

        if( !empty($created_by) ) {

            

            if( user_can( $created_by, 'client_employee' ) ) { // role Client Employee

                $user = get_userdata($created_by);



                $query = 'SELECT lead_id AS lead FROM '.$wpdb->prefix.'rg_lead_detail WHERE lead_id='.$entry_id.' AND field_number='.self::$field_id_participants.' AND value LIKE \'%'.$user->data->user_email.'%\';';

               

            } else {

                $query = 'SELECT id AS lead FROM '.$wpdb->prefix.'rg_lead WHERE id=%s AND created_by=%s;';



                $query = $wpdb->prepare($query,$entry_id,$created_by);

            }

        } else {

            $query = 'SELECT id AS lead FROM '.$wpdb->prefix.'rg_lead WHERE id=%s;';

            $query = $wpdb->prepare($query,$entry_id);

        }

        $result = $wpdb->get_var($query);

        return !empty($result);

    }

    function get_url_appointment($is_user_loggin = false) {

        $url = Theme_My_Login::get_page_link('login');

        $permalink_structure = get_option('permalink_structure', '');

        $url_submit_job = get_permalink(ID_PAGE_SUBMIT_JOB);

        $quote = 'appointment';

        if( empty($permalink_structure) ) {

            $url = $is_user_loggin ? $url_submit_job.'&quote='.$quote : $url.'&redirect_to='.$url_submit_job.'&quote='.$quote;

        } else {

            $url = $is_user_loggin ? $url_submit_job.'?quote='.$quote : $url.'?redirect_to='.$url_submit_job.'?quote='.$quote;

        }

        return $url;

    }



    

    /**

     * replace in the content the tag {link_appointment}.

     *

     * @uses is_page()

     */

    function content_filter_link_appointment( $content ) {

        if(is_page() && preg_match('/.*{invoice_pdf}.*/', $content)) {

            $aux_entry_id = $this->get_session_var('assoc_entry_invoice');

            $entry = gform_get_meta($aux_entry_id, 'info_invoice_event');

            

            if( !empty($entry) && array_key_exists('path_invoice',$entry) && !empty($entry['path_invoice']) ) {

                $pos = strpos($entry['path_invoice'],'/wp-content/');

                $path = substr($entry['path_invoice'],$pos);

                $content = str_replace('{invoice_pdf}',$path,$content);

            }                    

            

        }

        return $content;

    }



    /** used to add the javascript adn styles needed to render the notes textarea as wysiwyg editor **/

    function add_notes_texteditor($form, $lead) {

    wp_enqueue_script( 'nicEdit-js', plugins_url('/js/nicEdit-latest.js', __FILE__) );
	//"https://js.nicedit.com/"	
		


	/* $frmfield = new GF_Field_List();
	echo $frmfield->get_field_input(array('id'=>2));
	//echo GF_Field_List::get_field_input(2);

echo '<pre>';
$post_fields = array_keys(get_option('gf_labels_location', array()));
print_r($post_fields);
	print_r(get_all_form_fields(2)); */
	?>
    <!-- make all elements with class="editable" editable with Aloha Editor -->

	<div id="dynamic_location" class="dynamic_location" style="display:none" >
	<form name="location_add" id="location_add" >
	<table>
	<tr>
	<?php
	$form = RGFormsModel::get_form_meta(2);
    $post_fields = array_keys(get_option('gf_labels_location', array()));
	$i=0;
	if(is_array($form["fields"])){
		foreach($form["fields"] as $field){
		if(in_array($field->id, $post_fields)){
			if($field->id==50){
			echo '</tr><tr><td colspan="4">';	
			}else{
				echo '<td>';
			}
	?>			
	<label><?php echo GFCommon::get_label($field);?></label><div class="dynamic_fields"><?php echo (GFCommon::get_field_input( $field, $value, 0, $form_id, $form ));?></div></td>
	<?php
	$i++;
	if($i%4==0 ){
		?>
		</tr><tr>
		<?php
	}
	
				}
            }
        } 
	?>
	</tr>
	<tr>
	<td><a href="javascript:void(0)" onclick="savelocation(this)" class="button button-large button-primary">Save Location</a> <input type="hidden" name="action" value="addmorelocation" />  <input type="hidden" name="lid" id="lid" value="<?php echo $_GET["lid"];?>" /> </td></tr></table></form>
	
	</div>
	
	<style type="text/css">
	.dynamic_fields select {
    max-width: 160px;
}
</style>

	<script type='text/javascript' src='https://ahhthatsthespotmassage.com/wp-content/plugins/gravityforms/js/gravityforms.min.js'></script>
    <script type="text/javascript">
	function update_location(lid){	
	//alert('sfsaf');
	jQuery.post('admin-ajax.php',{'action':'editlocation','lid':lid},function(data){
		
		//alert(jQuery(".location_tr"+lid).length);
		jQuery(".location_tr"+lid).after(data);
		
	});
	
	}
	
	function addmorelocation(){	
	//alert('sfsaf');
	jQuery("#addmorelocation").append(jQuery("#dynamic_location").html());
	}
	

		var area1;

		jQuery(document).ready(function() {

			var notes = jQuery( "textarea[name='new_note']" );

			notes.attr('id', 'notes-editor');



			toggleAreaEditor();



			jQuery('.detail-note-content').each(function(index, element) {

				jQuery(this).html( jQuery(this).html() );

			});

		});

		function toggleAreaEditor() {

			if(!area1) {

					area1 = new nicEditor({fullPanel : true}).panelInstance('notes-editor');

			} else {

					area1.removeInstance('notes-editor');

					area1 = null;

			}

		}

	</script>

	<?php

    }


	
    function footer_emailnotes_change( $form = '' ) {
		
	

        echo <<<EOF

        <script type='text/javascript'>

            jQuery(function() {

                var _dropdown_emails = jQuery('#the-comment-list').find('.lastrow select');

                var _parent = jQuery('#gentry_email_subject_container');

                _dropdown_emails.remove();

                _parent.prepend( '<label for="gentry_email_notes_to">Send note to the following emails: </label>'

                               +'<input type="text" name="gentry_email_notes_to" style="width:30%" id="gentry_email_notes_to" /> &nbsp; ');

                _parent.append('<br/><small><b>Note: </b>If you define any email address, this note will be sent to that email. You can add several emails separated by comma</small>');

                _parent.show();

                

                jQuery('#gentry_email_subject').css('width','33%');



                jQuery('#the-comment-list').find('.button').click(function() {

                    toggleAreaEditor();

                });

            });

        </script>

EOF;



        return $form;

    }



    /**

     * Boxes created after the entry details page in the admin

     * */
	
	  function show_archive_location() {

        //die("<pre>".print_r($_GET['page'], true)."</pre>");



        wp_enqueue_style('jqueryui-local-style');

       

        wp_enqueue_script('jquery-ui-tabs');

        wp_enqueue_media();
		
		
		$lead['id'] = $_GET["lid"];

        // TODO: Fix issue with RAM on loading all website users

        //$all_therapists = $this->getAllLatLngTherapists();

        //echo("<pre>".print_r($all_therapists, true)."</pre>");

        
        $lead_id = gform_get_meta($lead['id'], 'pform_lead_id');



        if( !empty($lead_id) ) {            

            $lead2 = RGFormsModel::get_lead($lead_id);

            $form2 = RGFormsModel::get_form_meta($lead2['form_id']);

            /** Box Detail Form Primary **/

            //GFEntryDetail::lead_detail_grid($form2, $lead2, false);

        }
		
        $locations = $this->get_job_location_by_lead($lead['id'], 'yes');
		//print_r($locations);
		//print_r($lead['id']);
        $id_multiple = $this->getIdFieldMultiple();

        $labels_location = get_option('gf_labels_location', array());

        /** Box Locations **/
		$lead = RGFormsModel::get_lead($lead["id"]);
        $user = new WP_User($lead['created_by']);
		
        $args = array(

            'lead' => $lead,

            'lang' => self::LANG,

            'locations' => $locations,

            'labels_location' => $labels_location,

            'user_created' => $user,

            'required' => '*'

        );

        //die("<pre>box-".print_r($args, true)."</pre>");

        //$this->loadTemplate( dirname( __FILE__ ) . '/templates/box-locations/box-locations.php', $args);
        $this->loadTemplate( dirname( __FILE__ ) . '/templates/box-locations/box-locations_archive.php', $args);

        

        $this->the_modal_settings(true);

    }

    
	
    function add_custom_box($form, $lead) {

        //die("<pre>".print_r($_GET['page'], true)."</pre>");



        wp_enqueue_style('jqueryui-local-style');

        

        wp_enqueue_script('jquery-ui-tabs');

        wp_enqueue_media();
		
		


        // TODO: Fix issue with RAM on loading all website users

        //$all_therapists = $this->getAllLatLngTherapists();

        //echo("<pre>".print_r($all_therapists, true)."</pre>");

        $lead_id = gform_get_meta($lead['id'], 'pform_lead_id');



        if( !empty($lead_id) ) {            

            $lead2 = RGFormsModel::get_lead($lead_id);

            $form2 = RGFormsModel::get_form_meta($lead2['form_id']);

            /** Box Detail Form Primary **/

            GFEntryDetail::lead_detail_grid($form2, $lead2, false);

        }
		
        $locations = $this->get_job_location_by_lead($lead['id']);
        $firstlid = $this->getfirstlocationid($lead['id']);
		
        $id_multiple = $this->getIdFieldMultiple();

        $labels_location = get_option('gf_labels_location', array());

        /** Box Locations **/

        $user = new WP_User($lead['created_by']);

        $args = array(

            'lead' => $lead,

            'lang' => self::LANG,

            'locations' => $locations,
            'firstlocationid' => $firstlid,

            'labels_location' => $labels_location,

            'user_created' => $user,

            'required' => '*'

        );

        //die("<pre>box-".print_r($args, true)."</pre>");

        $this->loadTemplate( dirname( __FILE__ ) . '/templates/box-locations/box-locations.php', $args);
        //$this->loadTemplate( dirname( __FILE__ ) . '/templates/box-locations/box-locations_2.php', $args);

        

        

        $args['participants_massage'] = maybe_unserialize($lead[self::$field_id_participants]);



        /** Box Users corporate employee **/

        if($lead[self::$field_id_corporate_massage]) {

            $this->loadTemplate( dirname( __FILE__ ) . '/templates/box-users-employees/box-users-employees.php', $args);

        }

        



        /** Box Map **/

        $args['body_message'] = get_option('content_body_message_email_invoice', '');

        $this->loadTemplate( dirname( __FILE__ ) . '/templates/box-details/box-search-therapists.php', $args );



        $this->the_modal_settings(true);

    }

    

    

    /**

     * Filter Register Therapist

     * */

    function sm_registration_save($user_id, $config, $lead, $user_data_password) {

        $user = get_userdata($user_id);

        

        if( !empty($user_id) && empty($lead['created_by'])) {

            global $wpdb;

            $lead['created_by'] = $user_id;

            $wpdb->query('UPDATE '.$wpdb->prefix.'rg_lead SET created_by=\''.$user_id.'\' WHERE id=\''.$lead['id'].'\';');

        }

        

        if( user_can( $user_id, 'manage_jobs' ) ) { // role therapist            

            if(array_key_exists('latlng', $_POST)) {

                update_user_meta($user_id, 'address_latlng', $_POST['latlng']);

            }

            if(array_key_exists('input_'.self::$field_id_refer_friend, $_POST) && !empty($_POST['input_'.self::$field_id_refer_friend])) {

                $email_friend = $_POST['input_'.self::$field_id_refer_friend];

                $site_url = site_url();

                $message = <<<EOF

                    Hello %s,

                    

                    %s invited you to know our services.



                    Click in the following link: %s.

EOF;

                wp_mail($email_friend,__('Your friend has invited you', self::LANG),sprintf(__($message, self::LANG),$email_friend,$user->first_name,'<a style="color: '.self::$color_link_email.';" href="'.$site_url.'">'.$site_url.'</a>'));

            }

        } elseif( user_can( $user_id, 'read' ) ) { // role subscriber / client

            $user = $user->data;

            $creds = array();

            $creds['user_login'] = $user->user_login;

            $creds['user_password'] = $user_data_password;

            $creds['remember'] = false;

            $user = wp_signon( $creds, false );

            

            $this->add_location_submit_secundary_form($lead);

            

        }

    }



    function gform_afterupdate_entry($form, $entry_id) {

        //die("<pre>".print_r($lead, true)."</pre>");

        $entry = RGFormsModel::get_lead( $entry_id );



        $clients = unserialize( $entry[self::$field_id_participants] );



        //die("<pre>".print_r($lead, true)."</pre>");

        $this->gform_finalize_entry($entry, $form);

    }



    /**

     * Function called at the end of the save entry.

     * Used to register a new employee users when a secondary form is submited and the corporate massage is selected

     */

    function gform_finalize_entry($entry, $form) {
		global $wpdb;
		//print_r($entry);
		///print($form);
		//die();
		
		if( $entry['form_id'] == 11 ){
        
            for($i=0;$i<=20;$i++){

                $post_fields = array_keys(get_option('gf_labels_location', array()));	 
                
                //$value = AJ_Location::convertListObjects($_POST["input_50"]);
                /*print_r($value);
                print_r($post_fields);
                echo '<pre>';
                print_r($_POST);die(); 
                print_r($entry);*/
               

                $data = array();
                $it = 0;

                foreach($post_fields as $datas){
                    if($post_fields[$it]!=50){
                        $data[$datas] = $entry[$datas];
                    }
                    $it++;
                }
                
                if($i>0){

                    if(isset($_POST["input_52_".$i."_0"])){

                        $k = 0;
                        foreach($post_fields as $datas){
                            if(isset($_POST["input_".$datas."_".$i."_".$k])){
                                $data[$datas] = $_POST["input_".$datas."_".$i."_".$k];

                            }
                            
                        }

                    }else{
                        continue;
                    }
                   
                }

                //print_r($data);
                //die();

                
                $user_id = get_current_user_id();
                
                $data_columns = array(

                        'primary_location' => 1,

                        'created_by' => $user_id,

                        'users_invited' => null,

                        'accept_job' => null,

                        'id' => false

                    );
                //print_r($_POST);
                //print_r($data);die();
                $StartTime= $_POST["input_58"][0].':'.$_POST["input_58"][1].' '.$_POST["input_58"][2];
                $EndTime = $_POST["input_59"][0].':'.$_POST["input_59"][1].' '.$_POST["input_59"][2];
                $StartTime= $_POST["input_64"];
                $EndTime = $_POST["input_65"];
                $sst = strtotime($StartTime);
                $eet=  strtotime($EndTime);
                $diff= $eet-$sst;
                $timeElapsed= date("h.i",$diff);
                //echo $timeElapsed;die();
                
                $data[30] = $timeElapsed;
                $data[53] = '';
                $data["filled"] = "yes";
                $insert_id = AJ_Location::store_location(356,$data, $data_columns);
                
                //$listDates = array(0=>array('day'=>'','month'=>'','year'=>'','stime'=>'','etime'=>'') );
                //$value = AJ_Location::convertListObjects($_POST["input_50"]);
                $list = array();
                $list[0]->day = date('d',strtotime($_POST["input_53"]));
                $list[0]->month = date('m',strtotime($_POST["input_53"]));
                $list[0]->year = date('Y',strtotime($_POST["input_53"]));
                $list[0]->stime = $_POST["input_64"];//$_POST["input_58"][0].':'.$_POST["input_58"][1].':00 '.$_POST["input_58"][2];
                $list[0]->etime = $_POST["input_65"];//$_POST["input_59"][0].':'.$_POST["input_59"][1].':00 '.$_POST["input_59"][2];
                if($i>0){
                    $list = array();
                    $list[0]->day = date('d',strtotime($_POST["input_53_".$i."_6"]));
                    $list[0]->month = date('m',strtotime($_POST["input_53_".$i."_6"]));
                    $list[0]->year = date('Y',strtotime($_POST["input_53_".$i."_6"]));
                    $list[0]->stime = $_POST["input_64_".$i."_7"];//$_POST["input_58"][0].':'.$_POST["input_58"][1].':00 '.$_POST["input_58"][2];
                    $list[0]->etime = $_POST["input_65_".$i."_8"];
                }
                AJ_Location::addDatesEvent($insert_id,$list);
                
            }
		}

        //allow only submissions from Secondary Form (id=2)

        if($form['id']!=self::$job_form_id)

            return;



        if( !isset( $entry[self::$field_id_participants] ) ) {

            return true;

        }





        $clients = unserialize( $entry[self::$field_id_participants] );

        if($clients!==false && count($clients)>0) {

            $error = '';

            

            $email_subject = sprintf( __('[Welcome to %s] Your username and password', self::LANG), get_option('blogname') );

            $email_subject = html_entity_decode( $email_subject );

            $company_manager = $entry[self::$field_id_first_name].' '.$entry[self::$field_id_last_name];



            foreach ($clients as $user_email) {

                if(empty($user_email))

                    continue;



                //check if the registered user is the same of the event owner

                $owner_event = $entry[self::$field_id_email];

                if($user_email==$owner_event)

                    continue;



                $random_password = wp_generate_password( $length=8, $include_standard_special_chars=false );



                $user = get_user_by( 'email', $user_email );



                if($user===false) {

                    $user_id = wp_create_user( $user_email, $random_password, $user_email );

                    wp_update_user( array ('ID' => $user_id, 'role' => 'client_employee' ) ) ;



                    $message  = __('Hi there,', self::LANG) . "\r\n\r\n";

                    $message .= sprintf( __("Welcome to %s!", self::LANG), get_option('blogname')) . "\r\n\r\n";

                    $message .= sprintf( __("You have been registered because of your Company Manager (%s) "

                               ."has requested a Corporate Therapist Service for a Wellness Massage.\r\n"

                               ."Use the following link to log-in and separate your time slot on the massage service"), $company_manager)."\r\n\r\n";

                    $message .= wp_login_url() . "\r\n";

                    $message .= sprintf( __('Username: %s', self::LANG), $user_email ) . "\r\n";

                    $message .= sprintf( __('Password: %s', self::LANG), $random_password ) . "\r\n\r\n";

                    $message .= __('After the first login, you can change your password on Your Profile page.', self::LANG) . "\r\n\r\n";

                    $message .= sprintf( __('If you have any problems, please contact us at %s.', self::LANG), get_option('admin_email') ) . "\r\n\r\n";

                    

                    wp_mail(

                        $user_email,

                        $email_subject,

                        $message

                    );



                } else {

                    $user_id = $user->ID;

                    if( $user->ID>1 )

                        wp_update_user( array ('ID' => $user->ID, 'role' => 'client_employee' ) ) ;

                    

                    $message  = __('Hi there,', self::LANG) . "\r\n\r\n";

                    $message .= sprintf( __("Welcome to %s!", self::LANG), get_option('blogname')) . "\r\n\r\n";

                    $message .= __("Your Company Manager has requested a Corporate Therapist Service for a Wellness Massage.\r\n"

                               ."Use the following link to log-in with your credentials and separate your time slot on the massage service")."\r\n\r\n";

                    $message .= wp_login_url() . "\r\n\r\n";



                    $message .= sprintf( __('If you have any problems, please contact us at %s.', self::LANG), get_option('admin_email') ) . "\r\n\r\n";

                    

                    $invited = get_user_meta( $user_id, 'event-invited', true );

                    if($invited!='1') {

                        wp_mail(

                            $user_email,

                            sprintf( __('[%s] Corporate Wellness Massage', self::LANG), get_option('blogname') ),

                            $message

                        );

                    }

                }



                update_user_meta( $user_id, 'event-invited', '1' );

            }



            if(strlen($error)>0){

                $error = '<p>The following errors were ocurred when we were processing your Form Submission:</p>'."\n".$error;

                wp_mail($entry[3], 'Errors on the Massage Registration', $error);

            }

        }

    }

    

    /** 

     * Action on any new Entry form is created

     **/

    function gform_entry_created($lead, $form) {

        

        if(is_user_logged_in()) {

            // validations for the Secondary and Third Forms

            if($form['id'] == self::$job_form_id) {

                $this->add_location_submit_secundary_form($lead);

            } elseif( $this->service_review_form_id == $form['id']) {

                $user_id = get_current_user_id();

                $invoices = get_user_meta($user_id,'invoices_service_review',true);

                if( empty($invoices) ) {

                    $invoices = array();

                }

                

                $entry_id = $this->get_session_var('assoc_entry_invoice');

                

                if(!in_array($entry_id,$invoices)) {

                    $invoices[] = $entry_id;

                    update_metadata('user',$user_id,'invoices_service_review', $invoices);

                }

            }

            

        }

    }



    /**

     * used in functions: gform_entry_created, sm_registration_save

     * */

    function add_location_submit_secundary_form($lead) {

        $vlead = $this->get_session_var('pform_lead_id');

        if( !empty($vlead) ) {

            gform_update_meta($lead['id'], 'pform_lead_id', $vlead);

        }

        $this->delete_session_var('pform_lead_id');

        

        $post_fields = array_keys(get_option('gf_labels_location', array()));

        $idm = $this->getIdFieldMultiple($lead);

        $rows = empty($_POST['input_'.$idm]) ? array() : $_POST['input_'.$idm];

        $len = count($post_fields);

        

        for($j = $len-1; $j >= 0; $j--) {

            $idf = $post_fields[$j];

            $value = $_POST['input_'.$idf];

            

            if($idf == Admin_Jobs::$field_id_dates_and_events) {

                $value = AJ_Location::convertListObjects($value);

            } elseif(is_array($value)) {

                $sep = Admin_Jobs::get_separator_date();

                $value = implode($sep, $value);

            }

            array_unshift($rows,$value);

        }

        $this->save_location($post_fields,$lead['id'],$rows,$len,array('created_by' => $lead['created_by']) );

    }
	
	function getfirstlocationid($lead_id, $addiquery = ''){
		global $wpdb;
		$results = $wpdb->get_results('SELECT l.ID FROM '.$wpdb->prefix.AJ_Location::$tblLocation.' l WHERE l.lead_id='.$lead_id.' '.$addiquery.' order by l.ID asc limit 1;');
		return $results;
	}
	

    function get_job_location_by_lead($lead_id, $archived='no') {

        if( empty($lead_id) ) {

            return array();

        }
		/* echo $archived;
		echo 'ggg'; die(); */
        global $wpdb;
		$addiquery = 'and status not in("Archive","Delete","archive","delete")';
		if(isset($_GET["archive_location"]) && $_GET["archive_location"]=='yes'){
			//$addiquery = 'and status in("Archive","Delete")';
		}
		if($archived=='yes'){
			$addiquery = 'and l.status in("Archive")';
		}
		
		if(isset($_GET["event_year"]) && $_GET["event_year"]!=''){
			$addiquery .= 'and d.year = "'.$_GET["event_year"].'"';
		}
		
		if(isset($_GET["month"]) && $_GET["month"]!=''){
			$addiquery .= 'and d.month = "'.$_GET["month"].'"';
		}
		
		
		
        //$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.AJ_Location::$tblLocation.' LEFT JOIN   WHERE lead_id='.$lead_id.' '.$addiquery.';');
		
        $results = $wpdb->get_results('SELECT group_concat(d.day),group_concat(d.month),group_concat(d.year),group_concat(d.stime),group_concat(d.etime),l.ID,l.lead_id,l.data,l.created_by,l.users_invited,l.accept_job,l.primary_location FROM '.$wpdb->prefix.AJ_Location::$tblLocation.' l LEFT JOIN '.$wpdb->prefix.AJ_Location::$tblDateLocation.' d ON l.ID=d.location_id WHERE l.lead_id='.$lead_id.' '.$addiquery.' group by l.ID order by DATE_FORMAT(concat(d.year,"-",d.month,"-",d.day),"%Y-%m-%d") asc;');
		//
		//
		
		

        return $results;

    }

    

    public static function get_separator_date($format = '') {

        if(empty($format) && defined('GF_FORMAT_DATE')) {

            $format = GF_FORMAT_DATE;

        }

        switch($format) {

            case 'dmy_dash':

            case 'ymd_dash':

                return '-';

            break;

            case 'dmy_dot':

            case 'ymd_dot':

                return '.';

            break;            

            case 'mdy':

            case 'ymd_slash':

            case 'dmy':

            default:

                return '/';

            break;

        }

    }

    /**

     * Sessions functions

     * */

    function start_init() {

        if (!session_id()) {

            session_start();

        }

    }

    function set_session_var($var_name, $value) {

        if(session_id()) {

            $_SESSION[$var_name] = $value;

            return true;

        }

        return false;

    }

    function get_session_var($var_name) {

        if(session_id() && !empty($_SESSION[$var_name])) {

            return $_SESSION[$var_name];

        }

        return false;

    }

    function delete_session_var($var_name) {

        if(session_id() && isset($_SESSION[$var_name])) {

             unset($_SESSION[$var_name]);

        }

    }

    /** Functions Therapist Section **/

    function pagination_jobs() {

        global $paged;

        $temp = $paged; 

        if( !empty($_POST['paged']) ) {

            $paged = $_POST['paged'];

        }

        echo $this->front_therapist_panel($_POST['attrs'], true);

        $paged = $temp;

        die();

    }

    function front_therapist_panel($attrs = array(), $calling_ajax = false) {

		
		
		
        

        $date = null;

        if( !empty($_REQUEST['search']) && is_array($_REQUEST['search']) && !empty($_REQUEST['search']['year']) ) {

            $date = $_REQUEST['search']['year'].'{month}';

            $date = str_replace('{month}',(!empty($_REQUEST['search']['month']) ? '-'.$_REQUEST['search']['month'] : ''),$date);

        }

        $locations = $this->get_available_locations_user(get_current_user_id(),$date);
		//print_r($locations);
        

        $total_locations = count($locations);



        $atbts = empty($_REQUEST['attrs']) ? $attrs : $_REQUEST['attrs'];



        $num_page = 1;

        $limit_start = 0;

        $end_location = $this->number_locations_per_page;

        global $paged;

        if( $paged > 0 ) {

            $num_page = $paged;

            $limit_start = ($num_page - 1)*$this->number_locations_per_page;

        }

        $locations = array_slice($locations,$limit_start,$end_location);

        $count_locations = count($locations);

        

        $exists_pages = $total_locations > $this->number_locations_per_page;

        $items_pages = array();

        if($exists_pages) {

            $total_pages = ($total_locations%$this->number_locations_per_page == 0) ? intval($total_locations/$this->number_locations_per_page): intval($total_locations/$this->number_locations_per_page) + 1;



            $stpage = $num_page - $this->pag_count_before;

            $stpage = ( $stpage > 0) ? $stpage : 1;

            

            $endpage = ($this->pag_count_after+1)+$num_page;

            $endpage = $endpage > $total_pages ? $total_pages+1 : $endpage;



            $limit_pages = $endpage - $stpage;

            

            $items_pages = array_fill( $stpage, $limit_pages, '');

            if($atbts['enabled_pagination_ajax'] == 'true') {

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="javascript:void(0);" onclick="goPage('.$page.')">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="javascript:void(0);" onclick="goPage('.($num_page-1).')"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="javascript:void(0);" onclick="goPage('.($num_page+1).')"><i class="icon-chevron-right"></i></a></li>';

            } else {

                $post_id = $atbts['post_id'];

                $permalink_structure = get_option('permalink_structure', '');

                if( empty($permalink_structure) ) {

                    $url = get_permalink($post_id).'&paged=%s';

                } else {

                    $url = get_permalink($post_id);

                    $url = substr($url,-1) == '/' ? $url.'page/%s/' : $url.'/page/%s/';

                }                

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="'.sprintf($url,$page).'">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="'.sprintf($url,($num_page-1)).'"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="'.sprintf($url,($num_page+1)).'"><i class="icon-chevron-right"></i></a></li>';



            }

            

            $items_pages[$num_page] = '<li class="paged active"><a><b>'.$num_page.'</b></a></li>';

            ksort($items_pages);

        }

        $labels = array();

        if(count($locations) > 0) {

            $labels = get_option('gf_front_labels_location', array());

        }

        $args = array(

            'lang' => self::LANG,

            'locations' => $locations,

            'labels' => $labels,

            'pages' => $items_pages,

            'showing' => $count_locations,

            'total_locations' => $total_locations,

            'is_request_ajax' => $calling_ajax,

            'attrs' => $attrs,

            'exclude_fields' => array(

                //self::$field_id_first_name,

                //self::$field_id_last_name,

                //self::$field_id_number_therapist,

                //self::$field_id_phone,

                //self::$field_id_aproximate_total_of,

                self::$field_id_dates_and_events,
				//self::$field_id_onsite_person_email,
                self::$field_id_time_zone

            )

        );

        return $this->loadTemplate( dirname( __FILE__ ) . '/templates/panel-therapist/panel-therapist.php', $args, true);

    }

    

    

    function get_available_locations_user($user_id = null,$date = null) {

        if( empty($user_id) ) {

            $user_id = get_current_user_id();

        }

        $locations = $this->get_locations_user($user_id,false,$date);
        

        $available_locations = array();

        if( !empty($locations) ) {

            foreach($locations as $location) {

                $arr_values = maybe_unserialize($location->data);

                $arr_accept = maybe_unserialize($location->accept_job);

                //echo '<pre>';
				//print_r($location);

                $accept_filters = empty($arr_accept) ? array() : array_filter($arr_accept);

                //echo '<pre>';
				//print_r($accept_filters);
				
                if( $this->exists_available_job($location, $user_id) ) {
				
					if(array_key_exists($user_id, $accept_filters) && in_array($location->ID, $accept_filters[$user_id])){
						$location->accepted = 1;
					}else{
						$location->accepted = 0;
					}
					
                    $available_locations[] = $location;

                }

            }
	
        }
		//print_r($available_locations);
        return $available_locations;

        

    }

    function get_available_locations_created($user_created_by = null, $date = null, $onlySpored = false) {

        if( is_null($user_created_by) ) {

            $user_created_by = get_current_user_id();

        }



        $locations = $this->get_locations_user($user_created_by, true, $date,null, null, $onlySpored);

        $available_locations = array();

        

        if( !empty($locations) ) {

            

            foreach($locations as $loc) {

                if(empty($loc->accept_job)) {
					//
                    $loc->exists_vacantes = true;
					if(isset($_GET["location_listing"])){
						if($_GET["location_listing"]==$loc->ID)
						$available_locations[] = $loc;
						
					}else{
						$available_locations[] = $loc;
						
					}
                    

                    continue;

                }

                $arr_values = maybe_unserialize($loc->data);

                $arr_accept = maybe_unserialize($loc->accept_job);

                $accept_filters = array_filter($arr_accept);

                

                $loc->exists_vacantes = $this->exists_available_job($loc);

               
				if($_GET["location_listing"]){
					if($_GET["location_listing"]==$loc->ID)
					$available_locations[] = $loc;
					
				}else{
					$available_locations[] = $loc;
					
				}
				
            }

        }

        return $available_locations;

        

    }

    function get_location_user($location_id, $user_id = null) {

        if( empty($user_id) ) {

            $user_id = get_current_user_id();

        }

        $loc = $this->load_location($location_id);

        $loc->accepted = false;

        if( !empty($loc->accept_job) ) {

            $accept = maybe_unserialize($loc->accept_job);

            $loc->accepted = array_key_exists($user_id, $accept) && !empty($accept[$user_id]);

        }

        return $loc;

    }



    function get_locations_user($user_id = null, $created_by = false,$date = null,$limit_start = null, $limit_end = null, $isSponsored = false) {

        if( is_null($user_id) ) {

            $user_id = get_current_user_id();

        }

        $limit = '';

        if( !is_null($limit_start) && !is_null($limit_end) ) {

            $limit = ' LIMIT '.$limit_start.', '.$limit_end;

        }

        global $wpdb;

        // Si se esta consultando por el usuario que la creó, se puede filtrar directamente

        if($created_by) {

            $uid = $user_id == 0 ? null : $user_id;
            
            return AJ_Location::get_confirmation_events($uid, $date, $isSponsored, false, 0, false );

        } else { //si no, se debe buscar sobre toda la BD cuales locations tienen una invitación al usuario therapist actual

           

            $allLocations = AJ_Location::get_confirmation_events(null, $date, $isSponsored, false, $user_id);



            //dev:lamprea:11-mar-2014: disabled this for, due to improvement in database search

            /*foreach($allLocations as $i=>$location) {

                if(!is_null($location->users_invited)) {

                    $list = maybe_unserialize($location->users_invited);

                    if(is_array($list) && !array_key_exists($user_id, $list)) {

                        unset($allLocations[$i]);

                    }

                } else {

                    unset($allLocations[$i]);

                }

            }*/

            return $allLocations;

        }

        return array();

    }

    /*

     * Function response ajax to action: "therapist_action_job"

     * Used when a therapist wants to Accept or confirm an event location

     */

    function therapist_action_job() {
		//error_reporting(E_ALL);
		//ini_set('display_errors',1);
        

        $location_id = $_POST['location_id'];

        $response = $_POST['response'];

        $location_date_id = $_POST['location_date_id'];

        

        $location = $this->load_location($location_id);

        

        $result = array(

            'message' => __('Validation error.', self::LANG),

            'status' => 'error'

        );

        $user_id = get_current_user_id();



        if($this->exists_available_job($location, $user_id)) {

            

            



            if($response == 'yes') {
				
				$new_accepts = (array)maybe_unserialize($location->accept_job);
				
                if($location->accept_job=='' || !isset($new_accepts[$user_id]) || !in_array($location_date_id, $new_accepts[$user_id]) || !array_key_exists($location_date_id, $new_accepts[$user_id])) {
					
					
                    $new_accepts[$user_id] = array($location_date_id=>$location_date_id);

                }                
				
                $result['message'] = sprintf(__('Your request has been saved. The site administrator will be contacting you via email to finalize the details of the %s', self::LANG), $_POST['attrs']['name']);

            } else {

                if( !is_array($new_accepts[$user_id]) ) {

                    $new_accepts[$user_id] = array();

                }

                if( in_array($location_date_id, $new_accepts[$user_id]) ) {

                    unset($new_accepts[$user_id][$location_date_id]);

                }

                $result['message'] = __('Your request has been saved.', self::LANG);

            }



            

            $result['status'] = 'OK';



            $new_accepts = maybe_serialize($new_accepts);
			
            $data_columns = array(

                'primary_location' => $location->primary_location,

                'created_by' => $location->created_by,

                'users_invited' => $location->users_invited,

                'accept_job' => $new_accepts,

                'id' => $location->location_id

            );
			
			//print_r($_POST); print_r(unserialize($location->data)); die();
			
			$user_info = get_userdata($user_id);
			$user_name = $user_info->first_name;

			$user_email = $user_info->user_email;
			$subject = 'Event Reminder';
			$customerdata = unserialize($location->data);
			
			$time_start = date('l jS \of F Y h:i:s A', strtotime($location->year."-".$location->month."-".$location->day." ".$location->stime));
			$time_end = date('l jS \of F Y h:i:s A', strtotime($location->year."-".$location->month."-".$location->day." ".$location->etime));
			
			/*$content = "Dear ".$user_name.", we're sending you a friendly reminder that your event, ".$customerdata[8]." will occur on ".$time_start." to ".$time_end.", for event details click <a href='".site_url('/my-events/')."'>here</a>.";
			wp_mail($user_email, $subject, $content, '', false);*/
			

			
			if($response == 'yes') {
				
				$isSend = wp_mail($customerdata[44], "Your job has been accepted by a therapist", "Dear ".$customerdata[33].", your job has been accepted by a therapist for That's The Spot Massage Therapy. For event details click <a href='".site_url('/event-listing/')."'>here</a>.", '', false);
				
				//$isSend = wp_mail($customerdata[44], "Your job has been accepted by a therapist", "Dear customer your job has been accepted by therapist on site That's the Spot Massage Therapy", '', false);
				
				//$isSend = wp_mail("gpsbaroli@gmail.com", "Job has accepted by therapist", "Dear customer your job has been accepted by therapist on site That's the Spot Massage Therapy", '', false);
				
			}
			
            AJ_Location::store_location($location->lead_id,$location->data,$data_columns);

            

        } else {

            $result = array(

                'message' => __('We are sorry but the jobs has been filled for this location', self::LANG),

                'status' => 'info'

            );

        }
		
        echo json_encode($result);

        die();

    }

	
    /**

     * Function that determines, if a location(job) has spaces available

     * */

    function exists_available_job($location, $user_id = false) {
        //echo $user_id.'lll';
        if(!is_object($location)) {

            $location = $this->load_location($location);

        }

        $arr_values = maybe_unserialize($location->data);

        

        $arr_accept = maybe_unserialize($location->accept_job);

        

        if(empty($arr_accept)) {

            //return false;

        }

        $accept_filters =  array_filter($arr_accept);

        $amount_acccepted = count( $accept_filters );

        

        $vacantes = $arr_values[self::$field_id_number_therapist];

        $amount_vacantes = $vacantes - $amount_acccepted;

        $accept_job = maybe_unserialize($location->accept_job);

        

        if($user_id) {
            $passed = 0;
            if(is_array($accept_filters)) {

                foreach($accept_filters as $userId => $listAccept) {

                    if($user_id !== $userId && in_array($location->ID, $listAccept)) {
                        //updated for display non accepted location
                        //return false;
                        $passed = 1;
                    }

                }

            }

            if($passed == 0){
                //return false;
            }

            return ($amount_vacantes > 0) || ( array_key_exists($user_id, $accept_filters) && in_array($location->ID, $accept_filters[$user_id]) );

        }

        return ($amount_vacantes > 0);

    }

   static function load_location($location_id) {

        if( empty($location_id) ) {

            return null;

        }

        global $wpdb;

        return $wpdb->get_row('SELECT d.*,l.lead_id,l.data,l.created_by,l.users_invited,l.accept_job,l.primary_location FROM '.$wpdb->prefix.AJ_Location::$tblLocation.' l LEFT JOIN '.$wpdb->prefix.AJ_Location::$tblDateLocation.' d ON l.ID=d.location_id WHERE l.ID='.$location_id.';');

    }

    function save_location($post_fields, $lead_id, $all_data, $data_length, $data_columns) {

	
	
        $defaults = array (

            'created_by' => null,

            'users_invited' => null,

            'accept_job' => null,

            'id' => false

        );

        $sep = Admin_Jobs::get_separator_date();

        $columns = wp_parse_args( $data_columns, $defaults );



        if( !empty($all_data) ) {

            $data = array();

            $centinela = true;

            $k = 0;

            $idx = 0;

            for($k = 0; $k < count($all_data); $k++) {

                $idx2 = $idx;

                $idx = $k%$data_length;



                $insert_id = null;

                if($k > 0 && $idx == 0) {

                    if($centinela) {

                        $columns['primary_location'] = 1;

                        $centinela = false;

                    } else {

                        $columns['primary_location'] = 0;

                    }

                    $insert_id = AJ_Location::store_location($lead_id, $data, $columns);

                    $data = array();

                }

                if($post_fields[$idx2] == Admin_Jobs::$field_id_dates_and_events) {



                    if( isJson($all_data[$k-1]) ) {// if string json

                        $listDates = json_decode($all_data[$k-1]);



                        AJ_Location::addDatesEvent($insert_id,$listDates);

                    } elseif(is_array($all_data[$k-1])) {



                        AJ_Location::addDatesEvent($insert_id,$all_data[$k-1]);

                    }

                } else {

                    $data[$post_fields[$idx2]] = isset($all_data[$k-1])?$all_data[$k-1]:'';

                }

            }

            if($centinela) {

                $columns['primary_location'] = 1;

                $centinela = false;

            } else {

                $columns['primary_location'] = 0;

            }

            

            $insert_id = AJ_Location::store_location($lead_id, $data, $columns);



            if($post_fields[$idx] == Admin_Jobs::$field_id_dates_and_events) {

                if( isJson($all_data[$k-1]) ) {// if string json

                    $listDates = json_decode($all_data[$k-1]);

                    

                    AJ_Location::addDatesEvent($insert_id,$listDates);

                } elseif(is_array($all_data[$k-1])) {

                    AJ_Location::addDatesEvent($insert_id,$all_data[$k-1]);

                }

            }

        }

    }

    

    

    /**

     * Function User Section

     * *******************************************

     * Function for render shortcode [events-user]

     * */

    function front_events_user($attrs = array(), $calling_ajax = false) {

        wp_localize_script( 'events-user-js', 'evUser2', array('attrs' => $attrs) );

        

        wp_enqueue_script('events-user-js');

        

        $date = '';

        if( !empty($_REQUEST['search']) && is_array($_REQUEST['search']) && !empty($_REQUEST['search']['year']) ) {

            $date = $_REQUEST['search']['year'].'{month}';

            $date = str_replace('{month}',

                        (!empty($_REQUEST['search']['month']) ? '-'.$_REQUEST['search']['month'] : ''), $date);

        }



        $atbts = empty($_REQUEST['attrs']) ? $attrs : $_REQUEST['attrs'];





        $uid = $atbts['public'] === 'true' ? 0 : get_current_user_id();

        $locations = $this->get_available_locations_created($uid,$date, $atbts['only_spored'] == 'true');



        $total_locations = count($locations);



        

        $num_page = 1;

        $limit_start = 0;

        $end_location = $this->number_locations_per_page;

        global $paged;

        if( $paged > 0 ) {

            $num_page = $paged;

            $limit_start = ($num_page - 1)*$this->number_locations_per_page;

        }

        $locations = array_slice($locations,$limit_start,$end_location);

        $count_locations = count($locations);

        

        $exists_pages = $total_locations > $this->number_locations_per_page;

        $items_pages = array();

        if($exists_pages) {

            $total_pages = ($total_locations%$this->number_locations_per_page == 0) ? intval($total_locations/$this->number_locations_per_page): intval($total_locations/$this->number_locations_per_page) + 1;



            $stpage = $num_page - $this->pag_count_before;

            $stpage = ( $stpage > 0) ? $stpage : 1;

            

            $endpage = ($this->pag_count_after+1)+$num_page;

            $endpage = $endpage > $total_pages ? $total_pages+1 : $endpage;



            $limit_pages = $endpage - $stpage;

            

            $items_pages = array_fill( $stpage, $limit_pages, '');

            if($atbts['enabled_pagination_ajax'] == 'true') {

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="javascript:void(0);" onclick="goPage('.$page.')">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="javascript:void(0);" onclick="goPage('.($num_page-1).')"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="javascript:void(0);" onclick="goPage('.($num_page+1).')"><i class="icon-chevron-right"></i></a></li>';

            } else {

                $post_id = $atbts['post_id'];

                $permalink_structure = get_option('permalink_structure', '');

                if( empty($permalink_structure) ) {

                    $url = get_permalink($post_id).'&paged=%s';

                } else {

                    $url = get_permalink($post_id);

                    $url = substr($url,-1) == '/' ? $url.'page/%s/' : $url.'/page/%s/';

                }                

                foreach($items_pages as $page=>$value) $items_pages[$page] = '<li class="paged"><a href="'.sprintf($url,$page).'">'.$page.'</a></li>';

                

                if($num_page > 1) $items_pages[0] = '<li class="arrow-left paged"><a href="'.sprintf($url,($num_page-1)).'"><i class="icon-chevron-left"></i></a></li>';

                

                if( ($num_page+1) <= $total_pages) $items_pages[] = '<li class="arrow-right paged"><a href="'.sprintf($url,($num_page+1)).'"><i class="icon-chevron-right"></i></a></li>';

            }

            

            $items_pages[$num_page] = '<li class="paged active"><a><b>'.$num_page.'</b></a></li>';

            ksort($items_pages);

        }

        $labels = array();

        if(count($locations) > 0) {

            $labels = get_option('gf_front_labels_location', array());

        }

        $args = array(

            'lang' => self::LANG,

            'locations' => $locations,

            'labels' => $labels,

            'pages' => $items_pages,

            'showing' => $count_locations,

            'total_locations' => $total_locations,

            'is_request_ajax' => $calling_ajax,

            'attrs' => $attrs,

            'link_appointment' => $this->get_url_appointment(is_user_logged_in())

        );

        if($attrs['public'] === 'true') {

            $args['exclude_fields'] = array(

                //self::$field_id_first_name,

                //self::$field_id_last_name,

                self::$field_id_number_therapist,

                self::$field_id_phone,

                self::$field_id_aproximate_total_of,

                self::$field_id_dates_and_events,

                self::$field_id_time_zone

            );

            return $this->loadTemplate( dirname( __FILE__ ) . '/templates/public-events/public-events.php', $args, true);

        } else {

            $args['exclude_fields'] = array(

                //self::$field_id_number_therapist,

                //self::$field_id_phone,
                //self::$field_id_onsite_person_email,

                //self::$field_id_aproximate_total_of,

                //self::$field_id_dates_and_events

            );

            return $this->loadTemplate( dirname( __FILE__ ) . '/templates/events-user/events-user.php', $args, true);

        }

    }





    function func_pagination_events() {

        global $paged;

        $temp = $paged; 

        if( !empty($_POST['paged']) ) {

            $paged = $_POST['paged'];

        }

        

        echo $this->front_events_user($_POST['attrs'], true);

        $paged = $temp;

        die();

    }

    /**

     **** end functions user section ******/



    

    /**

     * Response Ajax action: modal_view_job

     * */

    public function modal_view_lead() {

        

        if(isset($_REQUEST['l'])) {

            $lead_id = $_REQUEST['l'];

            if($lead_id) {

                

                $lead = RGFormsModel::get_lead($lead_id);

                $form = RGFormsModel::get_form_meta($lead['form_id']);

                if(!class_exists('GFEntryDetail')) {

                    require_once(ABSPATH.'/wp-content/plugins/gravityforms/entry_detail.php');

                }

                $lead_id2 = gform_get_meta($lead['id'], 'pform_lead_id');

                

                $loc = $this->get_location_user($_REQUEST['j']);

                global $smglobal_vars;

                

                $content_class = 'full-width'; ?>

                <div class="tmpl-raw">

                    <div id="wrapper-content" class="total-width">

                        <div id="content" class="<?php echo $content_class.' '.apply_filters('sm_class_content', ''); ?>">

                            <div id="grid_lead"><?php 

                            if( !empty($lead_id2) ) {            

                                $lead2 = RGFormsModel::get_lead($lead_id2);

                                $form2 = RGFormsModel::get_form_meta($lead2['form_id']);

                                

                                GFEntryDetail::lead_detail_grid($form2, $lead2, false);

                            }

                            GFEntryDetail::lead_detail_grid($form, $lead, false);

                            ?></div>

                        </div>

                        <?php

                            if(current_user_can('manage_jobs')) {

                                if($loc->accepted): ?>

                                <div class="text-center modal-job"><div class="alert alert-info message"><?php printf($smglobal_vars['textCancelJob'],$_REQUEST['n']); ?></div></div>

                                <?php else: ?>

                                <div class="text-center modal-job"><button class="button-primary btn btn-success btn-large" onclick="acceptJob(this,<?php echo $loc->location_id.','.$loc->ID; ?>);" data-loading-text="<?php _e('Sending', self::LANG); ?>"><?php printf($smglobal_vars['textAccept'], $_REQUEST['n']); ?></button></div>

                                <?php endif;

                            }

                        ?>

                        <div class="clr"></div>

                    </div>

                    <script type="text/javascript" src="<?php echo plugins_url('/templates/box-locations/box_locations.js', __FILE__); ?>"></script>

                    <script type="text/javascript">

                        jQuery(document).on('refresh_page_ajax',modal_view_lead);

                        function modal_view_lead() {

                            jQuery('#grid_lead table').addClass('table table-striped');

                            jQuery('#grid_lead table table').removeClass('table-striped').addClass('table-bordered');

                        }

                    </script>

                </div>

                <?php

            }

            

        }

        die();

    }



    public static function getTimesReservedUser($user_id,$lead_id) {

        

        global $wpdb;



        $tbl = $wpdb->prefix.'confirm_events_employee';

        $query = 'SELECT * FROM '.$tbl.' WHERE user_id=%s AND lead_id=%s;';

        

        return $wpdb->get_results($wpdb->prepare($query,$user_id,$lead_id));

    }

    /**

     *

     * Check if a user pay the invoice

     *

     *****/

    public static function checkIfPayService($entry_id, $user_id = null, $location_date_id = null) {

        if(!$location_date_id) {

            return false;

        }

        if(!$user_id) {

            $user_id = get_current_user_id();

        }



        $key = str_replace('{uid}', $user_id, KEY_STATUS_USER);// constant KEY_STATUS_USER define in functions theme



        $status = gform_get_meta($entry_id, 'status_payment_job');



        $status_payment = empty($status) ? array() : get_object_vars( json_decode($status) );



        /**

         * Payment Freshbooks

         ***/

        $obj = new ThatsSpotMassage_FreshBooks();

        $invoice = $obj->getInvoiceByEntry($user_id,$entry_id);



        

        return (array_key_exists($key, $status_payment) && $status_payment[$key]->status == 'Success' && in_array($location_date_id, $status_payment[$key]->locationsDatesIds) ) || ( !is_null($invoice) && $invoice->status == 'paid' );



    }





    /**

     *

     * Esta funcion es utilizada para actualizar los datos de los usuarios desde el formulario de "confirm events"

     * http://spotmassage.smadit.com/confirm-events/

     *

     *********/

    function ajax_update_profile() {



        $user_id = get_current_user_id();



        if( !empty($_POST['first_name']) ) {

            update_user_meta($user_id, 'first_name', $_POST['first_name']);

        }

        if( !empty($_POST['last_name']) ) {

            update_user_meta($user_id, 'last_name', $_POST['last_name']);

        }

        if( !empty($_POST['user_department']) ) {

            update_user_meta($user_id, 'user_department', $_POST['user_department']);

        }

        if( !empty($_POST['user_position']) ) {

            update_user_meta($user_id, 'user_position', $_POST['user_position']);

        }

        if( !empty($_POST['user_address']) ) {

            update_user_meta($user_id, 'user_address', $_POST['user_address']);

        }



        die(json_encode(array('status' => 'ok')));

     }



     /**

      * this function is used in the file /templates/events-user/events-user.php

      ***/

     public static function getTimes($user_id,$list = array()) {

        foreach($list as $item) {

            if($item['user_id'] == $user_id) {

                return $item;

            }

        }

        return null;

    }





    function delete_therapist_user( $user_id ) {

        global $wpdb;

        $sql = "SELECT id FROM ".$wpdb->prefix."rg_lead WHERE created_by=".$user_id;

        $leads = $wpdb->get_col($sql);



        if ( $leads ) {

            foreach ($leads as $lead) {

                $sql = "DELETE FROM ".$wpdb->prefix."rg_lead WHERE id=".$lead;

                $wpdb->query( $sql );



                $sql = "DELETE FROM ".$wpdb->prefix."rg_lead_detail WHERE lead_id=".$lead;

                $wpdb->query( $sql );



                $sql = "DELETE FROM ".$wpdb->prefix."rg_lead_meta WHERE lead_id=".$lead;

                $wpdb->query( $sql );



                $sql = "DELETE FROM ".$wpdb->prefix."rg_lead_notes WHERE lead_id=".$lead;

                $wpdb->query( $sql );



                $sql_loc = "SELECT ID FROM ".$wpdb->prefix."job_location WHERE lead_id=".$lead;

                $locations = $wpdb->get_col($sql_loc);

                if($locations){

                    foreach ($locations as $loc) {

                        $sql = "DELETE FROM ".$wpdb->prefix."location_date_event WHERE location_id=".$loc;

                        $wpdb->query( $sql );

                    }

                }



                $sql = "DELETE FROM ".$wpdb->prefix."job_location WHERE lead_id=".$lead;

                $wpdb->query( $sql );

            }

        }



        $sql = "SELECT id FROM ".$wpdb->prefix."confirm_events_employee WHERE user_id=".$user_id;

        $user = $wpdb->get_var($sql);

        if($user) {

            $sql = "DELETE FROM ".$wpdb->prefix."confirm_events_employee WHERE user_id=".$user_id;

            $wpdb->query( $sql );

        }

    }



}







class AJ_Location {

    
	
    static $tblLocation = 'job_location';

    static $tblDateLocation = 'location_date_event';

    /**

     * this function store a location

     **/
	
	function addmorelocation(){
		
		$post_fields = array_keys(get_option('gf_labels_location', array()));
		//$value = AJ_Location::convertListObjects($_POST["input_50"]);
		/* print_r($value);
		print_r($post_fields);die(); */
		$data = array();
		$it = 0;
		//echo '<pre>';print_r($_POST);die();
		
		foreach($_POST as $datas){
			
			if($post_fields[$it]!=50){
				$data[$post_fields[$it]] = $datas;
			}
			$it++;
			
		}
		
		$user_id = get_current_user_id();
		
		$data_columns = array(

                'primary_location' => 1,

                'created_by' => $user_id,

				'users_invited' => null,

				'accept_job' => null,

				'id' => false

            );
		
		$insert_id = AJ_Location::store_location($_POST['lid'],$data, $data_columns);
		
		//$listDates = array(0=>array('day'=>'','month'=>'','year'=>'','stime'=>'','etime'=>'') );
		$value = AJ_Location::convertListObjects($_POST["input_50"]);
		AJ_Location::addDatesEvent($insert_id,$value);
		
		die();
		
	}
	
	function updatelocation(){
		global $wpdb;
		
		$post_fields = array_keys(get_option('gf_labels_location', array()));
		//$value = AJ_Location::convertListObjects($_POST["input_50"]);
		/* print_r($value);
		print_r($post_fields);die(); */
		//echo '<pre>';print_r($_POST);die();
		$data = array();
		$it = 0;
		foreach($_POST as $datas){
			
			if($post_fields[$it]!=50){
				$data[$post_fields[$it]] = $datas;
			}
			$it++;
			
		}
		
		$user_id = get_current_user_id();
		
		$data_columns = array(

                'primary_location' => 1,

                'created_by' => $user_id,

				'users_invited' => null,

				'accept_job' => null,

				'id' => $_POST["locid"]

            );
			
		//print_r($data_columns); die();
		
		$insert_id = AJ_Location::store_location($_POST['lid'],$data, $data_columns);
		
		//$listDates = array(0=>array('day'=>'','month'=>'','year'=>'','stime'=>'','etime'=>'') );
		$value = AJ_Location::convertListObjects($_POST["input_50"]);
		
		$sql = "DELETE FROM ".$wpdb->prefix."location_date_event WHERE location_id=".$_POST["locid"];
		$wpdb->query( $sql );
		
		AJ_Location::addDatesEvent($insert_id,$value);
		
		die();
		
	}
    public static function store_location($lead_id, $data, $data_columns) {

        global $wpdb;
		
        if(isset($data["filled"]) && $data["filled"]=="yes"){
			if(!is_serialized($data)) {
            unset($data["filled"]);
			}
        }else{
		$StartTime= $_POST["input_50"][3];
        $EndTime = $_POST["input_50"][4];
        $sst = strtotime($StartTime);
        $eet=  strtotime($EndTime);
        $diff= $eet-$sst;
        $timeElapsed= date("h.i",$diff);
			if(!is_serialized($data)) {
				$data[30] = $timeElapsed;
			}
        }
        if(!is_serialized($data)) {

            $data = maybe_serialize($data);

        }
		
		
        
		$defaults = array(

            'primary_location' => 0,

            'lead_id' => $lead_id, 

            'data' => $data,

            'created_by' => null,

            'users_invited' => null,

            'accept_job' => null,

            'id' => false

        );

        $columns = wp_parse_args( $data_columns, $defaults );

        if(!$columns['id']) {

            $wpdb->insert($wpdb->prefix.self::$tblLocation, $columns);

            return $wpdb->insert_id;

        } else {

            $wpdb->update($wpdb->prefix.self::$tblLocation, $columns, array('ID' => $columns['id']));

            return $columns['id'];

        }

    }



    /**

     * Esta funcion es usada en el momento del "submit" del formulario "submit job"

     * Ver la funcion: add_location_submit_secundary_form()

     ***/

    public static function convertListObjects($arr) {

        global $smglobal_vars;

        if(! isset($smglobal_vars['lengthFieldMDates']) ) {

            $smglobal_vars['lengthFieldMDates'] = 5;

        }

        if(is_array($arr)) {

            $len = count($arr);

            if($len <= 0) {

                return array();

            }

            $list = array();

            for($i = 0,$j = 0, $list[$j] = new stdClass; $i < $len; $i++) {

                $k = $i%$smglobal_vars['lengthFieldMDates'];

                

                switch($k) {

                    case 0://day

                        $list[$j]->day = $arr[$i];

                        break;

                    case 1://month

                        $list[$j]->month = $arr[$i];

                        break;

                    case 2://year

                        $list[$j]->year = $arr[$i];

                        break;

                    case 3://start time

                        $list[$j]->stime = $arr[$i];

                        break;

                    case 4://end time

                        $list[$j]->etime = $arr[$i];

                        $j++;

                        if($i+1 != $len) {

                            $list[$j] = new stdClass;

                        }

                        break;

                }

            }

            return $list;

        }

        return null;

    }



    /**

     * Funcion usada para agredar las fechas de la funcionalidad de multiples fechas

     * en la tabla llamada: "location_date_event"

     *****/

    public static function addDatesEvent($location_id, $listDates = array()) {



        if( !empty($location_id) && !empty($listDates) ) {

            global $wpdb;



            $inserts = '';

            foreach ($listDates as $date) {

                //$inserts .= ',('.$date->day.','.$date->month.','.$date->year.','.$date->stime.','.$date->etime.')';

                $inserts .= ','.$wpdb->prepare('( %s, %s, %s, %s, %s, %s )',

                    $location_id, 

                    $date->day, 

                    $date->month, 

                    $date->year,

                    date('H:i:s', strtotime( ($date->stime == '12:00 m' ? '12:00 pm' : $date->stime) ) ),

                    date('H:i:s', strtotime( ($date->etime == '12:00 m' ? '12:00 pm' : $date->etime) ) )

                );

            }

            $inserts = substr($inserts, 1);

            //$wpdb->prefix.'invitations_job'

            return $wpdb->query( sprintf( "INSERT INTO %s ( location_id, day, month, year, stime, etime ) VALUES %s;",

                $wpdb->prefix.self::$tblDateLocation,

                $inserts)

            );

            

        }

        return false;

    }



    /**

     * Esta funcion permite obtener los "location dates" de un "location"

     *

     * Actualmente esta funcion es usada en una llamada ajax disparada por el action "getlocationdates"

     * que es ejecutada por una funcion javscript llamada "blViewDatesEvents"

     *****/

    public static function getDatesEventByLocation($location_id = null) {

        $ifViaAjax = !empty($_POST['location_id']) || isset($_REQUEST['action']);

        if( $ifViaAjax ) {
            $location_id = $_POST['location_id'];

        }
        if( empty($location_id) ) {

            return array();

        }

        global $wpdb;



        $list = $wpdb->get_results( $wpdb->prepare('SELECT lde.* FROM '.$wpdb->prefix.self::$tblDateLocation.' lde WHERE lde.location_id=%d ORDER BY lde.year, lde.month, lde.day, lde.stime, lde.etime;',$location_id));



        if( $ifViaAjax ) {

            $result = array(

                'status' => 'OK',

                'message' => __('Ok',Admin_Jobs::LANG),
                'results' => $list
            );

            die(json_encode($result));
        }

        return is_null($list) ? array() : $list;

    }


    public static function get_confirmation_events($user,$filter_date = null,$onlyLocationTypeCorporate = false,$onlyPrimary = false, $therapist_id=0, $assigned = true) {

		$objUser = is_numeric($user) ? get_user_by('id', $user) : get_user_by('email', $user);
        
        $user = empty($objUser) ? 0 : $user;

        global $wpdb;

        $where = $onlyPrimary ? 'AND l.primary_location=1 ' : '';

		$where .= " AND l.status!='delete' and l.status!='archive' ";

		if(isset($_GET["location_listing"]) && $_GET["location_listing"]!=''){
			$where .= 'AND l.id='.$_GET["location_listing"]." ";
		}
		
        if(!is_numeric($user) && 0) {
			
			
            $len = strlen($objUser->user_email);

            $fp = Admin_Jobs::$field_id_participants;
			
			$search_query = '';
			
			if($objUser->ID!=136343 && $objUser->ID!=65263 && $objUser->ID!=64380){
				//$search_query = 'AND a.value LIKE %s:'.$len.':"'.$objUser->user_email.'"%';
			}

            $query = <<<EOF

            SELECT a.* 

                FROM {$wpdb->prefix}rg_lead_detail a  

                WHERE a.field_number=$fp ' 

                ORDER BY a.lead_id ASC;

EOF;

            $leads = $wpdb->get_results($query);
			
           /* $sleads = '';

            foreach ($leads as $row) {

                $sleads .= $row->lead_id.',';
            }

            if($sleads) {

                $sleads = substr($sleads, 0,-1);

                $where .= 'AND l.lead_id IN ('.$sleads.') ';

            } else {

                return array();

            }
			*/
            
        //
        } elseif($user ||  $therapist_id>0 ) {
            
            if($assigned == false){
                if(is_admin()? ($user!=13634300 && $user!=65263 && $user!=64380):1){
                    $where .= 'AND l.created_by='.$user.' ';
                }
            }else{
                //dev:lamprea:11-mar-2013: Added validation when a therapist is invited
                $user = get_user_by('id', get_current_user_id());
                //$therapist_id>0
                
                if( !empty($user->user_email) && $user->user_email!='' && get_current_user_id() != 13634300 && get_current_user_id() != 65263 && get_current_user_id() != 64380 ) {

                    $where .= "AND users_invited LIKE '%i:".get_current_user_id()."%'";

                }
            }

        }

        $tblL = self::$tblLocation;

        $tblDL = self::$tblDateLocation;

        

        if($filter_date) {

            if(strpos($filter_date,'-') > 0) {

                list($y,$m) = explode('-', $filter_date);

                $where .= 'AND d.year='.((int)$y).' AND d.month='.((int)$m);

            } else {

                $where .= 'AND d.year='.((int)$filter_date);

            }

        }

        // if get all massage event locations in the database filter just by date :(

        // TODO: add a filter by current logged in user      

        $sql = <<<SQL

        SELECT d.*,l.data,l.lead_id,l.data,l.created_by,l.users_invited,l.accept_job,l.primary_location,

               c.user_id,c.hour_start,c.hour_end 

            FROM {$wpdb->prefix}$tblL l 

            RIGHT JOIN {$wpdb->prefix}$tblDL d ON d.location_id=l.ID 

            LEFT JOIN {$wpdb->prefix}confirm_events_employee c 

                ON l.lead_id=c.lead_id AND c.location_date_id=d.ID 

        WHERE 1=1 $where ORDER BY d.year, d.month, d.day, d.stime, d.etime;

SQL;

		if(isset($_GET["passs"])){
			echo $sql;
		}

        $events = $wpdb->get_results($sql);
	
        if( empty($events) ) {

            return array();

        }        

        $all = $reservs = array();

        $eL = null;

        foreach ($events as $e) {

            

            $eL = is_null($eL) ? $e : $eL;



            $reserved = array('user_id' => $e->user_id, 'start' => $e->hour_start, 'end' => $e->hour_end);

            $eL->myrange =  ($e->user_id == $objUser->ID) ? $reserved : ( isset($eL->myrange) ? $eL->myrange : array() );

            

            if($e->ID != $eL->ID) {

                unset($eL->hour_start);

                unset($eL->hour_end);

                $eL->reserved = $reservs;

                if($onlyLocationTypeCorporate) {

                    $lead = RGFormsModel::get_lead($eL->lead_id);

                    if($lead[Admin_Jobs::$field_id_corporate_massage]) {

                        $all[] = $eL;

                    }

                } else {

                    $all[] = $eL;

                }

                $eL = $e;

                $reservs = array();

            }

            if($e->user_id) {

                $reservs[] = $reserved;

            }

        }

        $eL->myrange =  ($eL->user_id == $objUser->ID) ? $reserved : ( $eL->myrange ? $eL->myrange : array() );

        



        unset($eL->hour_start);

        unset($eL->hour_end);

        $eL->reserved = $reservs;

        if($onlyLocationTypeCorporate) {

            $lead = RGFormsModel::get_lead($eL->lead_id);

            if($lead[Admin_Jobs::$field_id_corporate_massage]) {

                $all[] = $eL;

            }

        } else {

            $all[] = $eL;

        }



        return $all;

    }



    public static function getEventDateLocation($location_date_id) {

        if( empty($location_date_id) ) {

            return null;

        }

        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare('SELECT lde.* FROM '.$wpdb->prefix.self::$tblDateLocation.' lde WHERE lde.ID=%d;',$location_date_id));

        return $row;

    }

}



$GLOBALS['pgl_manage_events_jobs'] = new Admin_Jobs();

function emailtesting(){
$therapistemail = array('gpsbaroli@gmail','info@systork.com');

$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
$headers .= "From: Ahh Thats The Spot Massage <".get_bloginfo('admin_email').">" . "\r\n";
	
	
$isSend = wp_mail($therapistemail, 'Test support call', 'This is the content for when check updated ', $headers);
}

//add_action('init', 'emailtesting');

/*cron for job reminder*/

add_filter( 'cron_schedules', 'kyc_corn_schedules');
function kyc_corn_schedules()
{
	return array(
		
		'in_per_thirty_minute' => array(
			'interval' => 1 * 30,
			'display' => 'In every thirty Mintues'
		),
		/*'three_hourly' => array(
			'interval' => 60 * 60 * 3,
			'display' => 'Once in Three hours'
		)*/
	);
}


if( !wp_next_scheduled('kyc_thirty_minute_event') )
{
	wp_schedule_event( time(), 'in_per_thirty_minute', 'kyc_thirty_minute_event' );
}


function job_reminder_thirty_minute_job_cron(){
	
	job_reminder_thirty_minute_job_cron_asday(14);
	job_reminder_thirty_minute_job_cron_asday(7);
	job_reminder_thirty_minute_job_cron_asday(3);
	
}
//job_reminder_thirty_minute_job_cron_asday(14);

///add_action('wp_mail_failed', 'onMailError', 10, 1);
function onMailError($wp_error)
{
    if (!empty($wp_error)) {
        echo "<pre>";
        print_r($wp_error);
        echo "</pre>";
    }
}

if(isset($_GET["sss"])){
job_reminder_thirty_minute_job_cron_asday(7);	
	
}


function job_reminder_thirty_minute_job_cron_asday($day = 14){
	include_once(ABSPATH . 'wp-includes/pluggable.php');  
	global $wpdb;
	
	//$updatefield = 'remind_before_14_d';
	//email for 14
	$tblDateLocation = 'location_date_event';
	if(isset($_GET["sss"])){
		$day = 1;
	}
	$date = strtotime("+".$day." day");
	$getnextfuture = date('Y-m-d', $date);
	$getnextfutureleson1 = date('Y-m-d', $date-1);
	$list = $wpdb->get_results ( "SELECT *, CONCAT(year,'-',LPAD(month, 2, 0),'-',LPAD(day, 2, 0)) as timeframe FROM `".$wpdb->prefix.$tblDateLocation."` WHERE CONCAT(year,'-',LPAD(month, 2, 0),'-',LPAD(day, 2, 0))>='".$getnextfutureleson1."' and  CONCAT(year,'-',LPAD(month, 2, 0),'-',LPAD(day, 2, 0))<='".$getnextfuture."' and remind_before_".$day."_d IS NULL  " );
	
	//$jobdata = maybe_unserialize($locations[0]->data);
	
	//print_r($list);
	//$list = $wpdb->get_results( $wpdb->prepare('SELECT lde.* FROM '.$wpdb->prefix.$tblDateLocation.' lde WHERE lde.location_id=%d ORDER BY lde.year, lde.month, lde.day, lde.stime, lde.etime;',$location_id));
	
	$headers  = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
	$headers .= "From: Ahh Thats The Spot Massage <".get_bloginfo('admin_email').">" . "\r\n";
	
	$fname = "new_cron_schedular_log.txt";
	$fhandle = fopen($fname,"r");
	$content_log = fread($fhandle,filesize($fname));
	
	$content_log .= "\n start for the ".date("Y-m-d H:i:s");
	if(!empty($list)){
		foreach($list as $cotact_data){
			if(!isset($_GET["sss"])){
			$wpdb->query("update ".$wpdb->prefix.$tblDateLocation." set remind_before_".$day."_d = 1 WHERE ID=".$cotact_data->ID);
			}
			$time_start = date('l jS \of F Y h:i:s A', strtotime($cotact_data->timeframe." ".$cotact_data->stime));
			$time_end = date('l jS \of F Y h:i:s A', strtotime($cotact_data->timeframe." ".$cotact_data->etime));
			$location_id = isset($cotact_data->location_id)?$cotact_data->location_id:0;
			
			if($location_id>0){
				
				$sql_loc = "SELECT * FROM ".$wpdb->prefix."job_location WHERE status != 'archive' and status != 'delete' and ID=".$location_id;

				$locations = $wpdb->get_results($sql_loc);
				if(!empty($locations)){
					
					$jobdata = maybe_unserialize($locations[0]->data);
					//$users_inviteds = maybe_unserialize($locations[0]->users_invited);
					
					$status_accept = maybe_unserialize($locations[0]->accept_job);
					
					//print_r($status_accept);
					if(!empty($status_accept)){
						foreach($status_accept as $id=>$data) {
						
						$user = get_user_by('id', $id);
						
						$user->full_name = $user->first_name ? $user->first_name.($user->last_name ? ' '.$user->last_name : '') : null;
						$email = $user->user_email;
						$name = empty($user->full_name) ? ( empty($user->display_name) ? $email : $user->display_name) : $user->full_name;

						//dev:lamprea: Added placeholders for userlogina dna userpass
						$userlogin = $user->user_login;
						$content_log .= "\n send mail to therapist -  ".$email;
						$userpass = get_user_meta($user->ID, 'generated_random', true);
						//".$data[8]." ".$data[9]." ".$data[51]." ".$data[10]."
						
						$content_for_therapist = "Dear ".$user->first_name.", we're sending you a friendly reminder for your event, ".((isset($data[52]) && $data[52]!='')?$data[52]:$data[38])." will occur on ".$time_start." to ".$time_end.", for event details click <a href='".site_url('/my-events/')."'>here</a>.";
						
						$subject = 'Event Reminder';
						
						$therapistemail = array($email,'gpsbaroli@gmail','info@systork.com');
						
						$isSend = wp_mail($therapistemail, $subject, $content_for_therapist, $headers);
						wp_mail('gpsbaroli@gmail', $subject, $content_for_therapist, $headers);
						$isSend = wp_mail('info@systork.com', $subject, $content_for_therapist, $headers);
						//Code for SMS
						$message_sms = "Dear Admin, we're sending you a friendly reminder that your event will occur in ".$time_start." to ".$time_end;
						
						$to = get_user_meta($user->ID, 'mobile_phone_number', true);
						if($to==''){
							$to = get_user_meta($user->ID, 'phonenumber', true);
						}
						
						if( !empty($to) && substr($to,0,1) != '+') {
							$to = '+1'.$to;
						}
						$to = '+919610730117';
						//echo $to;
						//echo $message_sms;die();
						Admin_Jobs::sendSMS($to,$message_sms);    
						
						
						}
					}
					
					////send email to event owner
					
					$eventowner_id	 = ($locations[0]->created_by);
					
					$eventowner = get_user_by('id', $eventowner_id);
					
					$eventowner->full_name = $eventowner->first_name ? $eventowner->first_name.($eventowner->last_name ? ' '.$eventowner->last_name : '') : null;
					$eventowner_email = $eventowner->user_email;
					$eventowner_name = empty($eventowner->full_name) ? ( empty($eventowner->display_name) ? $email : $eventowner->display_name) : $eventowner->full_name;
					
					//$customeremail = array('gsjpr7@gmail.com', 'info@systork.com');
					///$isSend = wp_mail($customeremail, 'email user', 'email for user'.$eventowner_email, $headers);
					
					if($eventowner_email!=''){
					$content_for_eventowner = "Dear ".$eventowner_name.", we're sending you a friendly reminder for your event, ".((isset($data[52]) && $data[52]!='')?$data[52]:$data[38])." will occur on ".$time_start." to ".$time_end.", for event details click <a href='".site_url('/event-listing/')."'>here</a>.";
						
						$subject = 'Event Reminder';
							$content_log .= "\n send mail to customer -  ".$eventowner_email;
							$customeremail = array($eventowner_email,'gsjpr7@gmail.com', 'info@systork.com');
							
							$isSend = wp_mail($customeremail, $subject, $content_for_eventowner, $headers);
							
							//Code for SMS
							$message_sms = "Dear ".$eventowner_name.", we're sending you a friendly reminder that your event will occur in ".$time_start." to ".$time_end;
							
							$to = get_user_meta($eventowner_id, 'mobile_phone_number', true);
							if($to==''){
								$to = get_user_meta($user->ID, 'phonenumber', true);
							}
							if( !empty($to) && substr($to,0,1) != '+') {
								$to = '+1'.$to;
							}
							$to = '+919610730117';
							//echo $message_sms;die();
							//Admin_Jobs::sendSMS($to,$message_sms);    
					}
					
					//Send email to site owner
					$blogadmin = get_bloginfo('admin_email');
					
					if($blogadmin!=''){
					$content_for_admin = "Dear Admin, we're sending you a friendly reminder that your event, ".((isset($data[52]) && $data[52]!='')?$data[52]:$data[38])." will occur on ".$time_start." to ".$time_end.", for event details click <a href='".site_url('/my-events/')."'>here</a>. ";
						
						
						$subject = 'Event Reminder';
							
							$content_log .= "\n send mail to admin -  ".$blogadmin;
							$isSend = wp_mail($blogadmin, $subject, $content_for_admin, $headers);
							
							//Code for SMS
							$message_sms = "Dear Admin, we're sending you a friendly reminder that your event will occur in ".$time_start." to ".$time_end;
							
							/*$to = get_user_meta($user->ID, 'mobile_phone_number', true);
							
							if( !empty($to) && substr($to,0,1) != '+') {
								$to = '+91'.$to;
							}*/
							$to = '+17862279815';
							//echo $message_sms;die();
							Admin_Jobs::sendSMS($to,$message_sms);    
					}
				}
		/*//end location loop */
			}
			
		}
		
	}
	
	
	$content_log .= "\n cron_run at ".date("Y-m-d H:i:s");

	$fhandle = fopen($fname,"w");
	fwrite($fhandle,$content_log);
	fclose($fhandle);

    // your code here
}

//kyc_thirty_minute_job_cron();

add_action( 'kyc_thirty_minute_event', 'job_reminder_thirty_minute_job_cron' );

//job_reminder_thirty_minute_job_cron();

if(isset($_GET["passs"])){


$to = '+17862279815';
$to = '+17867075642';
$to = '+17542412747';
$to = '+18654091381';
$to = '+17862279815';
$to = '+919610730117';
$sms = 'this is test message';
$from = '+13312003051';
//$from = '+18654091381';

$result = ThatsSpotMassage_Twilio::send_sms($from, $to, $sms);
print_r($result); echo 'kkkkk';
//print_r(Admin_Jobs::sendSMS($to,$message_sms)); 

}


function login_redirect( $redirect_to, $request, $user ){
   
    $user_roles = $user->roles;
	//print_r($user_roles);die();
	
    // Check if the role you're interested in, is present in the array.
    if ( in_array( 'van_user', $user_roles, true ) ) {
        return home_url('health-fairs-plus-services-powered-by-thats-the-spot-massage');
    }elseif(in_array( 'administrator', $user_roles, true )){
        return home_url('wp-admin');
    }elseif(in_array( 'therapist', $user_roles, true )){
        return home_url('my-events');
    }else{
        return home_url('dashboard');
    }
    
}
add_filter( 'login_redirect', 'login_redirect', 10, 3 );