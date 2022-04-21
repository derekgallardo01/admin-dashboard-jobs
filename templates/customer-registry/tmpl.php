<?php $states = GFCommon::get_us_states(); ob_start(); ?>
<div id="customer-users-registry" class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php _e('Customer Users Registry', self::LANG); ?></h2>
    <div class="form-ajax psearch">
        <div>
            <select id="search_state" name="state" class="inpt-searchs">
                <option value=""><?php _e('Select a State', self::LANG); ?></option>
                <?php foreach($states as $code=>$st): ?>
                    <option value="<?php echo $st; ?>"><?php echo $st; ?></option>
                <?php endforeach; ?>
            </select>
            <input id="search_city" type="text" name="city" value="" placeholder="<?php echo __('City', self::LANG); ?>" class="inpt-searchs" />
            <input id="search_keyword" type="text" name="keyword" value="" placeholder="<?php echo __('Code Zip or Keyword', self::LANG); ?>" class="inpt-searchs" />
            <input id="search_name" type="text" name="name" value="" placeholder="<?php echo __('First or Last Name', self::LANG); ?>" class="inpt-searchs" />
            
            <a id="search-users" class="button-secondary" href="javascript:void(0);"><?php _e('Search', self::LANG); ?></a>
            <img id="loading-search-users" style="display: none;" src="<?php echo plugins_url('/box-details/images/ajax-loader.gif',dirname(__FILE__)); ?>" />
            <p id="show-settings-form" class="search-box"><a id="link-show-modal-settings" class="button-primary" href="javascript:void(0);"><?php _e('Change Search Radius', self::LANG); ?></a></p>
        </div>
        <p id="msg-result" style="display: none;" class="message-info fsize16"></p>
    </div>
        <div id="map-canvas" style="display: none;"></div>
        <div id="count-results" style="display: none;" class="fsize16"><?php _e('Users Found:', self::LANG); ?>&nbsp;<span class="count"></span></div>
        <p class="btn-send">
            <a class="show-modal-message button-primary" href="javascript:void(0);"><?php _e('Send Message', self::LANG); ?></a>
            <a class="show-modal-message button-secondary" href="javascript:void(0);"><?php _e('Send to all users', self::LANG); ?></a>
        </p>

		<table id="results-users" class="widefat">
            <thead>
                <tr>
                	<th class="manage-column column-cb check-column"><label class="lbl-all"><input type="checkbox" /></label></th>
                    <th class="manage-column column-username"><?php _e('Username', self::LANG); ?></th>
                    <th class="manage-column column-name"><?php _e('Name', self::LANG); ?></th>
                    <th class="manage-column column-email"><?php _e('Email', self::LANG); ?></th>
                    <th class="manage-column"><?php _e('Company Info', self::LANG); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                	<th class="manage-column column-cb check-column"><label class="lbl-all"><input type="checkbox" /></label></th>
                    <th class="manage-column column-username"><?php _e('Username', self::LANG); ?></th>
                    <th class="manage-column column-name"><?php _e('Name', self::LANG); ?></th>
                    <th class="manage-column column-email"><?php _e('Email', self::LANG); ?></th>
                    <th class="manage-column"><?php _e('Company Info', self::LANG); ?></th>
                </tr>
            </tfoot>
            <tbody id="list-result">
                    <tr>
                        <td colspan="5"><?php _e('Not has been searched a customer users', self::LANG); ?></td>
                    </tr>
            </tbody>
        </table>
        <p class="btn-send">
            <a class="show-modal-message button-primary" href="javascript:void(0);"><?php _e('Send Message', self::LANG); ?></a>
            <a class="show-modal-message button-secondary" href="javascript:void(0);"><?php _e('Send to all users', self::LANG); ?></a>
        </p>
        <div id="dialog-send-message" class="wrap modal-dialog" title="<?php _e('Send Message', self::LANG); ?>">
            <img id="ajax_loading" class="ajax-loading-dialog" src="<?php echo plugins_url('/modal-settings/images/ajax-loader-black.gif', dirname(__FILE__)); ?>" alt="" title="" style="display: none;" />
            <div>
                <label for="subject"><?php _e('Subject', self::LANG); ?>&nbsp;<span><?php echo $required; ?></span></label>
                <input id="subject" type="text" name="subject" value="" />
            </div>
            <div><?php echo wp_editor( stripcslashes(get_option('content_msg_email_users', '')), 'message-content', array('media_buttons' => false, 'textarea_rows' => 20) ); ?></div>
            <div>
                <p>On the email content you can use some placeholders to replace them for the real content with the data of each user:</p>
                <ul>
                    <li><b>{user}</b> : Replace the Full name of the user</li>
                    <li><b>{userlogin}</b> : Replace the login or username of the user</li>
                    <li><b>{userpass}</b> : Replace the password of the user generated in the CSV uploader</li>
                </ul>
            </div>
            <p id="message-result" style="display: none;"></p>
        </div>  
</div>
<?php return ob_get_clean(); ?>