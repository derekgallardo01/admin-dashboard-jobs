<?php

/**
 * Template to Meta Box show to the Jobs available
 **/
?>
<div id="box_manage_jobs" class="stuffbox">
    <h3><label for="name"><?php _e('Locations <input type="checkbox" name="mastercheck" id="mastercheck"> Check All', $lang); ?>	<?php	if(isset($_GET["archive_location"]) && $_GET["archive_location"]=='yes'){		?>		&nbsp; <label for="name"><a href="admin.php?page=gf_entries&view=<?php echo $_GET["view"];?>&id=<?php echo $_GET["id"];?>&lid=<?php echo $_GET["lid"];?>&dir=<?php echo $_GET["dir"];?>&filter&paged=<?php echo $_GET["paged"];?>&pos=<?php echo $_GET["pos"];?>&field_id&operator&archive_location=no"><?php esc_html_e( 'View Locations', 'gravityforms' ); ?></a></label>	<?php }else{		?>		&nbsp; <label for="name"><a href="admin.php?page=gf_entries&view=<?php echo $_GET["view"];?>&id=<?php echo $_GET["id"];?>&lid=<?php echo $_GET["lid"];?>&dir=<?php echo $_GET["dir"];?>&filter&paged=<?php echo $_GET["paged"];?>&pos=<?php echo $_GET["pos"];?>&field_id&operator&archive_location=yes"><?php esc_html_e( 'View Archived Locations', 'gravityforms' ); ?></a></label>		<?php	}		?>		</label></h3>
    <form method="post">		<p>	<select name="archive_note" id="archive_note" >    <option value="">Select Option</option>    <!--<option value="activate">Activate</option>-->    <option value="archive">Archive</option>    <option value="delete">Delete</option>	    </select>	<input type="submit" name="submit_status" id="submit_status" />	</p>		
        <div class="inside">
            <?php if(count($locations) > 0): ?>
                <table class="widefat">
                <tbody>
                <?php 
				
				foreach($locations as $key=>$location) :                    
                    $details = maybe_unserialize($location->data);
                    $count_users = $details[Admin_Jobs::$field_id_number_therapist];
                    $users_inviteds = null;
                    $ids_users_accept = null;
                    $users_therapists = array();
                    if( !empty($location->users_invited) ) {
                        $users_inviteds = maybe_unserialize($location->users_invited);
                        $status_accept = maybe_unserialize($location->accept_job);
                        //print_r($users_inviteds); die();
                        foreach($users_inviteds as $id=>$therapist) {

                            if( empty($status_accept[$id]) ) {
                                $users_therapists[] = sprintf(__('Pending for accept... (%s)'.'<span> &nbsp; &nbsp; <a  target="_blank" href="user-edit.php?user_id='.$id.'" >Edit User</a></span> &nbsp; &nbsp; <a  onclick = "if (! confirm(\'Do you want to delete record\')) { return false; }" href="admin.php?page=gf_entries&delete_invited=yes&invited_id='.$id.'&location_id='.$location->ID.'&view=entry&id=2&lid='.$_GET["lid"].'&filter&paged=1&pos=0" >Delete Invited</a></span>', $lang), $therapist);
                            } else {
                                $txt = '<span>'.sprintf(__('%s accepted the following dates job:', $lang), $therapist).'</span>&nbsp; &nbsp; <span><a target="_blank" href="user-edit.php?user_id='.$id.'" >Edit User</a></span>  &nbsp; &nbsp; <a  onclick = "if (! confirm(\'Do you want to delete record\')) { return false; }" href="admin.php?page=gf_entries&delete_invited=yes&invited_id='.$id.'&location_id='.$location->ID.'&view=entry&id=2&lid='.$_GET["lid"].'&filter&paged=1&pos=0" >Delete Invited</a></span> <ul>';
                                
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
                                <table id="info_location_<?php echo $location->ID; ?>" class="gfield_list_new tab">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Location # <input type="checkbox"  class="checkBoxClass"  name="locationnumber['.$location->ID.']" id="locationnumber'.$key.'"  value="1" />', $lang); ?></th>
                                            <?php  foreach($labels_location as $label): ?>
                                            <th><?php echo $label; ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="location_tr<?php echo $location->ID; ?>">
                                            <td><?php echo $location->ID; ?></td>
                                            <td><?php echo implode('</td><td>', $details); ?></td>
                                            <td><div><a href="javascript:void(0);" class="button-secondary" onclick="blViewDatesEvents(this,<?php echo $location->ID; ?>);"><?php _e('View dates', $lang); ?></a>											</div>											<p></p>											<div>											<a class="button-secondary" target="_blank" href="admin.php?page=view_notes&lid=<?php echo $location->ID; ?>"><?php _e('View Notes', $lang); ?></a>											</div> 											<p></p>											<div>											<a class="button-secondary" href="javascript:void(0);" onclick="update_location('<?php echo $location->ID; ?>')"><?php _e('Edit Location', $lang); ?></a>											</div>																																												</td>
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
                </table>								<div id="addmorelocation"></div>				<div id="button">								<a href="javascript:void(0)" onclick="addmorelocation()" class="button button-large button-primary">Add More Location</a>								</div>
            <?php endif; ?>
        </div>
    </form>
    <script type="text/javascript" src="<?php echo plugins_url('box_locations.js', __FILE__); ?>"></script>
</div>