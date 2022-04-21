<?php $path_pgl = dirname(dirname(__FILE__)); ?>
<!-- <h4><?php _e('Invoice Creation', $lang); ?></h4> -->

<fieldset id="wrapper-payment-method" class="box-forms-send method-selc">
    <legend>1) <?php _e('Select the payment method:', $lang); ?></legend>
<div id="method-payment">
    <label title="<?php _e('Payment via paypal', $lang); ?>" for="paypal" class="wimg selected">
        <img src="<?php echo plugins_url('/images/paypal-logo.png', $path_pgl); ?>" />
        <input class="radio-payment" id="paypal" name="method_payment" type="radio" value="paypal" checked="checked" />
    </label>
    <label title="<?php _e('Payment via Freshbooks', $lang); ?>" for="freshbooks" class="wimg">
        <img src="<?php echo plugins_url('/images/opt-freshbooks.png', $path_pgl); ?>" />
        <input class="radio-payment" id="freshbooks" name="method_payment" type="radio" value="freshbooks" />
    </label>
</div>
</fieldset>

<fieldset id="invoice-generation-method" class="box-forms-send method-selc">
    <legend>2) <?php _e('Select the invoice generation type:', $lang); ?></legend>

    <?php if($lead[Admin_Jobs::$field_id_corporate_massage]) { ?>
    <label><input id="slc_method_send" type="checkbox" name="slc_method_send" />
        <?php _e('Calculate price based in amount of minutes and auto-generate pdf',$lang); ?></label>

    <table class="widefat entry-detail-view box-forms-send" id="send-auto">
        <tbody>
            <tr><td class="entry-view-section-break" colspan="2">
                <?php _e('Automatic Payment Link', $lang); ?>
            </td></tr>
            <tr>
                <td width="200px"><?php _e('Price per minute'); ?></td>
                <td><input id="price_minute" type="text" name="price_minute" />&nbsp;<span class="valg-top"><?php echo $required; ?></span></td>
            </tr>
        </tbody>
    </table>

    <?php } ?>


    <table class="widefat entry-detail-view box-forms-send method-selc" id="upload-invoices" >
        <tbody>
            <tr><td class="entry-view-section-break" colspan="2">
                <?php _e('Manual Invoice', $lang); ?>
            </td></tr>
            <?php 
            if($lead[Admin_Jobs::$field_id_corporate_massage]) {
                $offset = get_option('gmt_offset');
                global $wpdb;
                $tbl = $wpdb->prefix.'confirm_events_employee';
                $query = 'SELECT * FROM '.$tbl.' WHERE user_id=%s AND lead_id=%s;';
                $users = array();

                //foreach users ----------------------------------------
                foreach ($participants_massage as $email) {
                    $user = get_user_by( 'email', $email );

                    $users[$user->ID] = array();
                    
                    $alltimes = $wpdb->get_results($wpdb->prepare($query,$user->ID, $lead['id']));

                    ?>
                    <tr><td colspan="2"><b><?php _e('Client employee:', $lang)?></b>&nbsp;<?php echo $user->data->user_email; ?></td></tr>
                    <?php 

             
                    if(!empty($alltimes)) { 
                        
                        foreach ($alltimes as $times) {//foreach times selecteds ----------------------
                            $minutes = 0;
                            if( !empty($times) ) {
                                $hour_start = strtotime($times->hour_start)/*+60*60*$offset*/;
                                $hour_end = strtotime($times->hour_end)/*+60*60*$offset*/;
                                $minutes = ($hour_end-$hour_start)/60;
                                $description = date('g:i a', $hour_start).' - '.date('g:i a', $hour_end).' '.sprintf(_n('(1 minute)', '(%s minutes)', $minutes, $lang), $minutes);
                                ?>
                                <tr>
                                    <td colspan="2"><b><?php _e('Time Selected:',$lang); ?></b>&nbsp;<?php echo $description; ?></td>
                                </tr>
                            <?php }?>
                            <tr>
                                <td><b><?php _e('Price to be paid $', $lang); ?></b></td>
                                <td><input id="price_invoice_<?php echo $user->ID.'_'.$times->location_date_id; ?>" type="text" size="20" name="price_invoice[]
                                    " value="" /><span><?php _e('USD', $lang); ?></span>&nbsp;<span class="valg-top"><?php echo $required; ?></span></td>
                            </tr>
                        <?php 

                            $users[$user->ID][] = array('description' => __('A massage service event.', $lang)."<br/>".__('Times:',$lang).' '.$description, 'minutes' => $minutes, 'location_date_id' => $times->location_date_id);

                        } //end foreach times selecteds ----------------------?>
                        <tr>
                            <td><button class="button upload_file_button" user="<?php echo $user->ID; ?>"><?php _e('Select File', $lang); ?></button></td>
                            <td><input id="path_file_<?php echo $user->ID; ?>" type="text" size="20" name="path_file_<?php echo $user->ID; ?>" value="" disabled="true" />&nbsp;<span class="valg-top"><?php echo $required; ?></span></td>
                        </tr>
                        <tr><td colspan="2"><hr/></td></tr>
                    <?php } else {
                        echo '<tr><td colspan="2"><b>'.__('The user has not confirmed a time slot for the massage',$lang).'</b></td></tr>';
                    }
                
                } // END foreach users --------------------------------------------

            } else { 

                $users = array();
                $users[$user_created->ID] = array();
                $users[$user_created->ID][] = array('description' => __('A massage service event.', $lang), 'minutes' => 0, 'location_date_id' => 0);

                ?>
                <tr><td colspan="2"><b><?php _e('Client:', $lang)?></b>&nbsp;<?php echo $user_created->user_email; ?></td></tr>
                <tr>

                    <td><b><?php _e('Price to be paid $', $lang); ?></b></td>
                    <td><input id="price_invoice_<?php echo $user_created->ID.'_0'; ?>" type="text" size="20" name="price_invoice" value="" /><span><?php _e('USD', $lang); ?></span>&nbsp;<span class="valg-top"><?php echo $required; ?></span></td>
                </tr>
                <tr>
                    <td><button class="button upload_file_button" user="<?php echo $user_created->ID; ?>"><?php _e('Select File', $lang); ?></button></td>
                    <td><input id="path_file_<?php echo $user_created->ID; ?>" type="text" size="20" name="path_file_<?php echo $user_created->ID; ?>" value="" disabled="true" />&nbsp;<span class="valg-top"><?php echo $required; ?></span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</fieldset>

<fieldset class="box-forms-send method-selc">
    <legend>3) <?php _e('Customize the email message', $lang); ?></legend>
    <table>
        <tbody>
            <tr>
                <td class="valg-top"><?php _e('Content Message Email', $lang); ?></td>
                <td>
                    <textarea id="body-message" name="body_message" cols="60" rows="10"><?php echo $body_message; ?></textarea>&nbsp;<span class="valg-top"><?php echo $required; ?></span>
                    <div><i><?php _e('Note: It is required the usage of the tag {link_invoice} in the content', $lang); ?></i></div>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>

<p><a id="send-invoice" class="button-primary" href="#"><?php _e('Send Invoice', $lang); ?></a></p>
<div id="reSta"></div>
<input type="hidden" value="<?php echo $lead['id']; ?>" id="entry_id" name="entry_id" />

<script type="text/javascript">
var usersInvoices = <?php echo json_encode($users); ?>;

jQuery(document).on('ready', function(){
    var change_method = function() {
        var check = jQuery(this).is(':checked');
        var btn = jQuery('#send-invoice');
        btn.off('click');

        if(check) {
            /*
            jQuery('#send-auto').slideDown('fast');
            jQuery('#upload-invoices').slideUp('fast');
            */
            jQuery('#send-auto').addClass('method-selc');
            jQuery('#upload-invoices').removeClass('method-selc');

            btn.on('click', send_invoice_auto);
        } else {
            /*
            jQuery('#send-auto').slideUp('fast');
            jQuery('#upload-invoices').slideDown('fast');
            */
            jQuery('#send-auto').removeClass('method-selc');
            jQuery('#upload-invoices').addClass('method-selc');

            btn.on('click', send_invoice);
        }
    };
    jQuery('#slc_method_send').on('change', change_method);
});
</script>