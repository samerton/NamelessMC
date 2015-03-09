<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */
 
class Forum {
	private $_db,
			$_data;
	
	public function __construct() {
		$this->_db = DB::getInstance();
	}
	
	// Returns an array of forums a user can access
	// Params: $group_id (integer) - group id of the user
	public function listAllForums($group_id = null) {
		if($group_id == null){
			$group_id = 0;
		}
		// Get the forums the user can view based on their group ID
		$access = $this->_db->get("forums_permissions", array("group_id", "=", $group_id))->results();
		
		$return = array(); // Array to return containing forums
		
		// Get the forum names
		foreach($access as $forum){
			// Can they view it?
			if($forum->view == 1){
				// Get the name..
				$forum_query = $this->_db->get("forums", array("id", "=", $forum->forum_id))->results();
				$forum_title = $forum_query[0]->forum_title;

				// Is it a parent category?
				if($forum_query[0]->parent == 0){ // Yes
					$return[$forum_title][] = "";
					
				} else { // No
					// Get the name of the parent category
					$parent_name = $this->_db->get("forums", array("id", "=", $forum->forum_id))->results();
					$parent_name = $parent_name[0]->forum_title;
					
					$return[$parent_name][] = $forum_title;
					
				}
			}
		}
		
		return $return;
	}
	
	// Returns an array of the latest discussions a user can access (10 from each category)
	// Params: $group_id (integer) - group id of the user
	public function getLatestDiscussions($group_id = null) {
		if($group_id == null){
			$group_id = 0;
		}
		// Get the forums the user can view based on their group ID
		$access = $this->_db->get("forums_permissions", array("group_id", "=", $group_id))->results();
		
		$return = array(); // Array to return containing discussions
		
		// Get the discussions
		foreach($access as $forum){
			// Can they view it?
			if($forum->view == 1){
				// Get a list of discussions
				$discussions_query = $this->_db->get("topics", array("forum_id", "=", $forum->forum_id . " ORDER BY `topic_reply_date` LIMIT 10"))->results();
				foreach($discussions_query as $discussion){
					$return[] = (array) $discussion;
				}
			}
		}
		return $return;
	}
	
}