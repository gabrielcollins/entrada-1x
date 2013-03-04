<?php
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if((isset($_GET["id"])) && ($proxy_id = clean_input($_GET["id"], array("int")))) {
	$startDate = date("Y") - 5;
	$endDate = date("Y") + 1;
	
	$query = "SELECT * FROM `ar_peer_reviewed_papers`, `global_lu_roles`, `ar_lu_publication_type`
    WHERE `ar_peer_reviewed_papers`.`proxy_id` = '$proxy_id'
    AND `ar_peer_reviewed_papers`.`type_id` = `ar_lu_publication_type`.`type_id`
    AND `ar_peer_reviewed_papers`.`role_id` = `global_lu_roles`.`role_id`
    AND `ar_peer_reviewed_papers`.`year_reported` BETWEEN '$startDate' AND '$endDate'
    AND `status` = \"Published\"
    ORDER BY `status` ASC";
    $results = $db->GetAll($query);
	if($results){
		foreach($results as $result)
		{
			$formattedRec	= "";

			if($formattedRec == "") {
				if($result["author_list"] != "") {
					$formattedRec = html_encode($result["author_list"]) . ", ";
				}

				if($result["title"] != "") {
					$formattedRec = $formattedRec . html_encode($result["title"])  . ", ";
				}

				if(isset($result["status_date"]) && strlen($result["status_date"]) == 5) {
					$month 	= substr($result["status_date"], 0, 1);
					$year 	= substr($result["status_date"], 1, 4);
					$formattedRec = $formattedRec . $month . "-" . $year . ", ";
				} else if(isset($result["status_date"]) && strlen($result["status_date"]) == 6) {
					$month 	= substr($result["status_date"], 0, 2);
					$year 	= substr($result["status_date"], 2, 4);
					$formattedRec = $formattedRec . $month . "-" . $year . ", ";
				} else if(isset($result["epub_date"]) && strlen($result["epub_date"]) == 5) {
					$month 	= substr($result["epub_date"], 0, 1);
					if($month == 0) {
						$month = 1;
					}
					$year 	= substr($result["epub_date"], 1, 4);
					$formattedRec = $formattedRec . $month . "-" . $year . " (e-pub), ";
				} else if(isset($result["epub_date"]) && strlen($result["epub_date"]) == 6) {
					$month 	= substr($result["epub_date"], 0, 2);
					if($month == 0) {
						$month = 1;
					}
					$year 	= substr($result["epub_date"], 2, 4);
					$formattedRec = $formattedRec . $month . "-" . $year . " (e-pub), ";
				}

				if($result["source"] != "") {
					$formattedRec = $formattedRec . html_encode($result["source"]) . ", ";
				}

				if(isset($result["editor_list"])) {
					$formattedRec . "Ed. " . html_encode($result["editor_list"]) . ", ";
				}

				if($result["volume"] != "" && $result["edition"] != "") {
					$formattedRec = $formattedRec . "Vol. " . html_encode($result["volume"]) . "(". html_encode($result["edition"]) . "):";
				} else if($result["volume"] != "" && $result["edition"] == "") {
					$formattedRec = $formattedRec . "Vol. " . html_encode($result["volume"]) . ", ";
				} else if($result["volume"] == "" && $result["edition"] != "") {
					$formattedRec = $formattedRec . html_encode($result["edition"]) . ":";
				}

				if($result["pages"] != "") {
					$formattedRec = $formattedRec . html_encode($result["pages"]);
				}

				// Check for existance of extra comma or colon at the end of the record
				// if there is one remove it
				$lengthOfRec = strlen($formattedRec) - 2;
				$lastChar = substr($formattedRec, $lengthOfRec, 1);
				if($lastChar == "," || $lastChar == ":") {
					$formattedRec = substr($formattedRec, 0, $lengthOfRec);
				}

				$formattedRec .=  " <b> - " . $result["role_description"] . " (" . $result["type_description"] . ")</b>";
			}
			echo $formattedRec."<br>";
		}
	}
}
?>
