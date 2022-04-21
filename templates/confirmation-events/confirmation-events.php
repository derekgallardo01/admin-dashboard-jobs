<?php
/**
 * Template to Page for Admin Therapist
 **/
global $smglobal_vars, $current_user;// var created in theme's functions.php
$required = '*';
if(!$is_request_ajax):
    $show_modal = empty($current_user->user_firstname) || empty($current_user->user_lastname);
?>
<div id="panel-confirmation-events" class="wrap list-locations<?php echo $show_modal ? ' show-update-profile' : ''; ?>">
    <?php if( $show_modal ) { ?>
    <div id="modal-update-profile">
        <div class="bg-modal"></div>
        <div id="form-update-profile">
            <h3 class="text-center"><?php _e('Update your data', $lang); ?></h3>
            <div class="form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="first_name"><?php _e('Firts name', $lang); ?>&nbsp;<span class="required"><?php echo $required; ?></span></label>
                    <div class="controls">
                        <input type="text" id="first_name" name="first_name" value="<?php echo $current_user->user_firstname; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="last_name"><?php _e('Last name', $lang) ?>&nbsp;<span class="required"><?php echo $required; ?></span></label>
                    <div class="controls">
                        <input type="text" id="last_name" name="last_name" value="<?php echo $current_user->user_lastname; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="user_department"><?php _e('Department', $lang) ?></label>
                    <div class="controls">
                        <input type="text" id="user_department" name="user_department" value="<?php echo get_user_meta($current_user->ID,'user_department', true); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="user_position"><?php _e('Position', $lang) ?></label>
                    <div class="controls">
                        <input type="text" id="user_position" name="user_position" value="<?php echo get_user_meta($current_user->ID,'user_position', true); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button id="update_profile" class="btn"><?php _e('Update', $lang); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="panel-content">
<?php endif;          
        if(count($confirmation_events) > 0) {
            $offset = get_option('gmt_offset');

            $current_time = $smglobal_vars['servertime']+60*60*$offset;//mktime(/*17,59,0,10,29,2013*/);
            $lead_id  = null;
            foreach($confirmation_events as $key=>$event) :
                $event_id = (int)$event->ID;

                $time = strtotime( $event->year.'-'.$event->month.'-'.$event->day.' '.$event->stime  );

                $enable_confirm = $current_time < $time;

                if($lead_id != $event->lead_id) {
                    $lead_id = $event->lead_id;
                    $fields = maybe_unserialize($event->data);
                }
                $txtTime = date('g:i a', $time).' - '.date('g:i a', strtotime($event->etime));
            ?>
            <div id="confirmation_event_<?php echo $event_id; ?>" class="colwrapper">
                    <div class="fields">
                        <div class="field-main field-main-slider">
                            <div class="eventDate">
                                <div class="title"><?php _e('Event Date', $lang); ?>:</div>
                                <div class="content"><?php echo date('F d, Y', $time); ?></div>
                            </div>
                        </div>
                        <?php foreach($labels as $k=>$lbel){ if(in_array($k, $exclude_fields)) continue;  ?>
                        <div class="sm_col <?php echo $k; ?>">
                            <div class="title"><?php echo $lbel; ?></div>
                            <div class="content"><?php echo $k == Admin_Jobs::$field_id_dates_and_events ? $txtTime : $fields[$k]; ?></div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="clr"></div>
                    <div class="box-slider">
                        
                        <?php if($enable_confirm) { ?>
                        <div class="message-select-range">
                            <span class="title-message-select-range"><?php _e('In order to select the time range to be attended, use the slider to drag and drop the Start and End time', $lang); ?></span>
                        </div>
                        <?php } ?>
                        <div class="single-range">
                            <div class="range-time">
                                <div class="lbl-range-time">
                                    <span class="lbl-time"><?php _e('Start time:', $lang); ?></span><span id="slistart_<?php echo $event_id; ?>" class="time"></span>
                                </div>
                                <div class="lbl-range-time">
                                    <span class="lbl-time"><?php _e('End time:', $lang); ?></span><span id="sliend_<?php echo $event_id; ?>" class="time"></span>
                                </div>
                                <?php if($enable_confirm) { ?>
                                <div class="lbl-range-time last">
                                    <button id="btn_confirm_<?php echo $event_id; ?>" class="btn block-center btn-confirm" onclick="confirm_event(<?php echo json_encode((int)$event->lead_id).','.json_encode($event_id); ?>);" data-loading-text="<?php _e('Confirmed...', $lang); ?>"><?php _e('Confirm event', $lang); ?></button>
                                </div>
                                <?php } ?>
                            </div>
                            <div id="slirange_<?php echo $event_id; ?>" class="slider-range"></div>
                            <?php if($enable_confirm) { ?>
                            <hr class="sep"/>
                            <div class="lbl-range-reserve">
                                <div class="progress progress-danger progress-striped">
                                  <div class="bar" style="width: 100%;"></div>
                                </div>
                                <span class="lbl-reserved mleft5"><?php _e('Red boxes mean time slots already reserved by other user', $lang); ?></span>
                            </div>
                            <?php } 
                            if( !empty($event->reserved) ) { ?>
                            <h6 class="title-reserves"><?php _e('Times reserved:', $lang); ?></h6>
                            <ul class="range-time list-reserves">
                                <?php foreach($event->reserved as $reserved) { ?>
                                <li class="lbl-range-time">
                                    <span class="lbl-time-reserved"><?php _e('Time:', $lang); ?></span><span class="mleft5"><?php echo date('g:i a', strtotime('2013-10-24 '.$reserved['start'])); ?></span> - <span><?php echo date('g:i a', strtotime('2013-10-24 '.$reserved['end'])); ?></span>
                                </li>
                                <?php } ?>
                            </ul>
                            <hr class="sep"/>
                            <?php } ?>
                        </div>
                    </div>
            </div>  
            <?php endforeach; 
            if(count($pages) > 0) { ?>
            <div class="pagination pagination-small">
                <span class="showing">
                    <span class="label"><?php printf(__('Showing %s of %s %s', $lang),$showing,$total_confirmation_events,$attrs['plural_name']); ?></span>
                </span>
                <span class="pages">
                    <span class="label"><?php _e('Pages:', $lang); ?></span>
                    <ul class="items-pages"><?php echo implode('', $pages); ?></ul>
                </span>
            </div>
            <?php }
        } else {
            echo '<h3>'.sprintf(__('%s not found', $lang), $attrs['plural_name']).'</h3>';
        }
        ?>
<?php if(!$is_request_ajax): ?>
    </div>
</div>
<?php endif; ?>