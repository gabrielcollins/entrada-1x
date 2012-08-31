<?php
require_once("Entrada/lastrss/lastrss.class.php");

class rssFeed extends lastRSS {
	function rssFeed() {
		$this->cache_dir	= RSS_CACHE_DIRECTORY;
		$this->cache_time	= RSS_CACHE_TIMEOUT;
		$this->CDATA		= "content";
		$this->stripHTML 	= true;
		
	}
	
	function fetch($feed_url, $items_limit = 5) {
		$this->items_limit	= $items_limit;
		
		return $this->Get($feed_url);
	}
}
?>