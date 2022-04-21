<?php
/**
 * Template to Page for Public Events
 **/
global $smglobal_vars;
if(!$is_request_ajax):
?>
<div id="filter-spored">
    <label>
        <input id="only_spored" type="checkbox" name="only_spored" />
        <?php _e('Only corporate massage', $lang); ?>
    </label>
</div>
<div id="panel-events-user" class="wrap list-locations">
    <div class="panel-content">
<?php endif;          
        if(count($locations) > 0) {
            foreach($locations as $location) :
                $info = maybe_unserialize($location->data);
                
                $status_payment = gform_get_meta($location->lead_id, 'status_payment_job');
                $eventDate = strtotime( $location->year.'-'.$location->month.'-'.$location->day.' '.$location->stime );
            ?>
            <div id="location_<?php echo $location->ID; ?>" class="colwrapper public-events.php">
                    <div class="fields">
                        <div class="field-main <?php if(!$location->exists_vacantes){ echo 'job-accepted'; }?>">
                            <div class="eventDate">
                                <div class="title"><?php echo $attrs['name'].' '.$labels[$fmain_id]; ?>:</div>
                                <div class="content"><?php echo date('F d, Y', $eventDate); ?></div>
                            </div>
                            <!-- More Details button -->
                            <!-- payment info button -->
                            <!-- Event Confirmed button -->
                        </div>
                        <?php foreach($labels as $key=>$lbel) { if(in_array($key, $exclude_fields)) continue; ?>
                        <div class="sm_col <?php echo $key; ?>">
                            <div class="title"><?php echo $lbel; ?></div>
                            <div class="content"><?php echo $info[$key]; ?></div>
                        </div>
                        <?php } ?>
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
                                    $times = Admin_Jobs::getTimesReservedUser($u->ID,$lead['id']);

                                     
                                    if($times) {
                                        $hour_start = strtotime($times->hour_start)/*+60*60*$offset*/;
                                        $hour_end = strtotime($times->hour_end)/*+60*60*$offset*/;
                                        $minutes = ($hour_end-$hour_start)/60;
                                        $stimes = date('g:i a', $hour_start).' - '.date('g:i a', $hour_end).'<br/><b>'.sprintf(_n('(1 minute)', '(%s minutes)', $minutes, $lang), $minutes).'</b>';
                                    } else {
                                        $stimes = __('No selected time', $lang);
                                    }
                                    echo '<tr>';
                                        echo '<td>'.($u->first_name ? $u->first_name.' ('.$u->user_login.')' : $u->user_login).'</td>';
                                        echo '<td>'.$stimes.'</td>';
                                        echo '<td>'.(Admin_Jobs::checkIfPayService($lead['id'],$u->ID) ? __('Yes', $lang) : __('No', $lang)).'</td>';

                                    echo '</tr>';
                                }
                                echo '</table></div>';
                            }
                            
                        }
                    ?>
            </div>  
            <?php endforeach; 
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
            echo '<h3>'.sprintf(__('%s not found', $lang),$attrs['plural_name']).'</h3>';
        }
        ?>
<?php if(!$is_request_ajax): ?>
    </div>
</div>
<?php endif; ?>