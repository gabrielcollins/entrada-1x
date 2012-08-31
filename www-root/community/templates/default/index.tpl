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

	<link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />
	<link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />

	{$page_head}

	<style type="text/css">
	#site-header {literal}{{/literal}
		background: transparent url('{$template_relative}/images/header-{$site_theme}.gif') no-repeat bottom;
	{literal}}{/literal}
	</style>
</head>
<body>
{$sys_system_navigator}
<div id="site-container">
	<div id="site-header">
		<div>
			<img src="{$template_relative}/images/community-icon.gif" width="101" height="130" alt="" style="vertical-align: bottom; margin-right: 10px" />
			<span class="community-title">{$site_community_title}</span>
		</div>
	</div>
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
				</td>
				<td class="column">
					{$site_breadcrumb_trail}
					{$child_nav}
					<div class="content">
						{$page_content}
					</div>
				</td>
				<td class="column">
					{$page_sidebar}
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