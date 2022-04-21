<?php


add_action( 'show_user_profile', 'ahh_user_profile_fields' );
add_action( 'edit_user_profile', 'ahh_user_profile_fields' );

function ahh_user_profile_fields( $user ) { ?>
    <h3><?php _e("Extra profile information", "blank"); ?></h3>

    <table class="form-table">
	<?php
	if(get_the_author_meta( 'massage_license', $user->ID )!=''){?>
    <tr>
        <th><label for="address"><?php _e("Massage License"); ?></label></th>
        <td>
            <img src="<?php echo esc_attr( get_the_author_meta( 'massage_license', $user->ID ) ); ?>" class="regular-text" /><br />
            
        </td>
    </tr>
	<?php } ?>
	
	<?php
	if(get_the_author_meta( 'liability_insurance', $user->ID )!=''){?>
    <tr>
        <th><label for="city"><?php _e("Liability License"); ?></label></th>
        <td>
            <img src="<?php echo esc_attr( get_the_author_meta( 'liability_insurance', $user->ID ) ); ?>" class="regular-text" /><br />
        </td>
    </tr>
    <?php } ?>
	
    </table>
<?php }

