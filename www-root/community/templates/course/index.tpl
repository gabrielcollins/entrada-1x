<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$site_default_charset}" />

	<title>{$page_title}</title>

	<meta name="description" content="{$page_description}" />
	<meta name="keywords" content="{$page_keywords}" />

	<meta name="robots" content="index, follow" />

	<link href="{$sys_website_url}/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="{$sys_website_url}/javascript/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/jquery/jquery-ui.min.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>

	<script type="text/javascript" src="{$sys_website_url}/javascript/scriptaculous/prototype.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/scriptaculous/scriptaculous.js"></script>
	
	<link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />
	<link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />

	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/livepipe.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/window.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/selectmultiplemod.js"></script>

	{$page_head}

	<style type="text/css">
	body {literal}{{/literal}
		behavior: url('{$sys_website_url}/css/fixes/csshoverfix.htc');
	{literal}}{/literal}
	#site-header {literal}{{/literal}
		background: transparent url('{$template_relative}/images/header-{$site_theme}.gif') no-repeat bottom;
	{literal}}{/literal}
	</style>
	%JQUERY%
</head>
<body>
{$sys_system_navigator}
<div class="no-printing" style="{php} echo (((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) ? "margin-top: 10px; " : ""); {/php}margin-bottom: 5px">
	<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td style="width: 185px; height: 25px; background-color: #003366"><img src="{$template_relative}/images/pixel.gif" width="185" height="25" alt="" title="" /></td>
		<td colspan="3" style="width: 765px; height: 25px; background-color: #EEEEEE; padding-left: 20px">
			<!-- Main Header Navigation Menu Start -->
			<table width="100%" cellspacing="1" cellpadding="1" border="0">
			<tr>
				<td style="text-align: left; padding-right: 15px; white-space: nowrap">{php} echo (((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) ? "<span class=\"content-small\">:. Welcome".(((int) $_SESSION["details"]["lastlogin"]) ? " back" : "")." ".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"].".</span>" : ""); {/php}</td>
				<td style="text-align: right; padding-right: 5px; white-space: nowrap">&nbsp;</td>
			</tr>
			</table>
			<!-- Main Header Navigation Menu End -->
		</td>
	</tr>
	<tr>
		<td style="width: 185px; height: 48px; background-color: #EEEEEE; text-align: center"><img src="{$template_relative}/images/pixel.gif" width="185" height="48" alt="" title="" /></td>
		<td style="width: 538px; height: 48px; background-color: #EEEEEE; text-align: left"><img src="{$template_relative}/images/school_of_medicine.gif" width="368" height="48" alt="School of Medicine" title="School of Medicine" /></td>
		<td colspan="2" style="width: 227px; height: 48px; background-color: #EEEEEE; text-align: left; vertical-align: bottom"><img src="{$template_relative}/images/medtech_logo.gif" width="227" height="48" alt="Medical Education Technology Unit" title="Medical Education Technology Unit" /></td>
	</tr>
	<tr>
		<td style="width: 185px; height: 2px; background-color: #003366; text-align: left"><img src="{$template_relative}/images/pixel.gif" width="185" height="2" alt="" title="" /></td>
		<td colspan="3" style="width: 765px; height: 2px; background-color: #003366; text-align: left"><img src="{$template_relative}/images/pixel.gif" width="595" height="2" alt="" title="" /></td>
	</tr>
	<tr>
		<td style="width: 185px; height: 10px; background-color: #FFFFFF"><img src="{$template_relative}/images/pixel.gif" width="185" height="10" alt="" title="" /></td>
		<td style="width: 538px; height: 10px; background-color: #FFFFFF"><img src="{$template_relative}/images/pixel.gif" width="368" height="10" alt="" title="" /></td>
		<td style="width: 140px; height: 10px; background-color: #A8CB49"><img src="{$template_relative}/images/pixel.gif" width="140" height="10" alt="" title="" /></td>
		<td style="width: 87px; height: 10px; background-color: #FFFFFF"><img src="{$template_relative}/images/pixel.gif" width="87" height="10" alt="" title="" /></td>
	</tr>
	</table>
</div>
<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
{php} if((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) : {/php}
	<tbody class="no-printing">
		<tr>
			<td style="width: 100%" colspan="2">
				<div id="screenTabs">
					<div id="tabs">
						<ul>
						{php}
							echo navigator_tabs();
						{/php}
						</ul>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
{php} endif; {/php}	
</table>
<div id="site-container">
	<div id="site-body">
		<table id="content-table" style="width: 100%; table-layout: fixed" cellspacing="0" cellpadding="0" border="0">
		<colgroup>
			<col style="width: 19%" />
			<col style="width: 62%" />
			<col style="width: 19%" />
		</colgroup>
		<tbody>
			<tr>
				<td class="column">
					{include file="navigation_primary.tpl" site_primary_navigation=$site_primary_navigation}
					<br />
					{$page_sidebar}
				</td>
				<td class="column" colspan="2">
					{$site_breadcrumb_trail}
					<span class="community-title">{$site_community_title}</span>
					{$child_nav}
					<div class="content" style="padding-left: 10px;">
						{$page_content}
					</div>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
	<div id="site-footer">
		<div style="padding: 10px 5px 15px 22%; text-align: left" class="content-copyright">
			{php}echo COPYRIGHT_STRING;{/php}
		</div>
	</div>
</div>
{php}if(((!defined("DEVELOPMENT_MODE")) || (!(bool) DEVELOPMENT_MODE)) && (defined("GOOGLE_ANALYTICS_CODE")) && (GOOGLE_ANALYTICS_CODE != "")) :{/php}
<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	var pageTracker = _gat._getTracker("{php} echo GOOGLE_ANALYTICS_CODE;{/php}");
	pageTracker._initData();
	pageTracker._trackPageview();
</script>
{php}endif;{/php}
</body>
</html>