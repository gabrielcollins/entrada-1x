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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_COMMUNITIES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}
?>
<div class="community-heading" style="width:749px; height: 154px">
	<div>
		<div class=corner-tl></div>
		<div class=corner-tr></div>
		<div class=corner-bl></div>
		<div class=corner-br></div>
		<div class=community-int-heading></div>
		<div class="community-contentblock bordering">
			<div class="community-content">
				<h3 style="line-height: 110%"><?php echo $translate->_("public_communities_heading_line"); ?></h3>
			</div>
		</div>
		<div class="community-int-footing">
			<div>
				Need a <strong>place</strong> for your <strong>group</strong> to sit? <a href="<?php echo ENTRADA_URL; ?>/communities?section=create">Create</a> an <strong>online community</strong> in 5 minutes.
			</div>
		</div>
	</div>
</div>

<div class="community-body">
	<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="">
	<colgroup>
		<col style="width: 35%" />
		<col style="width: 1%" />
		<col style="width: 64%" />
	</colgroup>
	<tbody>
	<tr>
		<td class="column-left">
			<div style="min-height: 430px; min-width: 250px;">
				<div style="background-color: #EBF4BF; border: 1px #669900 solid; margin: 4px; padding: 6px; text-align: center; vertical-align: middle">
					<a href="<?php echo ENTRADA_URL; ?>/communities?section=create" style="color: #669900; font-size: 14px"><strong>Create</strong><em style="padding-left: 3px; padding-right: 4px">a</em><strong>Community</strong></a>
				</div>
				<div style="text-align: center">
					<span class="content-small" style="color: #bababa"><strong>Powering</strong> <?php echo communities_count(); ?> communities</span>
				</div>

				<!-- 10 Most Active Communities -->
				<?php
				$query		= "
							SELECT a.`community_id`, b.`community_url`, b.`community_title`
							FROM `communities_most_active` AS a
							LEFT JOIN `communities` AS b
							ON a.`community_id` = b.`community_id`
							ORDER BY `activity_order` ASC
							LIMIT 0, 10";
				$results = $db->GetAll($query);
				if($results) {
					?>
					<h2>Most Active Communities</h2>
					<ol class="community-list">
					<?php
					foreach($results as $result) {
						?>
						<li><a href="<?php echo ENTRADA_URL."/community".$result["community_url"]; ?>"><?php echo html_encode(limit_chars($result["community_title"], 35)); ?></a></li>
						<?php
					}
					?>
					</ol>
					<?php
				}
				?>
				<!-- 10 Newest Communities -->
				<?php
				$query	= "
						SELECT a.`community_url`, a.`community_title`, a.`community_description`, a.`community_opened`
						FROM `communities` AS a
						WHERE a.`community_active` = '1'
						ORDER BY a.`community_opened` DESC
						LIMIT 0, 10";
				$results	= $db->CacheGetAll(CACHE_TIMEOUT, $query);
				if($results) {
					?>
					<h2>Newest Communities</h2>
					<ol class="community-list">
					<?php
					foreach($results as $result) {
						?>
						<li>
							<a href="<?php echo ENTRADA_URL."/community".$result["community_url"]; ?>" title="<?php echo html_encode(limit_chars($result["community_description"], 400)); ?>"><?php echo html_encode(limit_chars($result["community_title"], 35)); ?></a>
						</li>
						<?php
					}
					?>
					</ol>
					<?php
				}
				?>
			</div>
		</td>
		<td>&nbsp;</td>
		<td class="column-right">
			<div style="min-height: 430px; width: 400px;">
				<?php
				/**
				 * How many browse or search results to display per page.
				 */
				$RESULTS_PER_PAGE = 10;

				switch($ACTION) {
					case "browse" :
						$BREADCRUMB[] = array("url" => ENTRADA_URL."/".$MODULE, "title" => "Browse Communities");
						
						/**
						 * Browsing communities within a category.
						 */
						$CATEGORY_ID		= 0;
						
						/**
						 * The query that is actually be searched for.
						 */
						if((isset($_GET["category"])) && ((int) trim($_GET["category"]))) {
							$CATEGORY_ID = (int) trim($_GET["category"]);

							if(!$category_details = communities_fetch_category($CATEGORY_ID)) {
								$CATEGORY_ID = 0;
							}
						}

						if(!$CATEGORY_ID) {
							header("Location: ".ENTRADA_URL."/communities");
							exit;
						}
						?>
						<h2>Browse Communities</h2>
						<div style="margin-top: -10px; float: right">
							<a href="<?php echo ENTRADA_URL; ?>/communities?section=create&amp;category=<?php echo $CATEGORY_ID; ?>" style="color: #669900; font-size: 10px">create new community here</a>
						</div>
						<div style="margin-bottom: 15px">
							<div class="strong-green"><img src="<?php echo ENTRADA_URL; ?>/images/btn_attention.gif" width="11" height="11" alt="" title="" /> <?php echo html_encode($category_details["category_title"]); ?></div>
							<?php echo html_encode($community_details["category_description"]); ?>
						</div>
						<?php
						$query_counter	= "SELECT COUNT(*) AS `total_rows` FROM `communities` WHERE `category_id` = ".$db->qstr($CATEGORY_ID)." AND `community_active` = '1'";
						$query_search	= "SELECT `community_id`, `category_id`, `community_url`, `community_shortname`, `community_title`, `community_description`, `community_keywords` FROM `communities` WHERE `category_id` = ".$db->qstr($CATEGORY_ID)." AND `community_active` = '1' ORDER BY `community_title` ASC LIMIT %s, %s";

						/**
						 * Get the total number of results using the generated queries above and calculate the total number
						 * of pages that are available based on the results per page preferences.
						 */
						$result = ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query_counter) : $db->GetRow($query_counter));
						if($result) {
							$TOTAL_ROWS	= $result["total_rows"];

							if($TOTAL_ROWS <= $RESULTS_PER_PAGE) {
								$TOTAL_PAGES = 1;
							} elseif (($TOTAL_ROWS % $RESULTS_PER_PAGE) == 0) {
								$TOTAL_PAGES = (int) ($TOTAL_ROWS / $RESULTS_PER_PAGE);
							} else {
								$TOTAL_PAGES = (int) ($TOTAL_ROWS / $RESULTS_PER_PAGE) + 1;
							}
						} else {
							$TOTAL_ROWS		= 0;
							$TOTAL_PAGES	= 1;
						}

						/**
						 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
						 */
						if(isset($_GET["pv"])) {
							$PAGE_CURRENT = (int) trim($_GET["pv"]);

							if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
								$PAGE_CURRENT = 1;
							}
						} else {
							$PAGE_CURRENT = 1;
						}

						$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
						$PAGE_NEXT	= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

						if($TOTAL_PAGES > 1) {
							echo "<form action=\"".ENTRADA_URL."/communities\" method=\"get\" id=\"pageSelector\">\n";
							echo "<input type=\"hidden\" name=\"action\" value=\"browse\" />\n";
							echo "<input type=\"hidden\" name=\"category\" value=\"".$CATEGORY_ID."\" />\n";
							echo "<div style=\"margin-top: 10px; margin-bottom: 5px; text-align: right; white-space: nowrap\">\n";
							echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
							if($PAGE_PREVIOUS) {
								echo "<a href=\"".ENTRADA_URL."/communities?".replace_query(array("pv" => $PAGE_PREVIOUS))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$PAGE_PREVIOUS.".\" title=\"Back to page ".$PAGE_PREVIOUS.".\" style=\"vertical-align: middle\" /></a>\n";
							} else {
								echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
							}
							echo "</span>";
							echo "<span style=\"vertical-align: middle\">\n";
							echo "<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/communities?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($TOTAL_PAGES <= 1) ? " disabled=\"disabled\"" : "").">\n";
							for($i = 1; $i <= $TOTAL_PAGES; $i++) {
								echo "<option value=\"".$i."\"".(($i == $PAGE_CURRENT) ? " selected=\"selected\"" : "").">".(($i == $PAGE_CURRENT) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
							}
							echo "</select>\n";
							echo "</span>\n";
							echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
							if($PAGE_CURRENT < $TOTAL_PAGES) {
								echo "<a href=\"".ENTRADA_URL."/communities?".replace_query(array("pv" => $PAGE_NEXT))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$PAGE_NEXT.".\" title=\"Forward to page ".$PAGE_NEXT.".\" style=\"vertical-align: middle\" /></a>";
							} else {
								echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
							}
							echo "</span>\n";
							echo "</div>\n";
							echo "</form>\n";
						}

						/**
						 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
						 */
						$limit_parameter = (int) (($RESULTS_PER_PAGE * $PAGE_CURRENT) - $RESULTS_PER_PAGE);
						$query		= sprintf($query_search, $limit_parameter, $RESULTS_PER_PAGE);
						$results	= $db->GetAll($query);
						if($results) {
							echo "<div style=\"margin-left: 16px\">\n";
							foreach($results as $result) {
								if($result["community_description"]) {
									$description = limit_chars($result["community_description"], 350);
								} else {
									$description = "";
								}
								echo "<div id=\"result-".$result["community_id"]."\" style=\"width: 100%; margin-bottom: 10px; line-height: 16px;\">\n";
								echo "	<img src=\"".ENTRADA_URL."/images/list-community.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle; margin-right: 5px\" /><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\" style=\"vertical-align: middle; font-weight: bold\">".html_encode($result["community_title"])."</a>\n";
								echo "	<div style=\"margin-left: 16px\">\n";
								echo 		(($description) ? $description : "Community description not available.")."\n";
								echo "		<div style=\"white-space: nowrap; overflow: hidden\"><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\" style=\"color: green; font-size: 11px\" target=\"_blank\">".ENTRADA_URL."/community".$result["community_url"]."</a></div>\n";
								echo "	</div>\n";
								echo "</div>\n";
							}
							echo "</div>\n";
						} else {
							echo "<div class=\"display-notice\" style=\"margin-top: 20px; padding: 15px\">\n";
							echo "	<div style=\"font-side: 13px; font-weight: bold\">No Communities</div>\n";
							echo "	We have found no communities in this category. This is a great opportunity for you to <a href=\"".ENTRADA_URL."/communities?section=create&amp;category=".$CATEGORY_ID."\" style=\"color: #669900; font-weight: bold\">create a new community</a> to fill this niche!";
							echo "</div>\n";
						}
					break;
					case "search" :
						$BREADCRUMB[]	= array("url" => ENTRADA_URL."/".$MODULE, "title" => "Community Search");
						
						$SEARCH_QUERY	= "";

						/**
						 * The query that is actually be searched for.
						 */
						if((isset($_GET["q"])) && (trim($_GET["q"]))) {
							$SEARCH_QUERY = trim($_GET["q"]);

							if(strlen($SEARCH_QUERY) < 4) {
								$SEARCH_QUERY = str_pad($SEARCH_QUERY, 4, "*");
							}
						}

						if(!$SEARCH_QUERY) {
							header("Location: ".ENTRADA_URL."/communities");
							exit;
						}
						?>
						<h2>Search for a Community</h2>
						<div style="margin-left: 15px; margin-bottom: 15px">
							<form action="<?php echo ENTRADA_URL; ?>/communities" method="get">
							<input type="hidden" name="action" value="search" />
							<input type="text" id="q" name="q" value="<?php echo html_encode($SEARCH_QUERY); ?>" style="width: 255px" /> <input type="submit" class="button" value="Search" />
							<div class="content-small" style="margin-top: 3px">
								Example 1: <a href="<?php echo ENTRADA_URL; ?>/communities?action=search&amp;q=Sports+Club" class="content-small">Sports Club</a><br />
								Example 2: <a href="<?php echo ENTRADA_URL; ?>/communities?action=search&amp;q=Team+Technology" class="content-small">Team Technology</a><br />
							</div>
							</form>
						</div>
						<?php
						if($SEARCH_QUERY) {
							$query_counter	= "SELECT COUNT(*) AS `total_rows` FROM `communities` WHERE `community_active` = '1' AND MATCH (`community_title`, `community_description`, `community_keywords`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE)";
							$query_search	= "SELECT `community_id`, `category_id`, `community_url`, `community_shortname`, `community_title`, `community_description`, `community_keywords`, MATCH (`community_title`, `community_description`, `community_keywords`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE) AS `rank` FROM `communities` WHERE `community_active` = '1' AND MATCH (`community_title`, `community_description`, `community_keywords`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE) ORDER BY `rank` DESC, `community_title` ASC LIMIT %s, %s";

							/**
							 * Get the total number of results using the generated queries above and calculate the total number
							 * of pages that are available based on the results per page preferences.
							 */
							$result = ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query_counter) : $db->GetRow($query_counter));
							if($result) {
								$TOTAL_ROWS	= $result["total_rows"];

								if($TOTAL_ROWS <= $RESULTS_PER_PAGE) {
									$TOTAL_PAGES = 1;
								} elseif (($TOTAL_ROWS % $RESULTS_PER_PAGE) == 0) {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $RESULTS_PER_PAGE);
								} else {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $RESULTS_PER_PAGE) + 1;
								}
							} else {
								$TOTAL_ROWS	= 0;
								$TOTAL_PAGES	= 1;
							}

							/**
							 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
							 */
							if(isset($_GET["pv"])) {
								$PAGE_CURRENT = (int) trim($_GET["pv"]);

								if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
									$PAGE_CURRENT = 1;
								}
							} else {
								$PAGE_CURRENT = 1;
							}

							$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
							$PAGE_NEXT	= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

							if($TOTAL_PAGES > 1) {
								echo "<form action=\"".ENTRADA_URL."/communities\" method=\"get\" id=\"pageSelector\">\n";
								echo "<input type=\"hidden\" name=\"action\" value=\"search\" />\n";
								echo "<input type=\"hidden\" name=\"q\" value=\"".html_encode($SEARCH_QUERY)."\" />\n";
								echo "<div style=\"margin-top: 10px; margin-bottom: 5px; text-align: right; white-space: nowrap\">\n";
								echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
								if($PAGE_PREVIOUS) {
									echo "<a href=\"".ENTRADA_URL."/communities?".replace_query(array("pv" => $PAGE_PREVIOUS))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$PAGE_PREVIOUS.".\" title=\"Back to page ".$PAGE_PREVIOUS.".\" style=\"vertical-align: middle\" /></a>\n";
								} else {
									echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
								}
								echo "</span>";
								echo "<span style=\"vertical-align: middle\">\n";
								echo "<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/communities?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($TOTAL_PAGES <= 1) ? " disabled=\"disabled\"" : "").">\n";
								for($i = 1; $i <= $TOTAL_PAGES; $i++) {
									echo "<option value=\"".$i."\"".(($i == $PAGE_CURRENT) ? " selected=\"selected\"" : "").">".(($i == $PAGE_CURRENT) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
								}
								echo "</select>\n";
								echo "</span>\n";
								echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
								if($PAGE_CURRENT < $TOTAL_PAGES) {
									echo "<a href=\"".ENTRADA_URL."/communities?".replace_query(array("pv" => $PAGE_NEXT))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$PAGE_NEXT.".\" title=\"Forward to page ".$PAGE_NEXT.".\" style=\"vertical-align: middle\" /></a>";
								} else {
									echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
								}
								echo "</span>\n";
								echo "</div>\n";
								echo "</form>\n";
							}

							/**
							 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
							 */
							$limit_parameter = (int) (($RESULTS_PER_PAGE * $PAGE_CURRENT) - $RESULTS_PER_PAGE);
							$query		= sprintf($query_search, $limit_parameter, $RESULTS_PER_PAGE);
							$results	= $db->GetAll($query);
							if($results) {
								echo "<div class=\"searchTitle\">\n";
								echo "	<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
								echo "	<tbody>\n";
								echo "		<tr>\n";
								echo "			<td style=\"font-size: 14px; font-weight: bold; color: #003366\">Results:</td>\n";
								echo "			<td style=\"text-align: right; font-size: 10px; color: #666666; overflow: hidden; white-space: nowrap\">".$TOTAL_ROWS." Result".(($TOTAL_ROWS != 1) ? "s" : "")." Found. Results ".($limit_parameter + 1)." - ".((($RESULTS_PER_PAGE + $limit_parameter) <= $TOTAL_ROWS) ? ($RESULTS_PER_PAGE + $limit_parameter) : $TOTAL_ROWS)." for &quot;<strong>".html_encode($SEARCH_QUERY)."</strong>&quot; shown below.</td>\n";
								echo "		</tr>\n";
								echo "		</tbody>\n";
								echo "	</table>\n";
								echo "</div>";

								foreach($results as $result) {
									$category_title = "";

									if(($result["category_id"]) && ($category_details = communities_fetch_category($result["category_id"]))) {
										$category_title = $category_details["category_title"];
									}

									if($result["community_description"]) {
										$description = search_description($result["community_description"]);
									} else {
										$description = "";
									}

									echo "<div id=\"result-".$result["community_id"]."\" style=\"width: 100%; margin-bottom: 10px; line-height: 16px;\">\n";
									echo "	<img src=\"".ENTRADA_URL."/images/list-community.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle; margin-right: 5px\" /><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\" style=\"vertical-align: middle; font-weight: bold\">".html_encode($result["community_title"])."</a> <span style=\"color: #666666; font-size: 11px\">(".html_encode($category_title).")</span>\n";
									echo "	<div style=\"margin-left: 16px\">\n";
									echo 		(($description) ? $description : "Community description not available.")."\n";
									echo "		<div style=\"white-space: nowrap; overflow: hidden\"><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\" style=\"color: green; font-size: 11px\" target=\"_blank\">".ENTRADA_URL."/community".$result["community_url"]."</a></div>\n";
									echo "	</div>\n";
									echo "</div>\n";
								}
							} else {
								echo "<div class=\"display-notice\" style=\"margin-top: 20px; padding: 15px\">\n";
								echo "	<div style=\"font-side: 13px; font-weight: bold\">No Matching Communities</div>\n";
								echo "	We have found no communities matching your search query in the system. This is a great opportunity for you to <a href=\"".ENTRADA_URL."/communities?section=create\" style=\"color: #669900; font-weight: bold\">create a new community</a> to fill this niche!";
								echo "</div>\n";
							}
						}
					break;
					default :
						/**
						 * Default page action (show community information).
						 */
						?>
						<h2>Search for a Community</h2>
						<div style="margin-left: 15px">
							<form action="<?php echo ENTRADA_URL; ?>/communities" method="get">
							<input type="hidden" name="action" value="search" />
							<input type="text" id="q" name="q" value="<?php echo ((isset($_GET["q"])) ? html_encode(trim($_GET["q"])) : ""); ?>" style="width: 255px" /> <input type="submit" class="button" value="Search" />
							<div class="content-small" style="margin-top: 3px">
								Example 1: <a href="<?php echo ENTRADA_URL; ?>/communities?action=search&amp;q=Sports+Club" class="content-small">Sports Club</a><br />
								Example 2: <a href="<?php echo ENTRADA_URL; ?>/communities?action=search&amp;q=Team+Technology" class="content-small">Team Technology</a><br />
							</div>
							</form>
						</div>
						<h2>Browse Communities</h2>
						<div style="margin-left: 0px">
						<?php
						$query	= "
								SELECT *
								FROM `communities_categories`
								WHERE `category_parent` = '0'
									AND `category_visible` = '1'
								ORDER BY `category_title` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							?>
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
							<colgroup>
								<col style="width: 50%" />
								<col style="width: 50%" />
							</colgroup>
							<tbody>
							<?php
							foreach($results as $result) {
								echo "<tr>\n";
								echo "	<td colspan=\"2\"><div class=\"strong-green\"><img src=\"".ENTRADA_URL."/images/btn_attention.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" /> ".html_encode($result["category_title"])."</div></td>\n";
								echo "</tr>\n";
								$query	= "
										SELECT *
										FROM `communities_categories`
										WHERE `category_parent` = ".$db->qstr($result["category_id"])."
											AND `category_visible` = '1'
										ORDER BY `category_title` ASC";
								$sresults	= $db->GetAlL($query);
								if($sresults) {
									echo "<tr>\n";

									$total_sresults	= @count($sresults);
									$count			= 0;
									$column			= 0;
									$max_columns		= 2;
									foreach($sresults as $sresult) {
										$count++;
										$column++;
										$communities = communities_count($sresult["category_id"]);
										echo "<td style=\"padding: 2px 2px 2px 19px\">";
										echo "	<a href=\"".ENTRADA_URL."/communities?".replace_query(array("action" => "browse", "category" => $sresult["category_id"]))."\" style=\"font-size: 13px; color: #006699\">".html_encode($sresult["category_title"])."</a> <span style=\"font-style: oblique\" class=\"content-small\">(".$communities.")</span>";
										echo "</td>\n";

										if(($count == $total_sresults) && ($column < $max_columns)) {
											for($i = 0; $i < ($max_columns - $column); $i++) {
												echo "<td>&nbsp;</td>\n";
											}
										}

										if(($count == $total_sresults) || ($column == $max_columns)) {
											$column = 0;
											echo "</tr>\n";

											if($count < $total_sresults) {
												echo "<tr>\n";
											}
										}
									}
									echo "<tr>\n";
									echo "	<td colspan=\"2\">&nbsp;</td>\n";
									echo "</tr>\n";
								}
							}
							?>
							</tbody>
							</table>
							<?php
						} else {
							$ERROR++;
							$ERRORSTR[] = "There does no seem to be any Community Categories in the database right now.<br /><br />The MEdTech Unit has been notified of this problem, please try again later. We apologize for any inconvenience this has caused.";

							echo display_error();

							application_log("error", "No community categories in the database. Database said: ".$db->ErrorMsg());
						}
					break;
				}
				?>
			</div>
		</td>
	</tr>
	</tbody>
	</table>
</div>