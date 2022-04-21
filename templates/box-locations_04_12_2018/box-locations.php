<?php

/**
 * Template to Meta Box show to the Jobs available
 **/
?>
<div id="box_manage_jobs" class="stuffbox">
    <h3><label for="name"><?php _e('Locations', $lang); ?></label></h3>
    <form method="post">
        <div class="inside">
            <?php if(count($locations) > 0): ?>
                <table class="widefat">
                    <tbody>
                <?php foreach($locations as $key=>$location) :                    
                    $details = maybe_unserialize($location->data);
                    $count_users = $details[Admin_Jobs::$field_id_number_therapist];
                    $users_inviteds = null;
                    $ids_users_accept = null;
                    $users_therapists = array();
                    if( !empty($location->users_invited) ) {
                        $users_inviteds = maybe_unserialize($location->users_invited);
                        $status_accept = maybe_unserialize($location->accept_job);
                        
                        foreach($users_inviteds as $id=>$therapist) {

                            if( empty($status_accept[$id]) ) {
                                $users_therapists[] = sprintf(__('Pending for accept... (%s)', $lang), $therapist);
                            } else {
                                $txt = '<span>'.sprintf(__('%s accepted the following dates job:', $lang), $therapist).'</span><ul>';
                                
                                foreach($status_accept[$id] as $location_date_id) {
                                    $objLocDate = AJ_Location::getEventDateLocation($location_date_id);
                                    if($objLocDate) {
                                        $d = strtotime($objLocDate->year.'-'.$objLocDate->month.'-'.$objLocDate->day.' '.$objLocDate->stime);

                                        $txt .= '<li>'.date('F d, Y',$d).' ('.date('H:i a',$d).' - '.date('H:i a',strtotime($objLocDate->etime)).')</li>';
                                    }
                                }
                                $txt .= '</ul>';
                                $users_therapists[] = $txt;//$therapist;
                            }
                        }
                    }
                ?>
                        <tr>
                            <td class="entry-view-field-value">
                                <?php //echo "<pre>".print_r($details, true)."</pre>"; ?>
                                <table id="info_location_<?php echo $location->ID; ?>" class="gfield_list tab">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Location #', $lang); ?></th>
                                            <?php  foreach($labels_location as $label): ?>
                                            <th><?php echo $label; ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $location->ID; ?></td>
                                            <td><?php echo implode('</td><td>', $details); ?></td>
                                            <td><a href="javascript:void(0);" class="button-secondary" onclick="blViewDatesEvents(this,<?php echo $location->ID; ?>);"><?php _e('View dates', $lang); ?></a></td>
                                        </tr>                                        
                                        <tr>
                                            <td colspan="<?php echo count($details)+2; ?>">
                                                <h4><?php _e('Invited Therapists:', $lang); ?></h4>
                                                <ul class="list-therapists">
                                                <?php
                                                $centinela = true;
                                                if( !empty($users_therapists) ) {
                                                    
                                                    foreach($users_therapists as $text) { $class = $centinela ? 'line-a' : 'line-b'; $centinela = !$centinela; ?>
                                                        <li class="<?php echo $class; ?>"><?php echo $text; ?></li>
                                                    <?php }
                                                    
                                                } else {
                                                    echo '<li class="line-a"><b>'.__('You have not sent the invitations yet', $lang).'</b></li>';
                                                } ?>
                                                
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>                            
                        </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </form>
    <script type="text/javascript" src="<?php echo plugins_url('box_locations.js', __FILE__); ?>"></script>
</div>