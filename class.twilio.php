<?php


class ThatsSpotMassage_Twilio {
    
    public static function send_sms($from, $to, $body, $shorten_urls=false){

        $api = self::get_api();
        $messages = self::prepare_message($body, $shorten_urls);
        
        $response = null;
        for($i=0, $count = count($messages); $i<$count; $i++){
            $body = $messages[$i];

            //Add ... to all messages except last one
            if($i < $count-1)
                $body .=" ...";

            $to = preg_replace('|[^\d\+]|', "", $to);

            /*if(strpos($to, "1")===false || strpos($to, "1")>0) {
                $to = '+'.$to;
            }*/
            //$data = array("From" => $from, "To" => $to, "Body" => $body);
            //$response = $api->request("{$api->base_path}/SMS/Messages", "POST", $data);
            try{
                $sms = $api->account->sms_messages->create($from, $to, $body);
                if($sms->sid) {
                    return $sms;
                } else {
                    return null;
                }
                // Display a confirmation message on the screen
                //echo "Sent message {$sms->sid}";
            } catch (Services_Twilio_RestException $e) {
                return $e->getMessage();
            }
        }
        return !is_null($response) ? $response->ResponseXml->SMSMessage : null;
    }
    
    
    public static function get_api() {
        require_once(self::get_base_path() . "/apis/Services/Twilio.php");

        // Twilio REST API version
        $ApiVersion = "2010-04-01";

        // Set our AccountSid and AuthToken
        $settings = get_option("gf_twilio_settings");
        $retry_attemps = 3;

        // Instantiate a new Twilio Rest Client
        // $client = new TwilioRestClient($settings["account_sid"], $settings["auth_token"]);
        $client = new Services_Twilio($settings["account_sid"], $settings["auth_token"], null, null, $retry_attemps);
        //$client->base_path = "{$ApiVersion}/Accounts/{$settings["account_sid"]}";
        return $client;
    }
    
    public static function get_base_path() {
        return dirname(__FILE__);
    }

    public static function prepare_message($text, $shorten_urls=false) {
        if($shorten_urls){
            $text = preg_replace_callback('~(https?|ftp):\/\/\S+~', create_function('$matches','return GFTwilio::shorten_url($matches[0]);'), $text);
        }
        return str_split($text, 156);
    }
}

?>