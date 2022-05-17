<?php
/**
 * Template to Page for Admin Therapist
 **/
global $smglobal_vars;// var created in theme's functions.php
if(!$is_request_ajax):
?>
<div id="panel-jobs-therapist" class="wrap list-locations">
    <div class="panel-content">
<?php endif;          
        if(count($locations) > 0) { 
            foreach($locations as $location) :
                $info = maybe_unserialize($location->data);
                $eventDate = strtotime( $location->year.'-'.$location->month.'-'.$location->day.' '.$location->stime );

                $txtTime = date('g:i a', $eventDate).' - '.date('g:i a', strtotime($location->etime));
				//$accepted_data = maybe_unserialize($location->accepted);
				//print_r($location->accepted);
				//print_r($accepted_data);
				//print_r($location);
            ?>
            <div id="location_<?php echo $location->ID; ?>" class="colwrapper panel-therapist.php">
                    <div class="fields">
                        <div class="field-main <?php if($location->accepted){ echo 'job-accepted'; }?>">
                            <div class="eventDate">
                                <div class="title"><?php echo $attrs['name']; ?>:</div>
                                <div class="content"><?php echo date('F d, Y', $eventDate).' ('.$txtTime.')'; ?></div>
                            </div>
                            <?php if($location->accepted): ?>
                            <div class="event-info-button banner-accepted alert alert-success sm-tooltip" data-placement="left" data-original-title="<?php printf($smglobal_vars['textCancelJob'], $attrs['name']); ?>">
                                <i class="icon-info-sign"></i>
                                <span class="text"><?php printf($smglobal_vars['textAccepted'], $attrs['name']); ?></span>
                            </div>
                            <?php else: ?>
                            <div class="event-info-button actions" id="actions_<?php echo $location->ID; ?>">
                                <!--<p><a id="location_<?php echo $location->ID; ?>_more_details" href="<?php echo admin_url('/admin-ajax.php?action=modal_view_lead&l='.$location->lead_id.'&j='.$location->ID.'&n='.$attrs['name']); ?>" rel="lightbox[l<?php echo $location->ID; ?>|]" class="btn btn-small btn-cloading not-link cbox-view" title=""><?php _e('More details', $lang); ?></a></p>-->
                                <button class="button-primary btn btn-success btn-small" onclick="responseJob('yes',<?php echo $location->location_id.','.$location->ID; ?>);" data-loading-text="<?php _e('Sending', $lang); ?>"><?php printf($smglobal_vars['textAccept'], $attrs['name']); ?></button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php 
							foreach($labels as $key=>$lbel){
							
							if(in_array($key, array_merge($exclude_fields,array(44)))) 
							continue; 
							
													
							?>
                        <div class="sm_col">
                            <div class="title <?php echo $key;?>"><?php echo $lbel; ?></div>
                            <div class="content">
							<?php 
							if(in_array(trim($key),array(34,44)) ){
								if($location->accepted && $eventDate <= strtotime('+4 days')){
									
								echo $key == Admin_Jobs::$field_id_dates_and_events ? $txtTime : $info[$key]; 
								}
							}else{
								
								echo $key == Admin_Jobs::$field_id_dates_and_events ? $txtTime : $info[$key]; 
							}
							?></div>
                        </div>
                        <?php }	?>
                    </div>
                    
                    <div class="clr"></div>
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
            echo '<h3>'.sprintf(__('%s not found', $lang), $attrs['plural_name']).'</h3>';
        }
        ?>
<?php if(!$is_request_ajax): ?>
    </div>
</div>
<?php endif; ?>