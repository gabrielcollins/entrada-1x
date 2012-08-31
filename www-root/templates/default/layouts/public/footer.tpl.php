<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves as the main Entrada "public" footer layout file.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
?>
		</div>
		<div class="no-printing">
			<span class="copyright">%LASTUPDATED%<?php echo COPYRIGHT_STRING; ?></span> <a href="<?php echo ENTRADA_URL; ?>/privacy_policy" class="copyright">Privacy Policy</a>.
			<?php
			$time_end	= getmicrotime();
			if (SHOW_LOAD_STATS) {
				echo "<br /><span class=\"copyright\">Rendered and loaded page in ".round(($time_end - $time_start), 4)." seconds.</span>\n";
			}
			?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<?php
if (((!defined("DEVELOPMENT_MODE")) || (!(bool) DEVELOPMENT_MODE)) && (defined("GOOGLE_ANALYTICS_CODE")) && (GOOGLE_ANALYTICS_CODE != "")) {
	?>
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	var pageTracker = _gat._getTracker("<?php echo GOOGLE_ANALYTICS_CODE; ?>");
	pageTracker._initData();
	pageTracker._trackPageview();
	</script>
	<?php
}
?>
</body>
</html>