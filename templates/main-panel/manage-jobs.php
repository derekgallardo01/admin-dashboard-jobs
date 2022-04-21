<?php

/**
 * Template to Page for Admin Manage Jobs
 * /wp-admin/admin.php?page=admin_jobs
 **/
?>
<div id="manage-jobs" class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php _e('Manage Jobs Massages', $lang); ?></h2>
    <div id="pfilter">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <select id="filter_year" name="filter_year">
                <option value="all"><?php _e('- Select a Year -', $lang); ?></option>
                <?php $min_year = date('Y'); for($year = $min_year-2; $year <= ($min_year+1); $year++): ?>
                    <option value="<?php echo $year; ?>" <?php if($year == $select_year) { echo 'selected="selected"'; } ?>><?php echo $year; ?></option>
                <?php endfor; ?>
            </select>
            <select id="filter_month" name="filter_month" <?php if($select_year == 'all') { echo 'disabled'; }?>>
                <option value="all"><?php _e('- Select a Month -', $lang); ?></option>
                <?php for($month = 1; $month <= 12; $month++): $ktime = mktime(1,1,1, $month, 1,1); ?>
                    <option value="<?php echo date('m', $ktime); ?>" <?php if($month == $select_month) { echo 'selected="selected"'; } ?>><?php echo date('F', $ktime); ?></option>
                <?php endfor; ?>
            </select>
            <input type="submit" value="<?php _e('Filter', $lang); ?>" class="button-secondary" />
            <a class="button-secondary" href="<?php echo admin_url('admin.php?page=admin_jobs') ?>" title=""><?php _e('Reset', $lang); ?></a>
        </form>
        <p id="show-settings-form" class="search-box">
            <a id="link-show-modal-settings" class="button-primary" href="javascript:void(0);"><?php _e('Change Settings', $lang); ?></a>
        </p>
    </div>
    
    <div class="pgl-content-settings">
        <h3><?php echo $form->title.' (# '.$form->id.')'; ?></h3>
        <table class="widefat">
            <thead>
                <tr>
                    <?php foreach($columns as $field_id=>$field_info): ?>
                    <th onclick="Search('<?php echo $field_id ?>', 'ASC', <?php echo $form->id; ?>, '', '', '', '');"><?php echo esc_html($field_info["label"]) ?></th>
                    <?php endforeach; ?>
                    <th><?php _e('Date', $lang); ?></th>       
                    <th><?php _e('Vacantes?', $lang); ?></th>
                    <th><?php _e('Event Sponsored?', $lang); ?></th>
                    <th><?php _e('Action', $lang); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <?php foreach($columns as $field_id=>$field_info): ?>
                    <th onclick="Search('<?php echo $field_id ?>', 'ASC', <?php echo $form->id; ?>, '', '', '', '');"><?php echo esc_html($field_info["label"]) ?></th>
                    <?php endforeach; ?>  
                    <th><?php _e('Date', $lang); ?></th>   
                    <th><?php _e('Vacantes?', $lang); ?></th>
                    <th><?php _e('Event Sponsored?', $lang); ?></th>
                    <th><?php _e('Action', $lang); ?></th>
                </tr>
            </tfoot>
            <tbody>
                <?php $i=0; foreach($leads as $row): 
                    $countVacantes = $class->getCountVacantes($row);
                    
                    $vacante_label = $countVacantes > 0 ? sprintf(__('Yes ( %s )', $lang), $countVacantes) : __('No', $lang);
                    $str_date = strtotime($row['date_created']);
                    $date = date('F j, Y', $str_date);
                    $rowclass = ($i%2==0)?'class="alternate"':'';
                    $i++;
                ?>
                    <tr <?php echo $rowclass; ?>>
                        <?php foreach($columns as $field_id=>$field_info): ?>
                        <td><?php echo $row[$field_id]; ?></td>
                        <?php endforeach; ?>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $vacante_label; ?></td>
                        <td><?php echo empty($row[Admin_Jobs::$field_id_corporate_massage]) ? __('No', $lang) : __('Yes', $lang); ?></td>
                        <td><b><a href="<?php echo admin_url('admin.php?page=gf_entries&view=entry&id='.$form->id.'&lid='.$row['id'].'&filter=&paged=1&pos=0'); ?>"><?php _e('View details', $lang); ?></a></b></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>