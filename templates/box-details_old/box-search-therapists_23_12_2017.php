<?php

/**
 * Template to Meta Box for Admin entrys Gravity Forms
 **/
?>
<div id="box_tabs_therapist" class="stuffbox">
    <h3><label for="name"><?php _e('Therapist List', $lang); ?></label></h3>
    <form method="post">
        <div class="inside">
            <div id="tabs-therapist-box">
                <ul>
                    <li><a href="#tab-1"><?php _e('Therapists Invitations', $lang); ?></a></li>
                    <li><a href="#tab-2"><?php _e('Billing', $lang); ?></a></li>
                </ul>
                <div id="tab-1">
                    <?php include_once('tpl-tab1.php'); ?>
                </div>
                <div id="tab-2">
                    <?php include_once('tpl-tab2.php'); ?>
                </div>
            </div>
            <div class="buttons-bottom">
                <p><a id="back-main-panel" class="button-primary" href="<?php echo admin_url('admin.php?page=admin_jobs'); ?>"><?php _e('Back to Jobs List', $lang); ?></a></p>
            </div>
        </div>
    </form>
</div>