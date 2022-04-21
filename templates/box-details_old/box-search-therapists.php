<?php

/**
 * Template to Meta Box for Admin entrys Gravity Forms
 **/$mylead = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."rg_lead_meta where meta_key = 'invoice_paid_detail' and lead_id = '".$_GET["lid"]."' " );$invoicepaid = (array)unserialize($mylead[0]->meta_value);//print_r($invoicepaid);die();?>
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
                <div id="tab-2">								<?php												include_once('tpl-tab2.php'); ?>							<?php			if(isset($invoicepaid) && $invoicepaid!=''){			?>						<h2> Paid Invoice Detail </h2>						<table class="gfield_list">				<tbody>				<tr>				<td>Name : </td>				<td> <?php echo $invoicepaid["ssl_first_name"];?> </td>				</tr>								<tr>				<td>Company : </td>				<td> <?php echo $invoicepaid["ssl_company"];?></td>				</tr>								<tr>				<td>Email : </td>				<td> <?php echo $invoicepaid["ssl_email"];?></td>				</tr>								<tr>				<td>Amount paid : </td>				<td> $<?php echo $invoicepaid["ssl_amount"];?></td>				</tr>				</tbody>				</table>				<?php				} ?>			
                </div>
            </div>
            <div class="buttons-bottom">
                <p><a id="back-main-panel" class="button-primary" href="<?php echo admin_url('admin.php?page=admin_jobs'); ?>"><?php _e('Back to Jobs List', $lang); ?></a></p>
            </div>						
        </div>
    </form>
</div>