<?php
/**
 * Template to Page for User (Role Client)
 * created to render the shortcode [events-jobs]
 **/
global $smglobal_vars;

if(!$is_request_ajax):
?>
<!-- <div id="filter-spored">
    <label>
		<input type="hidden" name="1" value="1" />
        <input id="only_spored" type="checkbox" name="only_spored" />
        <?php _e('Only corporate massage', $lang); ?>
    </label>
</div> -->
<!-- <p class="link-box">
    <a title="<?php _e('Schedule another appointment', $lang); ?>" 
        href="<?php echo $link_appointment; ?>" target="_blank"><?php _e('Schedule another appointment', $lang); ?></a>

    <br /><small class="border-top">Use this link to request a new massage event.</small>
</p> -->
<div id="panel-events-user" class="wrap list-locations">
<?php

if(isset($_GET["gg"]) || 1){
    
   /* error_reporting(E_ALL);
    ini_set('display_errors',1);*/

    $date = null;

    if( !empty($_REQUEST['search']) && is_array($_REQUEST['search']) && !empty($_REQUEST['search']['year']) ) {

        $date = $_REQUEST['search']['year'].'{month}';

        $date = str_replace('{month}',(!empty($_REQUEST['search']['month']) ? '-'.$_REQUEST['search']['month'] : ''),$date);

    }
    $adm_jobs = new Admin_Jobs();
    //get_available_locations_user
    
    $locations = $adm_jobs->get_available_locations_created(get_current_user_id(),$date);
    
    foreach($locations as $location_data){
        //echo '<pre>';
        //print_r($location_data);  die();
        $loc_data = unserialize($location_data->data);
        
        //print_r($location_data);

    ?>

        <tr>
            <td colspan="8">
            <!--<table border="0">
                <tr>
                <td><span class="nameheading"><b>Therapist Names</b></span></td>
                <td><span class="nameheading"><b>Email Id</b></span></td>
                <td><span class="nameheading"><b>Phone</b></span></td>
                <td><span class="nameheading"><b>Invitation Status</b></span></td>
                </tr>-->

                <?php
                $users_inviteds = maybe_unserialize($location_data->users_invited);
                $status_accept = maybe_unserialize($location_data->accept_job);
                //print_r($location_data);
                if(!empty($users_inviteds)){
                    foreach($users_inviteds as $id=>$therapist) {
                        
                        $user_info = get_userdata($id);
                        //$user_info = $user_info_ob->data;
                       // print_r($user_info);
                        $user_email = isset($user_info->user_email)?$user_info->user_email:'';
                        $first_name = get_user_meta($id, 'first_name', true);
                        $phone  = get_user_meta($id,'phone',true);
                        //print_r($phone);
                        //echo $therapist;
                        //echo '<tr><td><span class="simpletext">'.str_replace('View address','',$therapist).'</span></td><td><span class="simpletext">' . $user_email . '</span></td><td><span class="simpletext">' . $phone . '</span></td><td>';
                        
                        if( isset($status_accept[$id]) ) {
                            //str_replace('View address','',$therapist)
                            //echo 'Accepted Therapist : '.$first_name.' ('.$phone.')<br /> ';
                            //echo '<span class="selectedstat">Accepted</span>';
                        } else {
                            //echo '<span class="simplestat">Pending</span>';
                            
                        }
                        
                        //echo '</td></tr>';
                    }
                }
                                
                ?>

                <!--</table>-->	

                </td>
        </tr>

    <?php  } ?>
    </table>

    <?php 

}else{
    echo do_shortcode('[stickylist id="11" user="' . $usrid . '"]');
}


?>
    <div class="panel-content">
<?php endif;


        if(count($locations) > 0) { 
            foreach($locations as $location) {
                $info = maybe_unserialize($location->data);
                
                $status = gform_get_meta($location->lead_id, 'status_payment_job');

                $status_payment = empty($status) ? array() : get_object_vars( json_decode($status) );
                

                $eventDate = strtotime( $location->year.'-'.$location->month.'-'.$location->day.' '.$location->stime );
            
            ?>
            
            
            <div id="location_<?php echo $location->ID; ?>" class="colwrapper events-user.php">
                    <div class="fields">
                        <div class="field-main <?php if(!$location->exists_vacantes){ echo 'job-accepted'; }?>">
                            <div class="eventDate">
                                <div class="title"><?php echo $attrs['name'].' '.$labels[$fmain_id]; ?>:</div>
                                <div class="content"><?php echo date('F d, Y', $eventDate); ?></div>
                            </div>
                            <div class="event-info-button actions" id="actions_<?php echo $location->ID; ?>">
                                <a id="location_<?php echo $location->ID; ?>_more_details" href="<?php echo admin_url('/admin-ajax.php?action=modal_view_lead&l='.$location->lead_id.'&j='.$location->ID); ?>" rel="lightbox[l<?php echo $location->ID; ?>|]" class="btn btn-small btn-cloading not-link cbox-view" title=""><?php _e('More details', $lang); ?></a>
                            </div>
                            <?php if( /*!empty($status_payment)*/false ) {// verify if this job (entry) has been paid ?>
                            <div class="event-info-button banner-accepted alert alert-<?php echo strtolower('success'); ?> pay-status-<?php echo strtolower('success'); ?>">
                                <span class="text"><?php _e('Payment Success',$lang); ?></span>
                            </div>
                            <?php } ?>
                            <?php 
                            if(!$location->exists_vacantes) { ?>
                            <div class="event-info-button banner-accepted alert alert-success">
                                <i class="icon-ok-sign"></i>
                                <span class="text"><?php printf(__('%s Confirmed',$lang), $attrs['name']); ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <?php
                        foreach($labels as $key=>$lbel){ if(in_array($key, $exclude_fields)) continue;

                        $date_before_four_day = date('Y-m-d',  strtotime( $location->year.'-'.str_pad($location->month,2,0,STR_PAD_LEFT).'-'.str_pad($location->day,2,0,STR_PAD_LEFT) . ' -4 days' ) );
                        $today_date             = $location->year.'-'.str_pad($location->month,2,0,STR_PAD_LEFT).'-'.str_pad($location->day,2,0,STR_PAD_LEFT);
                        if($key==34? (date('Y-m-d')<=$today_date && date('Y-m-d')>=$date_before_four_day):1)
                            {
                            ?>
                            <div class="sm_col <?php echo $key; ?>">
                                <div class="title"><?php echo $lbel; ?></div>
                                <div class="content"><?php echo $key == Admin_Jobs::$field_id_dates_and_events ? date('g:i a', $eventDate).' - '.date('g:i a', strtotime($location->etime)) : $info[$key]; ?></div>
                            </div>
                            <?php 
                            }
                    } ?>
                    </div>
                    <div class="clr"></div>
                    <?php 
                        if($location->primary_location) {
                            $lead = RGFormsModel::get_lead($location->lead_id);
                            if($lead[Admin_Jobs::$field_id_corporate_massage]) {
                                echo '<div class="list-employees"><h3>'.__('Employees:',$lang).'</h3>';
                                echo '<table class="table table-bordered">';
                                echo '<thead><tr>';
                                    echo '<th>'.__('Email',$lang).'</th>';
                                    echo '<th>'.__('Times',$lang).'</th>';
                                    echo '<th>'.__('Paid?',$lang).'</th>';

                                echo '</tr></thead>';
                                $users_p = maybe_unserialize($lead[Admin_Jobs::$field_id_participants]);
                                
                                foreach ($users_p as $email) {
                                    $u = get_user_by('email', $email);
                                    $times = Admin_Jobs::getTimes($u->ID,$location->reserved);
                                    if($times) {
                                        $hour_start = strtotime($times['start'])/*+60*60*$offset*/;
                                        $hour_end = strtotime($times['end'])/*+60*60*$offset*/;
                                        $minutes = ($hour_end-$hour_start)/60;
                                        $stimes = date('g:i a', $hour_start).' - '.date('g:i a', $hour_end).'<br/><b>'.sprintf(_n('(1 minute)', '(%s minutes)', $minutes, $lang), $minutes).'</b>';
                                    } else {
                                        $stimes = __('No selected time', $lang);
                                    }
                                    $ifPay = Admin_Jobs::checkIfPayService($location->lead_id,$u->ID,$location->ID);
                                    echo '<tr>';
                                        echo '<td>'.($u->first_name ? $u->first_name.' ('.$u->user_login.')' : $u->user_login).'</td>';
                                        echo '<td>'.$stimes.'</td>';
                                        echo '<td>'.($ifPay ? __('Yes', $lang) : __('No', $lang)).'</td>';

                                    echo '</tr>';
                                }
                                echo '</table></div>';
                            }
                            
                        }
                    ?>
            </div>  
            <?php } 
            if(count($pages) > 0) { ?>
            <div class="pagination pagination-small">
                <span class="showing">
                    <span class="label"><?php printf(__('Showing %s of %s %s', $lang),$showing,$total_locations,$attrs['plural_name']); ?></span>
                </span>
                <span class="pages">
                    <span class="label"><?php _e('Pages:', $lang); ?></span>
                    <ul class="items-pages"><?php echo implode('', $pages); ?></ul>
                </span>
            </div>
            <?php }
        } else {
            echo '<h3>'.sprintf(__('No Current %s found.', $lang),$attrs['plural_name']).'</h3>';
        }
        ?>
<?php if(!$is_request_ajax): ?>
    </div>
</div>
<?php endif; 




?>

