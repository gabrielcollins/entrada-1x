<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * $Id: podcasts.inc.php 1171 2010-05-01 14:39:27Z ad29 $
 */

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('podcast', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$sidebar_html  = "<div style=\"text-align: center\">\n";
	$sidebar_html .= "	<a href=\"".str_replace(array("https://", "http://"), "itpc://", ENTRADA_URL)."/podcasts/feed\"><img src=\"".ENTRADA_URL."/images/podcast-dashboard-image.jpg\" width=\"149\" height=\"99\" alt=\"MEdTech Podcasts\" title=\"Subscribe to our Podcast feed.\" border=\"0\"></a><br />\n";
	$sidebar_html .= "	<a href=\"".str_replace(array("https://", "http://"), "itpc://", ENTRADA_URL)."/podcasts/feed\" style=\"color: #557CA3; font-size: 14px\">Subscribe Here</a>";
	$sidebar_html .= "</div>\n";
	new_sidebar_item("Podcasts in iTunes", $sidebar_html, "podcast-bar", "open", "1.1");

	?>
<div style="text-align: left; border-bottom: 1px #b3b3b2 solid">
	<a href="<?php echo str_replace(array("https://", "http://"), "itpc://", ENTRADA_URL); ?>/podcasts/feed"><img src="<?php echo ENTRADA_URL; ?>/images/podcast-header-image.jpg" width="750" height="238" alt="Podcasts in iTunes" title="" border="0" /></a>
</div>

<h1>Podcasts Now Available</h1>
Click the image above to launch iTunes, then enter your MEdTech username and password when iTunes prompts you.
<table style="width: 100%" cellspacing="3" cellpadding="3" border="0">
	<colgroup>
		<col style="width: 50%" />
		<col style="width: 50%" />
	</colgroup>
	<tbody>
		<tr>
			<td style="vertical-align: top; line-height: 175%">
				<h2>School of Medicine Podcasts</h2>
				<strong>The School of Medicine provides many of the presented learning events as digitally recorded podcasts available for download through iTunes or as MP3 files in the learning events' resources section.</strong>
				<br /><br />
			Podcast files can be accessed as a manual download from within the Learning Event page, or as an automatic download whenever iTunes is launched (click the button below to configure your iTunes).
				<br /><br />
			These podcasts are only accessible by Queen's School of Medicine learners and teachers after logging into the Online Course Resources system.
				<br /><br />
			Please note that it is forbidden to share podcast mp3 files or their derivatives beyond the Queen's medical school community, without prior consent from the teachers involved.
			</td>
			<td style="vertical-align: top; line-height: 175%">
				<h2>What is Podcasting?</h2>
			Podcasting is a form of audio broadcasting that allows individuals to subscribe to a group of audio files over the Internet and listen to them on their personal computer or portable media player. Although audio files by themselves have been available on the Internet for some time, the ability to subscribe to a feed and have new audio files automatically downloaded for you has made podcasting an extremely powerful and popular medium.
				<br /><br />
			The term podcasting is closely associated with Apple's iPod, however, it is important to note than an iPod is not required to listen to a podcast. All that is required to listen to a podcast is a podcasting client (such as <a href="http://www.itunes.com">iTunes</a>) and an Internet connection.
				<br /><br />
				<form>
					<div>
						<input type="button" class="button" onclick="window.location='<?php echo str_replace(array("https://", "http://"), "itpc://", ENTRADA_URL); ?>/podcasts/feed'" value="Launch iTunes" />
					</div>
				</form>

			</td>
		</tr>
	</tbody>
</table>
<?php } ?>