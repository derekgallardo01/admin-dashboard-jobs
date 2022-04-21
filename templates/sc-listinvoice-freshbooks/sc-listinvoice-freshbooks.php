<?php 
if(!defined('PATH_GFFRESHBOOKS')){
define('PATH_GFFRESHBOOKS', WP_PLUGIN_DIR.'/gravityformsfreshbooks');
}
if(!class_exists('FreshBooks_Invoice')) {
	require_once(PATH_GFFRESHBOOKS.'/api/Client.php');
    require_once(PATH_GFFRESHBOOKS.'/api/Invoice.php');
    require_once(PATH_GFFRESHBOOKS.'/api/Estimate.php');
    require_once(PATH_GFFRESHBOOKS.'/api/Item.php');
    require_once(PATH_GFFRESHBOOKS.'/api/Payment.php');
}

if(!defined('SC_SYMBOL_PRICE')) {
    define('SC_SYMBOL_PRICE', '$');
}

class ThatsSpotMassage_SCListInvoiceFreshBooks {

	const LANG = 'admjobs';

	var $pag_count_before = 2;
	var $pag_count_after = 2;

	static $listInvoices = null;

	public function __construct() {
		add_shortcode('freshbooks-invoices', array($this, 'renderInvoices'));

		$deps = array('jquery', 'spotmassage-base-js');

		$pagination_ajax_action = 'pagination_invoices';
        
        add_action('wp_ajax_'.$pagination_ajax_action, array($this, 'renderInvoicesAjax'));
        add_action('wp_ajax_nopriv_'.$pagination_ajax_action, array($this, 'renderInvoicesAjax'));

        if(!class_exists('ThatsSpotMassage_FreshBooks')) {
        	/**
	         * Add Freshbooks Class
	         * */
	        require_once(ADMJOBS_DIR.'/class.freshbooks.php');
        }
	}

	public function renderInvoices($attrs) {
		$default = array(
			'enabled_pagination_ajax' => 'false',
			'number_per_page' => 5
		);
		$atbts = wp_parse_args($attrs,$default);

		global $post, $current_user;
        $atbts['post_id'] = $post->ID;

		return $this->renderInvoicesAjax($atbts,true);
	}

	public function renderInvoicesAjax($atbts = array(), $return = false) {


		$attrs = empty($_REQUEST['attrs']) ? $atbts : $_REQUEST['attrs'];
		
        if( empty(self::$listInvoices) ) {
            self::$listInvoices = $this->getInvoices( get_current_user_id() );
        }

        $total = count(self::$listInvoices);


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
        self::$listInvoices = array_slice(self::$listInvoices,$limit_start,$end);
        $count_listInvoices = count(self::$listInvoices);
        
        $exists_pages = $total > $number_per_page;
        $pages = array();

        if($exists_pages) {
            $total_pages = ($total%$number_per_page == 0) ? intval($total/$number_per_page): intval($total/$number_per_page) + 1;

            $stpage = $num_page - $this->pag_count_before;
            $stpage = ( $stpage > 0) ? $stpage : 1;
            
            $endpage = ($this->pag_count_after+1)+$num_page;
            $endpage = $endpage > $total_pages ? $total_pages+1 : $endpage;

            $limit_pages = $endpage - $stpage;
            
            $pages = array_fill( $stpage, $limit_pages, '');
            if($attrs['enabled_pagination_ajax'] == 'true') {
                foreach($pages as $page=>$value) $pages[$page] = '<li class="paged"><a href="javascript:void(0);" onclick="goPage('.$page.')">'.$page.'</a></li>';
                
                if($num_page > 1) $pages[0] = '<li class="arrow-left paged"><a href="javascript:void(0);" onclick="goPage('.($num_page-1).')"><i class="icon-chevron-left"></i></a></li>';
                
                if( ($num_page+1) <= $total_pages) $pages[] = '<li class="arrow-right paged"><a href="javascript:void(0);" onclick="goPage('.($num_page+1).')"><i class="icon-chevron-right"></i></a></li>';
            } else {
                $post_id = $atbts['post_id'];
                $permalink_structure = get_option('permalink_structure', '');
                if( empty($permalink_structure) ) {
                    $url = get_permalink($post_id).'&paged=%s';
                } else {
                    $url = get_permalink($post_id);
                    $url = substr($url,-1) == '/' ? $url.'page/%s/' : $url.'/page/%s/';
                }                
                foreach($pages as $page=>$value) $pages[$page] = '<li class="paged"><a href="'.sprintf($url,$page).'">'.$page.'</a></li>';
                
                if($num_page > 1) $pages[0] = '<li class="arrow-left paged"><a href="'.sprintf($url,($num_page-1)).'"><i class="icon-chevron-left"></i></a></li>';
                
                if( ($num_page+1) <= $total_pages) $pages[] = '<li class="arrow-right paged"><a href="'.sprintf($url,($num_page+1)).'"><i class="icon-chevron-right"></i></a></li>';
            }
            
            $pages[$num_page] = '<li class="paged active"><a><b>'.$num_page.'</b></a></li>';
            ksort($pages);
        }

		if($return) {
            return include_once('tmpl.php');
        }
        //echo include_once('tmpl.php');

        $paged = $temp_paged;
        die();
	}


	public function getInvoices($user_id) {
		$obj = new ThatsSpotMassage_FreshBooks();
		$list = $obj->getAllInvoicesByUser($user_id);
		return is_null($list) ? array() : $list;
	}
}
new ThatsSpotMassage_SCListInvoiceFreshBooks();
?>