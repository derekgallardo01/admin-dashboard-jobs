<?php

/**
 * Template to Meta Box show to the Jobs available
 **/
?>
<div id="box_manage_jobs" class="stuffbox" >

<div class="eventarchive_head">LOCATION ARCHIVES <span class="admlogo" > <img src="<?php header_image(); ?>" height="44px" alt="lo" /></span> </div>


<form action="" name="archive_location" id="archive_location"  >
<div class="event_search">

<input type="hidden" name="page" id="page" value="<?php echo $_GET["page"];?>" />
<input type="hidden" name="lid" id="lid" value="<?php echo $_GET["lid"];?>" />
<input type="hidden" name="archive_location" id="archive_location" value="<?php echo $_GET["archive_location"];?>" />

<span class="year"> ENTER YEAR </span>
<input name="event_year" value="<?php echo $_GET["event_year"];?>" /> 
<?php
$pagelink = 'admin.php?page='.$_GET["page"].'&lid='.$_GET["lid"].'&archive_location='.$_GET["archive_location"].'&event_year='.$_GET["event_year"].'';
for($i=1;$i<13;$i++){
$mname = date('F',strtotime('01.'.$i.'.2001'));
print("<a href='".$pagelink."&month=".$i."'><span class='month'>".$mname."</span></a>");
}
?>
</div></form>

</div>

<div class="stuffbox">
<div class="bottomrow">
<div class="leftcol">
<span class="eventarchive_head">
<?php
if(isset($_GET["month"])){
	echo date('F',strtotime('01.'.$_GET["month"].'.2001'));
	
}
?>
</span>
</div>
<div class="rightcol">
<div class="lochead">
<?php
echo $lead[14].' '.$lead[48];

?>
</div>

<div class="lochead">
<?php
echo 'Submit Job Entry # '.$lead["id"];
//print_r($lead);
?>
</div>

</div>
</div>

<div class="toprow"> &nbsp; </div>

<form method="post">		<?/* <p>	<select name="archive_note" id="archive_note" >    <option value="">Select Option</option>    <!--<option value="activate">Activate</option>-->    <option value="archive">Archive</option>    <option value="delete">Delete</option>	    </select>	<input type="submit" name="submit_status" id="submit_status" />	</p>		 */?>
       
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
				<div class="firstdivrow"><span class="lochead">Location #<?php echo $location->ID;?></span></div>
				
				<div class="seconddivrow"><div class="leftcol">
				<table class="" border="0">
                    <tbody>
						<tr>
                            <td>
							<span class="subhead">Company Name: 
							</span><span class="locvalue"><?php echo $details[52];?></span>
							</td>
						</tr>
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
							<span  class="subhead">Invitation Request: </span><span class="locvalue"><?php 
							if(is_array(unserialize($location->users_invited)))
							print_r(count(unserialize($location->users_invited)));
							?></span>
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
					<a class="mainlinkred" href="admin.php?page=archive_location&lid=<?php echo $_GET["lid"];?>&archive_location=yes&delete_location=yes&loid=<?php echo $location->ID; ?>"  onclick="return confirm('Do you want delete this location?')" ><?php _e('DELETE EVENT', $lang); ?></a></div>
					
				</div>
				</div>
                <?php endforeach; ?>				
				
				
            <?php endif; ?>
			
    </form>

 
    <script type="text/javascript" src="<?php echo plugins_url('box_locations.js', __FILE__); ?>"></script>
</div>