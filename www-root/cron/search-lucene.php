<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for adding users to the google mail-list.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
*/
error_reporting(E_ALL);
@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));
require_once "Entrada/Search/Lucene/Document.php";
require_once "Entrada/Search/Lucene/Document/Pdf.php";
/**
 * Include the Entrada init cod
 */
require_once("init.inc.php");


try {
    $input = new Zend_Console_Getopt(
        array(
            'action|a=w' => 'Action',
            'index|i=w'  => 'Index',
            'path|p-w'   => 'Path to indexes with trailing slash. Default path will be used if not set',
            'term|t-s'   => 'Search query for "search" action'
        )
    );

    $input->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
}

if (!isset($input->path)) {
    $path = SEARCH_INDEX_PATH;
}
//die($path);
//$resolver = new Entrada_Search_Lucene_DocumentResolver();

switch ($input->action) {
    case 'create':
        $index = Zend_Search_Lucene::create($path.'/'.$input->index);
        break;

    case 'reindex':
        $index = Zend_Search_Lucene::open($path.'/'.$input->index);

        $query = "SELECT a.*, b.`audience_type`, b.`audience_value` AS `event_cohort`, c.`organisation_id`
								FROM `events` AS a
								LEFT JOIN `event_audience` AS b
								ON b.`event_id` = a.`event_id`
								LEFT JOIN `courses` AS c
								ON a.`course_id` = c.`course_id`
								WHERE (a.`parent_id` IS NULL OR a.`parent_id` = '0')
								";
        //AND (a.event_id = 22044 OR a.event_id = 22456 OR a.event_id = 22897)

        $results = $db->GetAll($query);
        foreach ($results as $result) {
            $hits = $index->find('event_id:'.$result['event_id']);
            foreach ($hits as $hit) {
                $index->delete($hit->id);
            }

            $query	= "	SELECT a.*, MAX(b.`timestamp`) AS `last_visited`
						FROM `event_files` AS a
						LEFT JOIN `statistics` AS b
						ON b.`module` = 'events'
						AND b.`action` = 'file_download'
						AND b.`action_field` = 'file_id'
						AND b.`action_value` = a.`efile_id`
						WHERE a.`event_id` = ".$db->qstr($result['event_id']);

            $event_files = $db->GetAll($query);
            //var_dump($event_files);die();

            $filesBody = null;
            foreach ($event_files as $file) {
                if (is_null($file['file_name'])) {
                    continue;
                }
                $path = pathinfo($file['file_name']);
                $document = Entrada_Search_Lucene_Document::factory(FILE_STORAGE_PATH."/".$file['efile_id'], $path['extension']);
                if (!is_null($document)) {
                    $filesBody .= ' '.$document->body;
                }
            }

            $document = new Zend_Search_Lucene_Document();
            $document->addField(Zend_Search_Lucene_Field::text('title', $result['event_title']));
            $document->addField(Zend_Search_Lucene_Field::keyword('event_id', $result['event_id']));
            $document->addField(Zend_Search_Lucene_Field::unStored('description', $result['event_description']));
            $document->addField(Zend_Search_Lucene_Field::unStored('goals', $result['event_goals']));
            $document->addField(Zend_Search_Lucene_Field::unStored('objectives', $result['event_objectives']));
            $document->addField(Zend_Search_Lucene_Field::unStored('message', $result['event_message']));
            $document->addField(Zend_Search_Lucene_Field::keyword('audience_type', $result['audience_type']));
            $document->addField(Zend_Search_Lucene_Field::keyword('audience_value', $result['event_cohort']));
            $document->addField(Zend_Search_Lucene_Field::keyword('event_start', $result['event_start']));
            $document->addField(Zend_Search_Lucene_Field::unStored('files_body', $filesBody));
            $document->addField(Zend_Search_Lucene_Field::keyword('organisation_id', $result['organisation_id']));
            $index->addDocument($document);
        }
        break;

    case 'optimize':
        $index = Zend_Search_Lucene::open($path.'/'.$input->index);
        $index->optimize();
        break;

    case 'search':
        $index = Zend_Search_Lucene::open($path.'/'.$input->index);

        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse($input->term);

        $results = $index->find($userQuery);
        $textTable = new Zend_Text_Table(array('columnWidths' => array(12, 12, 5, 45)));
        $textTable->appendRow(array('Document ID', 'Database ID', 'Score', 'Title'));

        foreach ($results as $hit) {
            $textTable->appendRow(array((string)$hit->id, (string)$hit->event_id, (string)round($hit->score,2), $hit->title));
        }
        echo $textTable;
        break;

    case 'status':
        $index = Zend_Search_Lucene::open($path.'/'.$input->index);

        $textTable = new Zend_Text_Table(array('columnWidths' => array(30, 10)));

        $textTable->appendRow(array('Documents Count', (string)$index->count()));
        $textTable->appendRow(array('Non-deleted Documents Count', (string)$index->numDocs()));

        echo $textTable;
        break;
}
	
?>