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
 * Module:	Curriculum Search
 * Area:		Public
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: search.inc.php 1171 2010-05-01 14:39:27Z ad29 $
 */
error_reporting(E_ALL);
if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("search", "read")) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();
	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
/**
 * Meta information for this page.
 */
	$PAGE_META["title"]			= "Curriculum Search";
	$PAGE_META["description"]	= "Allowing you to search the curriculum for specific key words and events.";
	$PAGE_META["keywords"]		= "";

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/search", "title" => "Curriculum Search");

	$SEARCH_QUERY				= "";
	$SEARCH_MODE				= "standard";
	$SEARCH_CLASS				= 0;
	$SEARCH_YEAR				= 0;
	$SEARCH_DURATION			= array();
	$SEARCH_ORGANISATION		= $ENTRADA_USER->getActiveOrganisation();
	$RESULTS_PER_PAGE			= 10;

	/**
	 * The query that is actually be searched for.
	 */
	if ((isset($_GET["q"])) && ($tmp_input = clean_input($_GET["q"]))) {
		$SEARCH_QUERY	= $tmp_input;

		if (strlen($SEARCH_QUERY) < 4) {
			$SEARCH_QUERY = str_pad($SEARCH_QUERY, 4, "*");
		}
	}

	/**
	 * The mode that results are displayed in.
	 */
	if ((isset($_GET["m"])) && (trim($_GET["m"]) == "timeline")) {
		$SEARCH_MODE = "timeline";
	}

	if ($SEARCH_QUERY) {
	/**
	 * Check if c variable is set for Class of.
	 */
		if ((isset($_GET["c"])) && ($tmp_input = clean_input($_GET["c"], array("nows", "int")))) {
			$SEARCH_CLASS = $tmp_input;
		}
		/**
		 * Check if o variable is set for Organisation
		 */
		if (isset($_GET["o"])) {
			if ($_GET["o"] == 'all') {
				$SEARCH_ORGANISATION = 'all';
			} else if ($tmp_input = clean_input($_GET["o"], array("nows", "int"))) {
				$SEARCH_ORGANISATION = $tmp_input;
			}
		}
		/**
		 * Check if y variable is set for Academic year.
		 */
		if ((isset($_GET["y"])) && ($tmp_input = clean_input($_GET["y"], array("nows", "int")))) {
			$SEARCH_YEAR				= $tmp_input;

			$SEARCH_DURATION["start"]	= mktime(0, 0, 0, 9, 1, $SEARCH_YEAR);
			$SEARCH_DURATION["end"]		= strtotime("+1 year", $SEARCH_DURATION["start"]);
		}

		if ($SEARCH_MODE == "standard") {

            $index = Zend_Search_Lucene::open(SEARCH_INDEX_PATH.'/events');
            $query = Zend_Search_Lucene_Search_QueryParser::parse($SEARCH_QUERY);


//            $query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term('cohort', 'audience_type')), true);
            if ($SEARCH_CLASS) {
                $query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term((int) $SEARCH_CLASS, 'audience_value')), true);
            }
            if ($SEARCH_ORGANISATION && $SEARCH_ORGANISATION != 'all') {
                $query->addSubquery(new Zend_Search_Lucene_Search_Query_Term(new Zend_Search_Lucene_Index_Term($SEARCH_ORGANISATION, 'organisation_id')), true);
            }
            if ($SEARCH_YEAR) {
                $query->addSubquery(new Zend_Search_Lucene_Search_Query_Range(
                    new Zend_Search_Lucene_Index_Term($SEARCH_DURATION["start"], 'event_start'),
                    new Zend_Search_Lucene_Index_Term($SEARCH_DURATION["end"], 'event_start'),
                    true
                ), true);
            }


            $result = $index->find($query);

            $hits = array();
            foreach ($result as $hit) {
                $hits[]= $hit->event_id;
            }

            $TOTAL_ROWS = count($result);

//			$query_counter = "	SELECT COUNT(DISTINCT(a.`event_id`)) AS `total_rows`
//								FROM `events` AS a
//								LEFT JOIN `event_audience` AS b
//								ON b.`event_id` = a.`event_id`
//								LEFT JOIN `courses` AS c
//								ON a.`course_id` = c.`course_id`
//								WHERE (a.`parent_id` IS NULL OR a.`parent_id` = '0')
//								AND".(($SEARCH_CLASS) ? " b.`audience_type` = 'cohort' AND b.`audience_value` = ".$db->qstr((int) $SEARCH_CLASS)." AND" : "").
//								(($SEARCH_ORGANISATION) && $SEARCH_ORGANISATION != 'all' ? " c.`organisation_id` = ".$db->qstr((int) $SEARCH_ORGANISATION)." AND" : "").
//								(($SEARCH_YEAR) ? " (`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).") AND" : "")."
//								MATCH (`event_title`, `event_description`, `event_goals`, `event_objectives`, `event_message`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE)";

			$query_search = "	SELECT a.*, b.`audience_type`, b.`audience_value` AS `event_cohort`, MATCH (`event_title`, `event_description`, `event_goals`, `event_objectives`, `event_message`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE) AS `rank`
								FROM `events` AS a
								LEFT JOIN `event_audience` AS b
								ON b.`event_id` = a.`event_id`
								LEFT JOIN `courses` AS c
								ON a.`course_id` = c.`course_id`
								WHERE (a.`parent_id` IS NULL OR a.`parent_id` = '0')
								AND".(($SEARCH_CLASS) ? " b.`audience_type` = 'cohort' AND b.`audience_value` = ".$db->qstr((int) $SEARCH_CLASS)." AND" : "").
								(($SEARCH_ORGANISATION) && $SEARCH_ORGANISATION != 'all' ? " c.`organisation_id` = ".$db->qstr((int) $SEARCH_ORGANISATION)." AND" : "").
								(($SEARCH_YEAR) ? " (`event_start` BETWEEN ".$db->qstr($SEARCH_DURATION["start"])." AND ".$db->qstr($SEARCH_DURATION["end"]).") AND" : "")."
								MATCH (`event_title`, `event_description`, `event_goals`, `event_objectives`, `event_message`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE)
								GROUP BY a.`event_id`
								ORDER BY `rank` DESC, `event_start` DESC
								LIMIT %s, %s";

            $query_search = "	SELECT a.*, b.`audience_type`, b.`audience_value` AS `event_cohort`, MATCH (`event_title`, `event_description`, `event_goals`, `event_objectives`, `event_message`) AGAINST (".$db->qstr(str_replace(array("%", " AND ", " NOT "), array("%%", " +", " -"), $SEARCH_QUERY))." IN BOOLEAN MODE) AS `rank`
								FROM `events` AS a
								LEFT JOIN `event_audience` AS b
								ON b.`event_id` = a.`event_id`
								LEFT JOIN `courses` AS c
								ON a.`course_id` = c.`course_id`
								WHERE a.event_id IN (".implode(',', $hits).")
								LIMIT %s, %s";


			/**
			 * Get the total number of results using the generated queries above and calculate the total number
			 * of pages that are available based on the results per page preferences.
			 */
			//$result = ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query_counter) : $db->GetRow($query_counter));
			if ($result) {
			//	$TOTAL_ROWS	= $result["total_rows"];

				if ($TOTAL_ROWS <= $RESULTS_PER_PAGE) {
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
			if (isset($_GET["pv"])) {
				$PAGE_CURRENT = (int) trim($_GET["pv"]);

				if (($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
					$PAGE_CURRENT = 1;
				}
			} else {
				$PAGE_CURRENT = 1;
			}

			$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
			$PAGE_NEXT	= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);
		}
	}
	?>
	<h1>Curriculum Search</h1>
	<form action="<?php echo ENTRADA_URL; ?>/search" method="get">
		<?php
		if ($SEARCH_MODE == "timeline") {
			echo "<input type=\"hidden\" name=\"m\" value=\"timeline\" />\n";
		}
		?>
		<table style="width: 100%" cellspacing="1" cellpadding="1" border="0">
			<colgroup>
				<col style=" width: 20%" />
				<col style=" width: 45%" />
				<col style=" width: 35%" />
			</colgroup>
			<tbody>
				<tr>
					<td><label for="q" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Boolean Search Term:</label></td>
					<td colspan="2"><input type="text" id="q" name="q" value="<?php echo html_encode($SEARCH_QUERY); ?>" style="width: 350px" /> <input type="submit" class="button" value="Search" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2" class="content-small">
						Example 1: <a href="<?php echo ENTRADA_URL."/search?".replace_query(array("q" => "asthma")); ?>" class="content-small">asthma</a><br />
						Example 2: <a href="<?php echo ENTRADA_URL."/search?".replace_query(array("q" => "pain+AND+palliative")); ?>" class="content-small">pain AND palliative</a><br />
						Example 3: <a href="<?php echo ENTRADA_URL."/search?".replace_query(array("q" => "%22heart+disease%22+NOT+pediatric")); ?>" class="content-small">"heart disease" NOT pediatric</a>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>
						<label for="c" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Graduating Class:</label>
					</td>
					<td>
						<select id="c" name="c" style="width: 250px">
							<option value="0"<?php echo ((!$SEARCH_CLASS) ? " selected=\"selected\"" : ""); ?>>-- All Cohorts --</option>
								<?php
								$cohorts = groups_get_all_cohorts($ENTRADA_USER->getActiveOrganisation());
								foreach ($cohorts as $cohort) {
									echo "<option value=\"".$cohort["group_id"]."\"".(($SEARCH_CLASS == $cohort["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
								}
								?>
						</select>
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						<label for="y" style="font-weight: bold; margin-right: 5px; white-space: nowrap">Academic Year:</label>
					</td>
					<td>
						<select id="y" name="y" style="width: 250px" <?php echo (($SEARCH_MODE == "timeline") ? " disabled=\"disabled\"" : ""); ?>>
							<option value="0"<?php echo ((!$SEARCH_YEAR)? " selected=\"selected\"" : ""); ?>>-- All Years --</option>
							<?php
							$start_year = (fetch_first_year() - 3);
							for ($year = $start_year; $year >= ($start_year - 3); $year--) {
								echo "<option value=\"".$year."\"".(($SEARCH_YEAR == $year) ? " selected=\"selected\"" : "").">".$year."/".($year + 1)."</option>\n";
							}
							?>
						</select>
					</td>
					<td style="text-align: right">
						<span style="width: 100px; height: 23px"><a href="<?php echo ENTRADA_URL; ?>/search?<?php echo replace_query(array("m" =>  "text")); ?>"><img src="<?php echo ENTRADA_URL; ?>/images/search-mode-text-<?php echo (($SEARCH_MODE != "timeline") ? "on" : "off"); ?>.gif" width="100" height="23" alt="" title="" border="0" /></a></span><span style="width: 100px; height: 23px"><a href="<?php echo ENTRADA_URL; ?>/search?<?php echo replace_query(array("m" =>  "timeline")); ?>"><img src="<?php echo ENTRADA_URL; ?>/images/search-mode-timeline-<?php echo (($SEARCH_MODE == "timeline") ? "on" : "off"); ?>.gif" widtgh="100" height="23" alt="" title="" border="0" /></a></span>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php
	if ($SEARCH_QUERY) {
		switch ($SEARCH_MODE) {
			case "timeline" :
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/timeline/timeline-api.js\"></script>\n";
				$ONLOAD[] = "loadTimeline()";
				?>
				<script type="text/javascript">
				var tl = new Array();
				var gradYears = new Array();
				var gradYearIds = new Array();
				<?php
				if ($SEARCH_CLASS) {
					echo "gradYears[0] = '".preg_replace("/[^0-9]/", "", groups_get_name($SEARCH_CLASS))."';\n\n";
					echo "gradYearIds[".preg_replace("/[^0-9]/", "", groups_get_name($SEARCH_CLASS))."] = '".$SEARCH_CLASS."';\n\n";
				} else {
					$cohorts_list = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
					$i = 0;
					foreach ($cohorts_list as $cohort) {
						echo "gradYears[".$i."] = '".preg_replace("/[^0-9]/", "", $cohort["group_name"])."';\n";
						echo "gradYearIds[".preg_replace("/[^0-9]/", "", $cohort["group_name"])."] = '".$cohort["group_id"]."';\n";
						$i++;
					}
				}
				?>

				function showYear(yearNumber) {
					if ((yearNumber < 1 ) || (yearNumber > 4)) {
						yearNumber = 1;
					}

					gradYears.each(function(gradClass) {
						startYear = (gradClass.match(/[\d\.]+/g) - (4 - yearNumber));

						tl[gradClass].getBand(0).setCenterVisibleDate(Timeline.DateTime.parseGregorianDateTime('Jan 25 ' + startYear + ' 00:00:00 GMT<?php echo date("O"); ?>'));
					});

					return;
				}

				function loadTotalsSidebar() {
					var class_totals = $('class-result-totals');

					if (class_totals != null) {
						var list_menu = document.createElement('ul');
						list_menu.setAttribute('class', 'menu');

						gradYears.each(function(gradClass) {
							var list_item = document.createElement('li');
							list_item.setAttribute('class', 'item');

							var year_totals	= document.createElement('div');
							year_totals.setAttribute('id', gradClass + '-event-count');

							var class_title	= document.createTextNode('Class of ' + gradClass + ': ');

							year_totals.appendChild(class_title);
							list_item.appendChild(year_totals);
							list_menu.appendChild(list_item);
						});

						class_totals.appendChild(list_menu);
					}

					return;
				}

				function loadTimeline() {
					if (gradYears.length > 0) {
						loadTotalsSidebar();
						gradYears.each(function(gradClass) {
							loadClass(gradClass);
						});
					} else {
						alert('There are no classes specified which can be searched.');
					}

					return;
				}

				function loadClass(gradClass) {
					gradYear = gradClass.match(/[\d\.]+/g);

					var eventSource = new Timeline.DefaultEventSource(0);
					var theme = Timeline.ClassicTheme.create();
					theme.event.bubble.width = 220;
					theme.event.bubble.height = 120;
					theme.event.track.height = 1.1;
					var zones = [
						{	start:    'Sept 1 ' + (gradYear - 4) + ' 00:00:00 GMT<?php echo date("O"); ?>',
							end:      'Apr 30 ' + gradYear + ' 00:00:00 GMT<?php echo date("O"); ?>',
							magnify:  4,
							unit:     Timeline.DateTime.MONTH
						}
					];

					var bandInfos = [
						Timeline.createHotZoneBandInfo({
							width:          '100%',
							intervalUnit:   Timeline.DateTime.YEAR,
							intervalPixels: 175,
							zones:          zones,
							eventSource:    eventSource,
							date:           Timeline.DateTime.parseGregorianDateTime('Jan 15 ' + (gradYear - 3) + ' 00:00:00 GMT<?php echo date("O"); ?>'),
							theme:          theme
						})
					];

					bandInfos[0].decorators = [
						new Timeline.SpanHighlightDecorator({
							startDate:  'Sept 1 ' + (gradYear - 4) + ' 00:00:00 GMT<?php echo date("O"); ?>',
							endDate:    'Apr 30 ' + gradYear + ' 00:00:00 GMT<?php echo date("O"); ?>',
							color:      ((gradYear % 2) ? '#003366' : '#336699'),
							opacity:    50,
							startLabel: 'Sept 01 ' + (gradYear - 4),
							endLabel:   'Apr 30 ' + gradYear,
							theme:      theme
						})
					];

					tl[gradClass] = Timeline.create($('search-timeline-' + gradClass), bandInfos, Timeline.HORIZONTAL);
					tl[gradClass].loadXML('<?php echo ENTRADA_RELATIVE; ?>/api/timeline.api.php?q=<?php echo rawurlencode($SEARCH_QUERY); ?>&c=' + gradYearIds[gradClass], function(xml, url) {
						eventSource.loadXML(xml, url);
						if ($(gradClass + '-event-count') != null) {
							$(gradClass + '-event-count').innerHTML += eventSource.getCount();
						}
					});
				}
				</script>

				<h2>Plotted Timeline</h2>

				<div style="text-align: right">
					<a href="javascript: showYear(1)">1st Year</a> |
					<a href="javascript: showYear(2)">2nd Year</a> |
					<a href="javascript: showYear(3)">3rd Year</a> |
					<a href="javascript: showYear(4)">4th Year</a>
				</div>

				<?php
				if ($SEARCH_CLASS) {
					echo "<div style=\"border: 1px #CCCCCC solid; margin-bottom: 1px\">\n";
					echo "	<img src=\"".ENTRADA_URL."/images/dynamic/14/314/5/90/".rawurlencode(groups_get_name($SEARCH_CLASS))."/jpg\" width=\"25\" height=\"325\" align=\"left\" alt=\"".html_encode(groups_get_name($SEARCH_CLASS))."\" title=\"".html_encode(groups_get_name($SEARCH_CLASS))."\" />\n";
					echo "	<div id=\"search-timeline-".preg_replace("/[^0-9]/", "", groups_get_name($SEARCH_CLASS))."\" style=\"height: 325px\"></div>\n";
					echo "</div>\n";
				} else {
					$cohorts_list = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
					foreach ($cohorts_list as $cohort) {
						echo "<div style=\"border: 1px #CCCCCC solid; margin-bottom: 1px\">\n";
						echo "	<img src=\"".ENTRADA_URL."/images/dynamic/14/314/5/90/".rawurlencode($cohort["group_name"])."/jpg\" width=\"25\" height=\"325\" align=\"left\" alt=\"".html_encode($cohort["group_name"])."\" title=\"".html_encode($cohort["group_name"])."\" />\n";
						echo "	<div id=\"search-timeline-".preg_replace("/[^0-9]/", "", $cohort["group_name"])."\" style=\"height: 325px\"></div>\n";
						echo "</div>\n";
					}
				}

				new_sidebar_item("Class Result Totals", "<div id=\"class-result-totals\"></div>", "result-totals", "open");
			break;
			case "standard" :
			default :
				if (($SEARCH_MODE != "timeline") && ($TOTAL_PAGES > 1)) {
					echo "<form action=\"".ENTRADA_URL."/search\" method=\"get\" id=\"pageSelector\">\n";
					echo "<div style=\"margin-top: 10px; margin-bottom: 5px; text-align: right; white-space: nowrap\">\n";
					echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
					if ($PAGE_PREVIOUS) {
						echo "<a href=\"".ENTRADA_URL."/search?".replace_query(array("pv" => $PAGE_PREVIOUS))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$PAGE_PREVIOUS.".\" title=\"Back to page ".$PAGE_PREVIOUS.".\" style=\"vertical-align: middle\" /></a>\n";
					} else {
						echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
					}
					echo "</span>";
					echo "<span style=\"vertical-align: middle\">\n";
					echo "<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/search?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($TOTAL_PAGES <= 1) ? " disabled=\"disabled\"" : "").">\n";
					for($i = 1; $i <= $TOTAL_PAGES; $i++) {
						echo "<option value=\"".$i."\"".(($i == $PAGE_CURRENT) ? " selected=\"selected\"" : "").">".(($i == $PAGE_CURRENT) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
					}
					echo "</select>\n";
					echo "</span>\n";
					echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
					if ($PAGE_CURRENT < $TOTAL_PAGES) {
						echo "<a href=\"".ENTRADA_URL."/search?".replace_query(array("pv" => $PAGE_NEXT))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$PAGE_NEXT.".\" title=\"Forward to page ".$PAGE_NEXT.".\" style=\"vertical-align: middle\" /></a>";
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
				$query = sprintf($query_search, $limit_parameter, $RESULTS_PER_PAGE);
				$results = $db->GetAll($query);
				if ($results) {
					echo "<div class=\"searchTitle\">\n";
					echo "	<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
					echo "	<tbody>\n";
					echo "		<tr>\n";
					echo "			<td style=\"font-size: 14px; font-weight: bold; color: #003366\">Search Results:</td>\n";
					echo "			<td style=\"text-align: right; font-size: 10px; color: #666666; overflow: hidden; white-space: nowrap\">".$TOTAL_ROWS." Result".(($TOTAL_ROWS != 1) ? "s" : "")." Found. Results ".($limit_parameter + 1)." - ".((($RESULTS_PER_PAGE + $limit_parameter) <= $TOTAL_ROWS) ? ($RESULTS_PER_PAGE + $limit_parameter) : $TOTAL_ROWS)." for &quot;<strong>".html_encode($SEARCH_QUERY)."</strong>&quot; shown below.</td>\n";
					echo "		</tr>\n";
					echo "	</tbody>\n";
					echo "	</table>\n";
					echo "</div>";

					foreach ($results as $result) {
						$description = search_description($result["event_objectives"]." ".$result["event_goals"]);

						echo "<div id=\"result-".$result["event_id"]."\" style=\"width: 100%; margin-bottom: 10px; line-height: 16px;\">\n";
						echo "	<a href=\"".ENTRADA_URL."/events?id=".$result["event_id"]."\" style=\"font-weight: bold\">".html_encode($result["event_title"])."</a> <span class=\"content-small\">Event on ".date(DEFAULT_DATE_FORMAT, $result["event_start"])."; ".(($result["audience_type"] == "cohort") ? html_encode(groups_get_name($result["event_cohort"])) : "Group Activity")."</span><br />\n";
						echo 	(($description) ? $description : "Description not available.")."\n";
						echo "	<div style=\"white-space: nowrap; overflow: hidden\"><a href=\"".ENTRADA_URL."/events?id=".$result["event_id"]."\" style=\"color: green; font-size: 11px\" target=\"_blank\">".ENTRADA_URL."/events?id=".$result["event_id"]."</a></div>\n";
						echo "</div>\n";
					}
				} else {
					if (strlen($SEARCH_QUERY) > 3) {
						echo "<div class=\"display-notice\" style=\"margin-top: 20px; padding: 15px\">\n";
						echo "	<div style=\"font-side: 13px; font-weight: bold\">No Matching Teaching Events</div>\n";
						echo "	There are no teaching events found which contain matches to &quot;<strong>".html_encode($SEARCH_QUERY)."</strong>&quot;.";
						if (($SEARCH_CLASS) || ($SEARCH_YEAR) || ($SEARCH_ORGANISATION)) {
							echo "<br /><br />\n";
							echo "You may wish to try modifying or removing the Graduating Class, Academic Year, or Organisation limiters.\n";
						}
						echo "</div>\n";
					} else {
						echo "<div class=\"display-error\" style=\"margin-top: 20px; padding: 15px\">\n";
						echo "	<div style=\"font-side: 13px; font-weight: bold\">Invalid Search Term</div>\n";
						echo "	The search term which you have provided &quot;<strong>".html_encode($SEARCH_QUERY)."</strong>&quot; must be at least 4 characters long in order to perform an accurate search.";
						echo "</div>\n";
					}
				}
			break;
		}
	}
}
?>