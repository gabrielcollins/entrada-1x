<?php

if(isset($_GET['id'])){
	$course_id = $_GET['id'];
}


$query		= "	SELECT d.course_name, a.event_title, c.eventtype_title, b.duration 
				FROM events as a 
				LEFT JOIN event_eventtypes as b 
				ON a.event_id = b.event_id 
				LEFT JOIN events_lu_eventtypes  AS c 
				ON b.eventtype_id = c.eventtype_id 
				LEFT JOIN courses as d 
				ON a.course_id = d.course_id 
				WHERE a.course_id = ".$db->qstr($course_id).";";

$results	= $db->GetAll($query); 
?>
<h1>Event Types Report for <?php echo $results[0]['course_name'];?> </h1>
<table>
	<tr style="font-weight:bold;">
		<td>Event</td>
		<td>Event Type</td>
		<td>Duration</td>
	</tr>
	<?php
	foreach($results as $key=>$result){
		?>
	<tr>
		<td><?php echo $result['event_title'];?></td>
		<td><?php echo $result['eventtype_title'];?></td>
		<td><?php echo $result['duration'];?> minutes</td>
	</tr>
	<?php
	}
	
	?>
</table>
<br/>
Return to <?php echo "<a href=\"".ENTRADA_URL."/admin/courses?id=".$COURSE_ID."&section=content\">".$results[0]['course_name']."</a>";?>