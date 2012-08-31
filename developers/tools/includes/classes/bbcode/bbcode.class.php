<?php
/**
 * Communities System (OCR Module)
 *
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@queensu.ca>
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2007 Queen"s University, MEdTech Unit
 *
 * $Id: bbcode.class.php 1 2008-07-11 20:11:41Z simpson $
*/

require_once(dirname(__FILE__)."/stringparser_bbcode.class.php");

// Unify line breaks of different operating systems
if(!function_exists("convertlinebreaks")) {
	function convertlinebreaks ($text) {
	    return preg_replace ("/\015\012|\015|\012/", "\n", $text);
	}
}

// Remove everything but the newline charachter
if(!function_exists("bbcode_stripcontents")) {
	function bbcode_stripcontents($text) {
	    return preg_replace ("/[^\n]/", "", $text);
	}
}

if(!function_exists("do_bbcode_url")) {
	function do_bbcode_url($action, $attributes, $content, $params, $node_object) {
	    if($action == "validate") {
	        return true;
	    }
	    if(!isset ($attributes["default"])) {
	        return "<a href=\"".html_encode($content)."\">".html_encode($content)."</a>";
	    }
	    return "<a href=\"".html_encode($attributes["default"])."\">".$content."</a>";
	}
}

if(!function_exists("do_bbcode_color")) {
	function do_bbcode_color($action, $attributes, $content, $params, $node_object) {
	    if($action == "validate") {
	        return true;
	    }
	    if(!isset ($attributes["default"])) {
	        return "<span>".html_encode($content)."</span>";
	    }
	    return "<span style=\"color: ".html_encode($attributes["default"])."\">".$content."</span>";
	}
}


// Function to include images
if(!function_exists("do_bbcode_img")) {
	function do_bbcode_img($action, $attributes, $content, $params, $node_object) {
	    if($action == "validate") {
	        return true;
	    }
	    return "<img src=\"".html_encode($content)."\" alt=\"\" title=\"\" />";
	}
}

$bbcode = new StringParser_BBCode();
$bbcode->addFilter(STRINGPARSER_FILTER_PRE, "convertlinebreaks");
$bbcode->addParser(array("block", "inline", "link", "listitem"), "htmlspecialchars");
$bbcode->addParser(array("block", "inline", "link", "listitem"), "nl2br");
$bbcode->addParser("list", "bbcode_stripcontents");
$bbcode->addCode("quote", "simple_replace", null, array("start_tag" => "<span class=\"quoteStyle\">", "end_tag" => "</span>"), "inline", array("block", "inline"), array());
$bbcode->addCode("code", "simple_replace", null, array("start_tag" => "<span class=\"codeStyle\">", "end_tag" => "</span>"), "inline", array("block", "inline"), array());
$bbcode->addCode("u", "simple_replace", null, array("start_tag" => "<span style=\"text-decoration: underline\">", "end_tag" => "</span>"), "inline", array("listitem", "block", "inline", "link"), array());
$bbcode->addCode("b", "simple_replace", null, array("start_tag" => "<span style=\"font-weight: bold\">", "end_tag" => "</span>"), "inline", array("listitem", "block", "inline", "link"), array());
$bbcode->addCode("i", "simple_replace", null, array("start_tag" => "<span style=\"font-style: oblique\">", "end_tag" => "</span>"), "inline", array("listitem", "block", "inline", "link"), array());
$bbcode->addCode("url", "usecontent?", "do_bbcode_url", array("usecontent_param" => "default"), "link", array("listitem", "block", "inline"), array("link"));
$bbcode->addCode("color", "usecontent?", "do_bbcode_color", array("usecontent_param" => "default"), "link", array("listitem", "block", "inline"), array());
$bbcode->addCode("link", "callback_replace_single", "do_bbcode_url", array(), "link", array("listitem", "block", "inline"), array("link"));
$bbcode->addCode("img", "usecontent", "do_bbcode_img", array(), "image", array("listitem", "block", "inline", "link"), array());
$bbcode->setOccurrenceType("img", "image");
$bbcode->setMaxOccurrences("image", 10);
$bbcode->addCode("list", "simple_replace", null, array("start_tag" => "<ul>", "end_tag" => "</ul>"), "list", array("block", "listitem"), array());
$bbcode->addCode("*", "simple_replace", null, array("start_tag" => "<li>", "end_tag" => "</li>"), "listitem", array("list"), array());
$bbcode->setCodeFlag("*", "closetag", BBCODE_CLOSETAG_OPTIONAL);
$bbcode->setCodeFlag("*", "paragraphs", true);
$bbcode->setCodeFlag("list", "paragraph_type", BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag("list", "opentag.before.newline", BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag("list", "closetag.before.newline", BBCODE_NEWLINE_DROP);
$bbcode->setRootParagraphHandling(false);
?>