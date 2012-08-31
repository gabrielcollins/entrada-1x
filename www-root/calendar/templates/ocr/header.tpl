<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset={CHARSET}" />
	<title>Class of {CALENDAR_NAME}: {DISPLAY_DATE}</title>
	<!-- switch rss_available on -->
	<link rel="alternate" type="application/rss+xml" title="RSS" href="{DEFAULT_PATH}/rss/rss.php?cal={CAL}&amp;rssview={CURRENT_VIEW}">
	<!-- switch rss_available off -->
	
	<meta name="robots" content="noindex, nofollow" />
	
	<meta name="MSSmartTagsPreventParsing" content="true" />
	<meta http-equiv="imagetoolbar" content="no" />
	
  	<link href="{DEFAULT_PATH}templates/{TEMPLATE}/default.css" rel="stylesheet" type="text/css" />
	<link href="{DEFAULT_PATH}../css/common.css" rel="stylesheet" type="text/css" media="all" />
	<link href="{DEFAULT_PATH}../css/print.css" rel="stylesheet" type="text/css" media="print" />
			
	{EVENT_JS}
	
	{CSS_IMAGES}
</head>
<body>
<form name="eventPopupForm" id="eventPopupForm" method="post" action="includes/event.php" style="display: none;">
  <input type="hidden" name="date" id="date" value="" />
  <input type="hidden" name="time" id="time" value="" />
  <input type="hidden" name="uid" id="uid" value="" />
  <input type="hidden" name="cpath" id="cpath" value="" />
  <input type="hidden" name="event_data" id="event_data" value="" />
</form>
<form name="todoPopupForm" id="todoPopupForm" method="post" action="includes/todo.php" style="display: none;">
  <input type="hidden" name="todo_data" id="todo_data" value="" />
  <input type="hidden" name="todo_text" id="todo_text" value="" />
</form>
<div class="no-printing" style="margin-bottom: 5px">
	<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td style="width: 185px; height: 25px; background-color: #003366"><img src="{DEFAULT_PATH}../images/pixel.gif" width="185" height="25" alt="" title="" /></td>
		<td colspan="3" style="width: 765px; height: 25px; background-color: #EEEEEE; padding-left: 20px">
			<!-- Main Header Navigation Menu Start -->
			<table width="100%" cellspacing="1" cellpadding="1" border="0">
			<tr>

				<td style="text-align: left; padding-right: 15px; white-space: nowrap"></td>
				<td style="text-align: right; padding-right: 5px; white-space: nowrap">&nbsp;</td>
			</tr>
			</table>
			<!-- Main Header Navigation Menu End -->
		</td>
	</tr>
	<tr>
		<td style="width: 185px; height: 48px; background-color: #EEEEEE; text-align: center"><img src="{DEFAULT_PATH}../images/pixel.gif" width="185" height="48" alt="" title="" /></td>

		<td style="width: 538px; height: 48px; background-color: #EEEEEE; text-align: left"><img src="{DEFAULT_PATH}../images/school_of_medicine.gif" width="368" height="48" alt="School of Medicine" title="School of Medicine" /></td>
		<td colspan="2" style="width: 227px; height: 48px; background-color: #EEEEEE; text-align: left; vertical-align: bottom"><img src="{DEFAULT_PATH}../images/medtech_logo.gif" width="227" height="48" alt="Medical Education Technology Unit" title="Medical Education Technology Unit" /></td>
	</tr>
	<tr>
		<td style="width: 185px; height: 2px; background-color: #003366; text-align: left"><img src="{DEFAULT_PATH}../images/pixel.gif" width="185" height="2" alt="" title="" /></td>
		<td colspan="3" style="width: 765px; height: 2px; background-color: #003366; text-align: left"><img src="{DEFAULT_PATH}../images/pixel.gif" width="595" height="2" alt="" title="" /></td>
	</tr>
	<tr>
		<td style="width: 185px; height: 10px; background-color: #FFFFFF"><img src="{DEFAULT_PATH}../images/pixel.gif" width="185" height="10" alt="" title="" /></td>

		<td style="width: 538px; height: 10px; background-color: #FFFFFF"><img src="{DEFAULT_PATH}../images/pixel.gif" width="368" height="10" alt="" title="" /></td>
		<td style="width: 140px; height: 10px; background-color: #A8CB49"><img src="{DEFAULT_PATH}../images/pixel.gif" width="140" height="10" alt="" title="" /></td>
		<td style="width: 87px; height: 10px; background-color: #FFFFFF"><img src="{DEFAULT_PATH}../images/pixel.gif" width="87" height="10" alt="" title="" /></td>
	</tr>
	</table>
</div>

<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
<tbody class="no-printing">
	<tr>
		<td style="width: 100%" colspan="2">
		{CALENDAR_TABS}				
		</td>
	</tr>
</tbody>
<tbody>
	<tr>
		<td style="width: 200px; vertical-align: top; padding: 5px" class="no-printing">
			{SIDEBAR}
		</td>
		<td style="width: 750px; vertical-align: top; text-align: left; padding-left: 5px; padding-top: 5px; background-color: #FFFFFF">
			<div style="width: 750px">
