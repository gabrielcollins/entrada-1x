<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves as the main Entrada administrative header layout file.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>" />

	<title>%TITLE%</title>

	<meta name="description" content="%DESCRIPTION%" />
	<meta name="keywords" content="%KEYWORDS%" />

	<meta name="robots" content="index, follow" />

	<meta name="MSSmartTagsPreventParsing" content="true" />
	<meta http-equiv="imagetoolbar" content="no" />
	
	<link href="<?php echo ENTRADA_RELATIVE; ?>/javascript/calendar/css/xc2_default.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/css/print.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="print" />
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/w3c/p3p.xml" rel="P3Pv1" type="text/xml" />
	
	<link href="<?php echo ENTRADA_URL;?>/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery-ui.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>	
	<script type="text/javascript">jQuery.noConflict();</script>
	%JQUERY%
	
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/prototype.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/scriptaculous.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/growler.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/livepipe.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/window.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/selectmultiplemod.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/common.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/selectmenu.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/calendar/config/xc2_default.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/calendar/script/xc2_inpage.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	%HEAD%
</head>
<body>
<?php echo load_system_navigator(); ?>
<div class="no-printing" style="margin-bottom: 5px">
	<div class="tail-top">
		<div class="entrada-header">
			<a href="<?php echo ENTRADA_URL; ?>"><img src="<?php echo TEMPLATE_RELATIVE; ?>/images/logo.jpg" alt="<?php echo APPLICATION_NAME; ?>" title="<?php echo APPLICATION_NAME; ?>" width="359" height="100" border="0" /></a>
		</div>
	</div>
</div>

<table style="width: 950px" cellspacing="0" cellpadding="0" border="0">
<?php if((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) : ?>
	<tbody class="no-printing">
		<tr>
			<td style="width: 100%" colspan="2">
				<?php echo navigator_tabs();?>
			</td>
		</tr>
	</tbody>
<?php endif; ?>
<tbody>
	<tr>
		<td style="width: 20%; vertical-align: top; padding: 5px" class="no-printing">
			%SIDEBAR%
		</td>
		<td style="width: 80%; vertical-align: top; padding: 5px">
			<div style="width: 100%">
				%BREADCRUMB%
