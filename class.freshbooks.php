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

class ThatsSpotMassage_FreshBooks {


	public function __construct() {
		$this->init_api();
	}


	public function init_api() {
		
		if(!class_exists('FreshBooks_Client')) {
			require_once(PATH_GFFRESHBOOKS.'/api/Client.php');
	        require_once(PATH_GFFRESHBOOKS.'/api/Invoice.php');
	        require_once(PATH_GFFRESHBOOKS.'/api/Estimate.php');
	        require_once(PATH_GFFRESHBOOKS.'/api/Item.php');
	        require_once(PATH_GFFRESHBOOKS.'/api/Payment.php');
    	}

        $url = "https://" . get_option("gf_freshbooks_site_name") . ".freshbooks.com/api/2.1/xml-in";
        $authtoken = get_option("gf_freshbooks_auth_token");
        FreshBooks_HttpClient::init($url,$authtoken);
	}

	public function getClient($user_id) {

		$client_id = get_user_meta($user_id, 'freshbooks_client_id', true);

		if( !empty($client_id) ) {
			//new Client object
			$client = new FreshBooks_Client();

			//try to get client with client_id "$client_id"
			if( !$client->get($client_id) ) {
			    return null;
			} else {
				return $client;
			}
		}
		return null;
	}

	public function createClient($user_id) {
		if( empty($user_id) ) {
			return null;
		}
		$client = $this->getClient($user_id);
		if(is_null($client)) {
			//new Client object
			$client = new FreshBooks_Client();

			$user = get_user_by('id',$user_id);

			if( empty($user->ID) ) {
				return null;
			}

			//populate client’s properties
			$client->email = $user->user_email;
			$client->firstName = $user->first_name ? $user->first_name : ( empty($user->display_name) ? $email : $user->display_name );
			$client->lastName = $user->last_name ? ' '.$user->last_name : '';


			//try to create new client with provided data on FB server
			if(!$client->create()) {
			    file_put_contents(dirname(__FILE__)."/error-freshbooks.log", 'Error:'.date('Y-m-d H:i:s').': '.$client->lastError."\n",FILE_APPEND);
			    return null;
			} else {
				update_user_meta($user->ID, 'freshbooks_client_id', $client->clientId);
			    return $client;
			}
		}
		return $client;
	}

	public function prepareInvoiceToSend($user_id,&$invoice,$lines,$entry_id = null,$reply = true) {
		$client = $this->createClient($user_id);

		if(is_null($client) || empty($lines) || !is_array($lines)) {
			return false;
		}
		$lastInvoice = null;
		if(!is_null($entry_id) && $reply) {
			$invoiceId = get_user_meta($user_id, 'invoice:entry_'.$entry_id, true);
			$lastInvoice = $this->getInvoice($invoiceId);
			if(is_null($lastInvoice)) {
				delete_user_meta($user_id, 'invoice:entry_'.$entry_id);
			}
		}

		if(empty($invoice) || !is_a($invoice, 'FreshBooks_Invoice')) {
			$invoice = new FreshBooks_Invoice();
		}
		
		$invoice->date = date('Y-m-d');
		$invoice->lines = $lines;

		if(is_null($lastInvoice)) {
			$invoice->clientId = $client->clientId;
			
			if( !$invoice->create() ) {
				file_put_contents(dirname(__FILE__)."/error-freshbooks.log", 'Error:'.date('Y-m-d H:i:s').': '.$invoice->lastError."\n",FILE_APPEND);
				return false;
			}
			update_user_meta($user_id,'invoice:entry_'.$entry_id, $invoice->invoiceId);
			return true;
		} else {
			$invoice->invoiceId = $lastInvoice->invoiceId;
			$invoice->clientId = $lastInvoice->clientId;
			if( !$invoice->update() ) {
				file_put_contents(dirname(__FILE__)."/error-freshbooks.log", 'Error:'.date('Y-m-d H:i:s').': '.$invoice->lastError."\n",FILE_APPEND);
				return false;
			}
			$invoice->invoiceId = $lastInvoice->invoiceId;
		}
		return true;
	}

	public function getInvoiceByEntry($user_id,$entry_id) {
		if( empty($entry_id) || empty($user_id) ) {
			return null;
		}
		$invoiceId = get_user_meta($user_id, 'invoice:entry_'.$entry_id, true);
		$invoice = $this->getInvoice($invoiceId);
		if(is_null($invoice)) {
			delete_user_meta($user_id, 'invoice:entry_'.$entry_id);
		}
		return $invoice;
	}

	public function sendEmail($user_id,$items,$subject_email,$message_email,$entry_id = null,$reply = true) {

		$amount = 0;

		foreach ($items as $item) {
			$amount += $item['unitCost'];
		}

		$invoice = new ThatsSpotMassage_Invoice();
		$invoice->setSubject($subject_email);
		$invoice->setMessage($message_email);
		$invoice->amount = $amount;
		

		if( $this->prepareInvoiceToSend($user_id,$invoice,$items,$entry_id,$reply) ) { 
			//The create invoice process has been success


			/**
			 * Send the email via freshbooks
			 * Documentation:
			 * 		-> http://developers.freshbooks.com/docs/invoices/#invoice.sendByEmail
			 * 		-> http://community.freshbooks.com/support/settings-emails/
			 ***/
			if( !$invoice->sendByEmail() ) {
				file_put_contents(dirname(__FILE__)."/error-freshbooks.log", 'Error:'.date('Y-m-d H:i:s').': '.$invoice->lastError."\n",FILE_APPEND);
				return false;
			}
			return true;
		}
		return false;
	}

	public function getInvoice($invoiceId) {
		if( empty($invoiceId) ) {
			return null;
		}
		$invoice = new ThatsSpotMassage_Invoice();
		if( $invoice->get($invoiceId) ) {
			return $invoice;
		}
		return null;
	}
	public function deleteInvoice($invoiceId) {
		if( empty($invoiceId) ) {
			return false;
		}
		$invoice = new ThatsSpotMassage_Invoice();
		$invoice->invoiceId = $invoiceId;
		return $invoice->delete();
	}

	public function getAllInvoicesByUser($user_id) {
		if( empty($user_id) ) {
			return null;
		}

		$client = $this->getClient($user_id);

		if(is_null($client)) {
			return null;
		}

		$invoice = new ThatsSpotMassage_Invoice();
		$invoice->clientId = $client->clientId;
		
		$dummy = $rows = array();
		$result = $invoice->listing($rows,$dummy);

		if($result) {
			return $rows;
		}
		return null;
	}

	public function deleteAllInvoices($user_id) {
		
		$rows = $this->getAllInvoicesByUser($user_id);

		if(is_null($rows)) {
			return false;
		}

		foreach ($rows as $item) {
			$this->deleteInvoice($item->invoiceId);
		}
		return true;
	}

}


/**
 *
 * This class is created only to overwrite the function "_internalPrepareSendByEmail" 
 * and can add the parameters "subject" and "message" to the content.
 *
 ***/
class ThatsSpotMassage_Invoice extends FreshBooks_Invoice {

	var $subject = '';
	var $message = '';

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	protected function _internalPrepareSendByEmail(&$content) {
		$content = $this->_getTagXML("invoice_id",$this->invoiceId);
		$content .= $this->_getTagXML("subject",$this->subject);
		$content .= $this->_getTagXML("message",$this->message);
	}

	protected function _internalPrepareListing($filters,&$content)
	{
		if(is_array($filters) && count($filters)){
			$content 	.= parent::_internalPrepareListing($filters,$content)
								.  $this->_getTagXML("recurring_id",$filters['recurringId']);
		} else {
			$content .= $this->_getTagXML("client_id",$this->clientId);
		}
	}

}
?>