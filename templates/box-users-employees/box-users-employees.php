<div id="box_users_employees" class="stuffbox">
    <h3><label for="name"><?php _e('Corporate Employee', $lang); ?></label></h3>
    <form method="post">
        <div class="inside">
            <table class="widefat">
                <tbody>
                    <tr>
                        <td class="entry-view-field-value">
                            <table id="info_users" class="gfield_list">
                                <thead>
                                    <tr>
                                        <th><?php _e('User', $lang); ?></th>
                                        <th><?php _e('Email', $lang); ?></th>
                                        <th><?php _e('Department', $lang); ?></th>
                                        <th><?php _e('Position', $lang); ?></th>
                                        <th><?php _e('Time selected', $lang); ?></th>
                                        <th><?php _e('Paid?', $lang); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if( !empty($participants_massage) ) {
                                        
                                        foreach ($participants_massage as $email) {
                                            $ht = ''; 
                                            $u = get_user_by('email', $email);

                                            $position = get_user_meta($u->ID,'user_position',true);
                                            if(!$position) {
                                                $position = '-';
                                            }
                                            
                                            $department = get_user_meta($u->ID,'user_department',true);
                                            if(!$department) {
                                                $department = '-';
                                            }
                                            $name = $u->first_name ? $u->first_name : $u->user_login;
                                            
                                            $alltimes = Admin_Jobs::getTimesReservedUser($u->ID,$lead['id']);
                                            $n = count($alltimes);

                                            $ht .= <<<HT
                                            <tr>
                                                <td rowspan="$n" class="vcenter bBottom">$name</td>
                                                <td rowspan="$n" class="vcenter bBottom">$email</td>
                                                <td rowspan="$n" class="vcenter bBottom">$department</td>
                                                <td rowspan="$n" class="vcenter bBottom">$position</td>
                                                {first}
                                            </tr>
HT;
                                            if( !empty($alltimes) ) {
                                                foreach($alltimes as $i=>$times) {
                                                    
                                                    if($times) {
                                                        $hour_start = strtotime($times->hour_start)/*+60*60*$offset*/;
                                                        $hour_end = strtotime($times->hour_end)/*+60*60*$offset*/;
                                                        $minutes = ($hour_end-$hour_start)/60;
                                                        $stimes = date('g:i a', $hour_start).' - '.date('g:i a', $hour_end).'<br/><b>'.sprintf(_n('(1 minute)', '(%s minutes)', $minutes, $lang), $minutes).'</b>';
                                                        
                                                    } else {
                                                        $stimes = __('No selected time', $lang);
                                                    }
                                                    $ynPaid = Admin_Jobs::checkIfPayService($lead['id'],$u->ID,$times->location_date_id) ? __('Yes', $lang) : __('No', $lang);
                                                    if($i == 0) {
                                                        $fT = '<td class="vcenter bBottom">'.$stimes.'</td>
                                                            <td class="vcenter bBottom">'.$ynPaid.'</td>';
                                                    } else {
                                                        $ht .= '<tr>
                                                            <td class="vcenter bBottom">'.$stimes.'</td>
                                                            <td class="vcenter bBottom">'.$ynPaid.'</td>
                                                        </tr>';
                                                    }
                                                }
                                            } else {
                                                $fT = '<td class="vcenter bBottom">'.__('No selected time', $lang).'</td><td class="vcenter bBottom">'.__('No', $lang).'</td>';
                                            }
                                            echo str_replace('{first}', $fT, $ht);
                                        }
                                        
                                    } else { ?>
                                        <tr>
                                            <td><?php _e('Not found users', $lang); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>