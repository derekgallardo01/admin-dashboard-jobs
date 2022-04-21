<div id="modal-settings" class="wrap modal-dialog" title="<?php _e('Settings', $lang); ?>">
    <img id="ajax_loading" class="ajax-loading-dialog" src="<?php echo plugins_url('/images/ajax-loader-black.gif', __FILE__); ?>" alt="" title="" style="display: none;" />
    <p class="message alternate" style="display: none;">
    <?php
        if(count($forms) > 0) {
            _e('Not established the form, please select a form to the next dropdown list:', $lang);
        } else {
            _e('No forms found, to create a new form, please click in the next button:', $lang);
        }
    ?>
    </p>
    <?php $isset = isset($hide_select_form); if(!$isset || ($isset && !$hide_select_form) ) { ?>
    <div class="block">
        <?php if(count($forms) > 0) { ?>
            <label><b><?php _e('Select a Form:', $lang); ?></b></label>
            <select id="list_forms" name="forms">
                <?php foreach($forms as $form): ?>
                <option value="<?php echo $form->id; ?>" <?php if($form_id == $form->id) { echo 'selected="selected"'; } ?>><?php echo $form->title; ?></option>
                <?php endforeach; ?>
            </select>
        <?php } else { ?>
            <div class="center"><a class="button-secondary" href="<?php echo admin_url('admin.php?page=gf_new_form'); ?>"><?php _e('Create New Form', $lang); ?></a>&nbsp;<b><?php _e('or', $lang); ?></b>&nbsp;<a class="button-secondary" href="<?php echo admin_url(); ?>"><?php _e('Go to Admin Home', $lang); ?></a></div>
        <?php }; ?>
    </div>
    <hr class="separator" />
    <?php } else { ?>
    <input id="list_forms" type="hidden" name="forms" value="" />
    <?php }?>
    <div class="block">
        <label><b><?php _e('Therapist search radius (mi.):', $lang); ?></b></label>
        <?php 
        $options_radio = apply_filters('sm_options_radio', array( 5, 10, 15, 25, 50, 75, 100, 200, 300 ));
        
        if( !empty($options_radio) ) { ?>
            <select id="search_radio" name="radio">
            <?php foreach($options_radio as $value) {
                ?>
                <option value="<?php echo $value; ?>" <?php if($value == $radio) { echo 'selected="selected"'; } ?>><?php echo $value; ?></option>
                <?php
            } ?></select>
            
            <?php
        }
        ?>
    </div>
</div>