<?php

/**
 * Template to Meta Box show to the Jobs available
 **/
?>
<div id="box_manage_jobs" class="stuffbox" >
<div class="event_head">Events</div>
<div class="toprow"><div class="leftcol"><a class="toplink" href="admin.php?page=archive_location&lid=<?php echo $_GET["lid"];?>&archive_location=yes">VIEW ARCHIVED EVENTS</a></div><div class="rightcol"><a  class="mainlink" href="admin.php?page=gf_entries&view=<?php echo $_GET["view"];?>&id=<?php echo $_GET["id"];?>&lid=<?php echo $_GET["lid"];?>&dir=<?php echo $_GET["dir"];?>&filter&paged=<?php echo $_GET["paged"];?>&pos=<?php echo $_GET["pos"];?>&field_id&operator&archive_all_location=yes"  onclick="return confirm('Do you want to archive all the location?')">ARCHIVE ALL EVENTS</a></div></div>

<form method="post">	
<?php /* <p>	<select name="archive_note" id="archive_note" >    <option value="">Select Option</option>    <!--<option value="activate">Activate</option>-->    <option value="archive">Archive</option>    <option value="delete">Delete</option>	    </select>	<input type="submit" name="submit_status" id="submit_status" />	</p>		 */?>

       
            <?php 
			$num=0;
			if(count($locations) > 0): 
			
					foreach($locations as $key=>$location) :    
					$num++;				
                    $details = maybe_unserialize($location->data);
                    $count_users = $details[Admin_Jobs::$field_id_number_therapist];
                    $users_inviteds = null;
                    $ids_users_accept = null;
                    $users_therapists = array();
					
					$datetime = AJ_Location::getDatesEventByLocation($location->ID);
					
                ?>
				<div class="divrow"><div class="leftcol">
				<table class="" border="0">
                    <tbody>
                        <tr>
                            <td >
							<span class="lochead">Location #<?php echo $location->ID;//-$firstlocationid[0]->ID+1;?></span>
							</td></tr>
						<tr>
                            <td>
							<span class="subhead">Company Name: 
							</span><span class="locvalue"><?php echo $details[52];?></span>
							</td></tr>
						<tr>
                            <td>
							<span class="subhead">Date: </span>
							<span class="locvalue"><?php
							$sep = '';
							foreach($datetime as $dt){
								echo $sep.date('M d, Y',strtotime($dt->month.'/'.$dt->day.'/'.$dt->year));
								$sep = ', ';
							}
							?></span>
							</td></tr>
						<tr>
                            <td>
							<span  class="subhead">Time: </span>
							<span class="locvalue">
							<?php $sep = '';
							foreach($datetime as $dt){
								echo $sep.$dt->stime.' To '.$dt->etime;
								$sep = ', ';
							}?></span>
							</td></tr>
						<tr>
                            <td>
							<span  class="subhead">Invitation Request: </span><span class="locvalue"><?php echo $num;?></span>
							</td></tr>
					
						 </tbody>
                </table>
				</div>
				
				<div class="rightcol">
					<div>
					<?php /* ?><a href="javascript:void(0);" class="button-secondary" onclick="blViewDatesEvents(this,<?php echo $location->ID; ?>);"><?php _e('View dates', $lang); ?></a><? */ ?>
					
					<a href="admin.php?page=view_location&lid=<?php echo $location->ID; ?>&view=entry&id=2&leid=<?php echo $_GET["lid"]?>&dir=DESC&filter&paged=1&pos=2&field_id&operator&" class="mainlinkred" ><?php _e('EVENT DETAILS', $lang); ?></a>
					</div>
					<p></p>											<div>											
					<a class="mainlinkred" href="admin.php?page=gf_entries&view=<?php echo $_GET["view"];?>&id=<?php echo $_GET["id"];?>&lid=<?php echo $_GET["lid"];?>&dir=<?php echo $_GET["dir"];?>&filter&paged=<?php echo $_GET["paged"];?>&pos=<?php echo $_GET["pos"];?>&field_id&operator&archive_one_location=yes&loid=<?php echo $location->ID; ?>"  onclick="return confirm('Do you want archive this location?')"><?php _e('ARCHIVE EVENT', $lang); ?></a></div> 											
					<p></p>											<div>											
		<a class="mainlinkred" href="admin.php?page=wpi_page_manage_invoice&loid=<?php echo $location->ID; ?>&lid=<?php echo $_GET["lid"];?>" ><?php _e('Invoice', $lang); ?></a></div>
					
		<?php  /* ?>		
		<p></p><div>
		<a class="mainlinkred" href="admin.php?page=gf_entries&view=<?php echo $_GET["view"];?>&id=<?php echo $_GET["id"];?>&lid=<?php echo $_GET["lid"];?>&dir=<?php echo $_GET["dir"];?>&filter&paged=<?php echo $_GET["paged"];?>&pos=<?php echo $_GET["pos"];?>&field_id&operator&delete_location=yes&loid=<?php echo $location->ID; ?>"
		onclick="return confirm('Do you want delete this location?')" ><?php _e('Invoices4444', $lang); ?></a></div>			
		<?php */ ?>	
		
				</div>
				</div>
                <?php endforeach; ?>				
				
				
            <?php endif; ?>
			<div id="addmorelocation"></div>
				
				</br><div class="morelocation">
				<a href="javascript:void(0)" onclick="addmorelocation()" class="mainlinkred">Add Another Location</a>
				</div></br>
				
    </form>

 
    <script type="text/javascript" src="<?php echo plugins_url('box_locations.js', __FILE__); ?>"></script>
</div>