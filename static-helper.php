<?php 

/**

 * Helper for some static methods on useful for the plugin

 **/

class SpotMassagesHelper {



	private static $entry_id;

    private static $current_user;





	static function secondaryFormHooks() {

        //param: SecondForm_FirstName

        add_filter('gform_field_value_SecondForm_FirstName', array('SpotMassagesHelper', 'populateSecondFormFirstName'));

        

        //param: SecondForm_FirstName

        add_filter('gform_field_value_SecondForm_LastName', array('SpotMassagesHelper', 'populateSecondFormLastName'));

        

        //param: SecondForm_Email

        add_filter('gform_field_value_SecondForm_Email', array('SpotMassagesHelper', 'populateSecondFormEmail'));



        //param: SecondForm_phone

        add_filter('gform_field_value_SecondForm_Phone', array('SpotMassagesHelper', 'populateSecondFormPhone'));



        //param: SecondForm_Position

        add_filter('gform_field_value_SecondForm_Position', array('SpotMassagesHelper', 'populateSecondFormPosition'));



        //param: SecondForm_Company

        add_filter('gform_field_value_SecondForm_Company', array('SpotMassagesHelper', 'populateSecondFormCompany'));



        //param: SecondForm_Company

        add_filter('gform_field_value_SecondForm_MainAddress', array('SpotMassagesHelper', 'populateSecondFormMainAddress'));





        //param: SecondForm_State

        add_filter('gform_field_value_SecondForm_State', array('SpotMassagesHelper', 'populateSecondFormState'));



        //param: SecondForm_City

        add_filter('gform_field_value_SecondForm_City', array('SpotMassagesHelper', 'populateSecondFormCity'));



        //param: SecondForm_TotalHours

        add_filter('gform_field_value_SecondForm_TotalHours', array('SpotMassagesHelper', 'populateSecondFormTotalHours'));



        //param: SecondForm_Participants

        //add_filter('gform_field_value_SecondForm_Participants', array('SpotMassagesHelper', 'populateSecondFormParticipants'));



        //param: SecondForm_FirstName

        add_filter('gform_field_value_SecondForm_ContactName', array('SpotMassagesHelper', 'populateSecondFormContactName'));

    }





    public static function loadFormField($entry_id, $field_number) {

    	global $wpdb;

    	

    	$query = 'SELECT value FROM '.$wpdb->prefix.'rg_lead_detail WHERE lead_id='.$entry_id

        		.' AND field_number='.$field_number;

        return $wpdb->get_var($query);

    }



	

    /** First Name Field - Secondary Form **/

    public static function populateSecondFormFirstName($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            self::$current_user = wp_get_current_user();



            $user_name = get_user_meta( self::$current_user->ID, 'first_name', true ); 

            return $user_name;

        } else {

            self::$entry_id = decrypt($_GET['quote']);

        

            //field_id in the database

            $first_name_field = 1;

            $result = SpotMassagesHelper::loadFormField( self::$entry_id, $first_name_field );

            $the_value = explode(" ", $result);

            

            return $the_value[0];

        }

    }



    /** Last Name Field - Secondary Form **/

    public static function populateSecondFormLastName($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            $field = get_user_meta( self::$current_user->ID, 'last_name', true ); 

            return $field;

        } else {

            //field_id in the database

            $first_name_field = 1;

            

            $result = SpotMassagesHelper::loadFormField( self::$entry_id, $first_name_field );

            $the_value = explode(" ", $result);

            if(count($the_value)>1)

                return $the_value[1];

            else

                return $value;

        }

    }



    /** Email Field - Secondary Form **/

    public static function populateSecondFormEmail($value) {



        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            return self::$current_user->user_email;

        } else {

            //field_id in the database

            $email_field = 3;

            

            $value = SpotMassagesHelper::loadFormField( self::$entry_id, $email_field );

            return $value;

        }

    }



    /** Phone Field - Secondary Form **/

    public static function populateSecondFormPhone($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            $field = get_user_meta( self::$current_user->ID, 'main_number', true ); 

            return $field;

        } else {

            //field_id in the database

            $phone_field = 2;

            

            $value = SpotMassagesHelper::loadFormField( self::$entry_id, $phone_field );

            return $value;

        }

    }





    /** Company Field - Secondary Form **/

    public static function populateSecondFormCompany($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            $field = get_user_meta( self::$current_user->ID, 'company_name', true ); 

            return $field;

        } else {

            //field_id in the database

            $field_number = 10;

            

            $value = SpotMassagesHelper::loadFormField( self::$entry_id, $field_number );

            return $value;

        }

    }



    /** Title/Position Field - Secondary Form **/

    public static function populateSecondFormPosition($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            $field = get_user_meta( self::$current_user->ID, 'user_position', true ); 

            return $field;

        } else {

            return $value;

        }        

    }



    /** Title/Position Field - Secondary Form **/

    public static function populateSecondFormMainAddress($value) {

        if( isset($_GET['quote']) &&  $_GET['quote']==='appointment') {

            $field = get_user_meta( self::$current_user->ID, 'user_address', true ); 

            return $field;

        } else {

            return $value;

        }        

    }





    /** State Field - Secondary Form **/

    public static function populateSecondFormState($value) {



        //field_id in the database

        $field_number = 12;

        

        $value = SpotMassagesHelper::loadFormField( self::$entry_id, $field_number );

        return $value;

    }





    /** City Field - Secondary Form **/

    public static function populateSecondFormCity($value) {



        //field_id in the database

        $field_number = 4;

        

        $value = SpotMassagesHelper::loadFormField( self::$entry_id, $field_number );

        return $value;

    }



    /** TotalHours Field - Secondary Form **/

    public static function populateSecondFormTotalHours($value) {



        //field_id in the database

        $field_number = 13;

        

        $value = SpotMassagesHelper::loadFormField( self::$entry_id, $field_number );

        return $value;

    }





    /** On Site Contact Name Field - Secondary Form **/

    public static function populateSecondFormContactName($value) {

        

        //field_id in the database

        $first_name_field = 1;

        

        $result = SpotMassagesHelper::loadFormField( self::$entry_id, $first_name_field );

        return $result;

    }

}