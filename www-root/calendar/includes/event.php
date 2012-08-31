<?php 
define('BASE', '../');

include_once(BASE.'functions/init.inc.php'); 
require_once(BASE.'functions/template.php');

function decode_popup ($item) {
	$item = stripslashes(rawurldecode($item));
	$item = str_replace('\\','',$item);
	return $item;
}

/*
Array
(
    [event_start] => 0830
    [event_end] => 1030
    [start_unixtime] => 1204637400
    [end_unixtime] => 1204644600
    [event_text] => Vaccines+and+Immunizations
    [event_length] => 7200
    [event_overlap] => 0
    [description] => 
    [status] => CONFIRMED
    [class] => PUBLIC
    [spans_day] => 
    [location] => BOT B139
    [organizer] => a:1:{i:0;a:2:{s:4:"name";s:14:"Heather Onyett";s:5:"email";s:30:"MAILTO:onyetth@post.queensu.ca";}}
    [attendee] => a:0:{}
    [calnumber] => 1
    [calname] => 2011
    [url] => https://developer.qmed.ca/~simpson/projects/courses/events?id=4929
)
*/

$event			= unserialize(stripslashes($_REQUEST['event_data']));
$organizers		= "";
$organizer 		= unserialize($event['organizer']);
$attendee 		= unserialize($event['attendee']);

// Format event time
// All day
if ($_POST['time'] == -1) {
	$event_times = $lang['l_all_day'];
} else {
	$event_times = date($timeFormat, $event['start_unixtime']) . ' - ' .  date($timeFormat, $event['end_unixtime']); 
}

$event['description'] 	= stripslashes(utf8_decode(urldecode($event['description'])));
$event['event_text']	= stripslashes(utf8_decode(urldecode($event['event_text'])));
$event['location']		= stripslashes(utf8_decode(urldecode($event['location'])));

if ($event['description']) {
	$event['description'] = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", '<a href="\0" target="_blank">\0</a>', $event['description']);
}

if((is_array($organizer)) && (count($organizer))) {
	foreach($organizer as $teacher) {
		if(trim($teacher["name"])) {
			$organizers .= $teacher["name"].((trim($teacher["email"])) ? " &lt;<a href=\"".strtolower(trim($teacher["email"]))."\">".str_replace("mailto:", "", strtolower(trim($teacher["email"])))."</a>&gt;" : "")."<br />";
		}
	}
} else {
	$organizers = "To Be Announced";	
}

if(is_array($attendee)) {
	$i=0;
	$display .= $attendee_lang . ' - ';
	foreach ($attendee as $val) {	
		$attendees .= $attendee[$i]["name"] . ', ';
		$i++;
	}
	$attendee = substr($attendees,0,-2);
}

if (!$event['location']) {
	$event['location'] = "To Be Announced";
}

if ($event['url']) {
	$event['url'] = '<a href="'.$event['url'].'" target="_blank" style="font-size: 11px; text-decoration: underline">'.$event['url'].'</a><div class="content-small"><strong>Notice:</strong> You must log in using your MEdTech username and password.</div>';
}

if (sizeof($attendee) == 0) $attendee = '';
if (sizeof($organizer) == 0) $organizer = '';

switch ($event['status']){
	case 'CONFIRMED':
		$event['status'] =	$lang['l_status_confirmed'] ; 
		break;
	case 'CANCELLED':
		$event['status'] =	$lang['l_status_cancelled'] ; 
		break;
	case 'TENTATIVE':
		$event['status'] =	$lang['l_status_tentative'] ; 
		break;
}

$page = new Page(BASE.'templates/'.$template.'/event.tpl');

$page->replace_tags(array(
	'charset'			=> $charset,
	'ocr_url'			=> $event['url'],
	'cal' 				=> $event['calname'],
	'event_text' 		=> $event['event_text'],
	'event_times' 		=> $event_times,
	'description' 		=> $event['description'],
	'organizer' 		=> $organizers,
	'attendee'	 		=> $attendee,
	'status'	 		=> $event['status'],
	'location' 			=> stripslashes($event['location']),
	'cal_title_full'	=> $event['calname'].' '.$lang['l_calendar'],
	'template'			=> $template,
	'l_organizer'		=> $lang['l_organizer'],
	'l_attendee'		=> $lang['l_attendee'],
	'l_status'			=> $lang['l_status'],
	'l_location'		=> $lang['l_location'],
	'l_event_times'		=> $lang['l_event_times'],
	'l_ocr_url'			=> $lang['l_ocr_url']
	));
$page->output();
?>