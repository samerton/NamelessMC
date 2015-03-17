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
					$parent_name = $this->_db->get("forums", array("id", "=", $forum_query[0]->parent))->results();
					$parent_name = $parent_name[0]->forum_title;
					
					$return[$parent_name][] = $forum_title;
					
				}
			}
		}
		return $return;
	}
	
	// Returns an array of forums a user can access, in order
	// Params: $group_id (integer) - group id of the user
	public function orderAllForums($group_id = null) {
		if($group_id == null){
			$group_id = 0;
		}
		// Get the forums the user can view based on their group ID
		$access = $this->_db->get("forums_permissions", array("group_id", "=", $group_id))->results();
		
		$return = array(); // Array to return containing forums
		
		// Get the forum information as an array
		foreach($access as $forum){
			// Can they view it?
			if($forum->view == 1){
				// Get the name..
				$forum_query = $this->_db->get("forums", array("id", "=", $forum->forum_id))->results();

				// Is it a parent category?
				if($forum_query[0]->parent != 0){ // No
					$return[] = (array) $forum_query[0];
				}
			}
		}
		
		usort($return, function($a, $b) {
			return $a['forum_order'] - $b['forum_order'];
		});
		
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
	
	// Returns true/false, depending on whether the specified forum exists and whether the user can view it
	// Params: $forum_id (integer) - forum id to check, $group_id (integer) - group id of the user
	public function forumExist($forum_id, $group_id = null) {
		if($group_id == null){
			$group_id = 0;
		}
		// Does the forum exist?
		$exists = $this->_db->get("forums", array("id", "=", $forum_id))->results();
		if(count($exists)){
			// Can the user view it?
			$access = $this->_db->get("forums_permissions", array("forum_id", "=", $forum_id))->results();
			
			foreach($access as $item){
				if($item->group_id == $group_id){
					if($item->view == 1){
						return true;
					}
				}
			}
		}

		return false;
	}
	
}
