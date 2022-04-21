<?php
/**
 * Template to Page for Admin Therapist
 **/
global $smglobal_vars, $current_user;// var created in theme's functions.php
$required = '*';

ob_start();
if($return) {
    
?>
<div class="list-invoices-freshbooks wrap list-locations">
    <div class="panel-content">
<?php }
        if(count(self::$listInvoices) > 0) {

            foreach(self::$listInvoices as $invoice) {
            ?>
            <div id="invoice_<?php echo $invoice->invoiceId; ?>" class="colwrapper">
                    <div class="box-wrapper">
                        <h3 class="main-title"><?php echo $invoice->firstName.( empty($invoice->lastName) ? '' : ' '.$invoice->lastName); ?></h3>
                        <div class="box-sup">
                            <div class="box-main <?php echo $invoice->status; ?>">
                                <div class="status"><b><?php _e('Status:', self::LANG); ?></b>&nbsp;<?php echo ucfirst($invoice->status); ?></div>
                                
                                <div class="details-status">
                                    <span class="total"><?php echo SC_SYMBOL_PRICE.$invoice->amountOutstanding; ?></span>
                                    <?php if($invoice->status !== 'paid') { ?>
                                    <a class="paynow" href="<?php echo $invoice->linkClientView; ?>" target="_blank"><?php ?><?php _e('Pay Now', self::LANG); ?></a>
                                    <?php } ?>
                                </div>
                                
                            </div>

                            <table class="table table-bordered tbl-right">
                                <tbody>
                                    <tr>
                                        <th><?php _e('Invoice #:', self::LANG); ?></th>
                                        <td><?php echo $invoice->number; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Date:', self::LANG); ?></th>
                                        <td><?php echo date('F d, Y', strtotime($invoice->date)); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Amount Due USD:', self::LANG); ?></th>
                                        <td><?php echo SC_SYMBOL_PRICE.$invoice->amountOutstanding; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="clr"></div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?php _e('Item', self::LANG); ?></th>
                                    <th><?php _e('Description', self::LANG); ?></th>
                                    <th><?php printf(__('Unit Cost (%s)', self::LANG), SC_SYMBOL_PRICE); ?></th>
                                    <th><?php _e('Quantity', self::LANG); ?></th>
                                    <th><?php printf(__('Price (%s)', self::LANG), SC_SYMBOL_PRICE); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($invoice->lines as $item) { ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td><?php echo $item['description']; ?></td>
                                        <td><?php echo $item['unitCost']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo $item['amount']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
            </div>  
            <?php }
            if(count($pages) > 0) { ?>
            <div class="pagination pagination-small">
                <span class="showing">
                    <span class="label"><?php printf(__('Showing %s of %s %s', self::LANG),$showing,$total_confirmation_events,$attrs['plural_name']); ?></span>
                </span>
                <span class="pages">
                    <span class="label"><?php _e('Pages:', $lang); ?></span>
                    <ul class="items-pages"><?php echo implode('', $pages); ?></ul>
                </span>
            </div>
            <?php }
        } else {
            echo '<h3>'.sprintf(__('%s not found', $lang), 'Invoices').'</h3>';
        }
        ?>
<?php if($return) { ?>
    </div>
</div>
<?php } 

return ob_get_clean();
?>