<?php
/**
 * 
 ****/
class CUsersResgistry {

	const LANG = 'admjobs';

	static $customerUsers = null;
	
	

	function __construct() {

		add_action('admin_enqueue_scripts', array($this, 'load_script'));
        //add_action('wp_ajax_'.$actionGetUsers, array($this, 'requestAllUsers'));
        //add_action('wp_ajax_nopriv_'.$actionGetUsers, array($this, 'requestAllUsers'));
		/** end load plugin search **/


		add_action('admin_menu', array($this, 'register_menu_page'));
	}
	
	function load_script(){
	
		$actionGetUsers = 'getallusers';
		/**
		 * Load Plugin search
		 *****/
		$vars_script = array(
			'allUsers' => array(),
			'urlGetUsers' => admin_url('admin-ajax.php'),
			'requestParams' => array('action' => $actionGetUsers),
			'radius' => get_option('mjobs_radio_search', 2),
			'messageResult' => __('It has established the following address as center location: "{address}", with a radius of {radius} mi.',self::LANG),
			'btnTriggerActionSearch' => 'search-users',
			'eLoading' => 'loading-search-users'
			/*
            'geocoder' => null,
            'eMessageResult' => 'msg-result',
            'inputKeyword' => 'search_keyword',
            'inputCity' => 'search_city',
            'inputState' => 'search_state',
            'usersFound' => array()*/
        );
        wp_register_script('cusers-resgistry-js', plugins_url('/customer_registry.js', __FILE__), array('jquery'), false, true);

		

        if(is_admin() && $_GET['page'] == 'customer_registry') {

	        wp_enqueue_script('cusers-resgistry-js');
	        ApiSearch::load();
	        wp_localize_script( 'apijs-search', '_configUSPgl', $vars_script );

        }		
	}
	

	public function requestAllUsers() {
		$users = array();

		$users = self::getCustomerUsers();

		echo json_encode($users);
		die();
	}

	public static function getCustomerUsers() {
        $users = get_users( array( 'role' => 'subscriber' ) );

        $users = array_merge($users, get_users( array( 'role' => 'client_employee' ) ) );

        $all = array();

        if( !empty(self::$customerUsers) ) {
        	return self::$customerUsers;
        }
        
        foreach($users as $u) {
            $data_latlng = get_user_meta($u->ID, 'address_latlng', true);
            
            $new_data = new stdClass;

            if( !empty($data_latlng) ) {
                $new_data->latidude = $data_latlng['latitude'];
                $new_data->longitude = $data_latlng['longitude'];
            }
                
            $new_data->ID = $u->ID;
            $new_data->avatar_img = null;
            if(function_exists('get_simple_local_avatar')) {
                $new_data->avatar_img = get_simple_local_avatar($u->ID,32);
            }
            $new_data->user_login = $u->user_login;
            $new_data->display_name = $u->display_name;
            $new_data->user_email = $u->user_email;
            $new_data->full_name = $u->first_name ? $u->first_name.($u->last_name ? ' '.$u->last_name : '') : null;
            $new_data->user_position = get_user_meta($u->ID, 'user_position', true);
            $new_data->user_department = get_user_meta($u->ID, 'user_department', true);
            $new_data->user_address = get_user_meta($u->ID, 'user_address', true);

            $all[] = $new_data;
        }
        self::$customerUsers = $all;
        return $all;
	}

	public function register_menu_page() {

		add_menu_page(__('Customer Users Registry', self::LANG),__('Customer Users Registry', self::LANG),'activate_plugins','customer_registry', array($this, 'render_customer_registry'));
	}

	public function render_customer_registry() {
		$required = '*';
		Admin_Jobs::the_modal_settings(true);
		wp_enqueue_style('admin-jobs-style');
		echo include_once('tmpl.php');
	}
}
new CUsersResgistry();

?>