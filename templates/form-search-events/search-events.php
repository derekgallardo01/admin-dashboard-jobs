<?php
/**
 * Template for event search form
 * @author Manuel Lara - Smad IT
 * */
?>
<div class="form-search-events">
    <h3><?php _e('Search your past events') ?></h3>
    <select id="search_year" name="year">
        <option value="all"><?php _e('- Select a Year -', $lang); ?></option>
        <?php $min_year = date('Y'); for($year = $min_year-2; $year <= ($min_year+1); $year++): ?>
            <option value="<?php echo $year; ?>" <?php if($year == $select_year) { echo 'selected="selected"'; } ?>><?php echo $year; ?></option>
        <?php endfor; ?>
    </select>
    <select id="search_month" name="month" disabled="disabled">
        <option value="all"><?php _e('- Select a Month -', $lang); ?></option>
            <?php for($month = 1; $month <= 12; $month++): $ktime = mktime(1,1,1, $month, 1,1); ?>
                <option value="<?php echo date('m', $ktime); ?>" <?php if($month == $select_month) { echo 'selected="selected"'; } ?>><?php echo date('F', $ktime); ?></option>
            <?php endfor; ?>
    </select>
</div>