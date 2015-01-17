<?php
class Forum {
	private $_db,
			$_data;
	
	public function __construct() {
		$this->_db = DB::getInstance();
	}
	
	public function getCategories($group_id = null) {
		$data = $this->_db->orderAll('categories', "cat_order", "ASC")->results();
		if(!empty($data)){
			$results = array();
			$n = 0;
			foreach($data as $category){
				if($category->parent == 0){
					if($group_id !== null){
						if($group_id == 1){
							if($category->view_access == '0'){
								$results[$n]["id"] = $category->id;
								$results[$n]["title"] = htmlspecialchars($category->category_title);
								$results[$n]["parent"] = "true";
							}
						} else if($group_id == 2){
							$results[$n]["id"] = $category->id;
							$results[$n]["title"] = htmlspecialchars($category->category_title);
							$results[$n]["parent"] = "true";
						} else if($group_id == 3){
							if($category->view_access == '0' || $category->view_access = '1'){
								$results[$n]["id"] = $category->id;
								$results[$n]["title"] = htmlspecialchars($category->category_title);
								$results[$n]["parent"] = "true";
							}
						}
					} else {
						if($category->view_access == '0'){
							$results[$n]["id"] = $category->id;
							$results[$n]["title"] = htmlspecialchars($category->category_title);
							$results[$n]["parent"] = "true";
						}
					}
				} else {
					if($group_id !== null){
						if($group_id == 1){
							if($category->view_access == '0'){
								$results[$n]["id"] = $category->id;
								$results[$n]["title"] = htmlspecialchars($category->category_title);
								$results[$n]["description"] = htmlspecialchars($category->category_description);
								$results[$n]["last_post_date"] = $category->last_post_date;
								$results[$n]["last_post_user"] = $category->last_user_posted;
								$results[$n]["last_post_topic"] = $category->last_topic_posted;
							}
						} else if($group_id == 2){
							$results[$n]["id"] = $category->id;
							$results[$n]["title"] = htmlspecialchars($category->category_title);
							$results[$n]["description"] = htmlspecialchars($category->category_description);
							$results[$n]["last_post_date"] = $category->last_post_date;
							$results[$n]["last_post_user"] = $category->last_user_posted;
							$results[$n]["last_post_topic"] = $category->last_topic_posted;
						} else if($group_id == 3){
							if($category->view_access == '0' || $category->view_access == '1'){
								$results[$n]["id"] = $category->id;
								$results[$n]["title"] = htmlspecialchars($category->category_title);
								$results[$n]["description"] = htmlspecialchars($category->category_description);
								$results[$n]["last_post_date"] = $category->last_post_date;
								$results[$n]["last_post_user"] = $category->last_user_posted;
								$results[$n]["last_post_topic"] = $category->last_topic_posted;
							}
						}
					} else {
						if($category->view_access == '0'){
							$results[$n]["id"] = $category->id;
							$results[$n]["title"] = htmlspecialchars($category->category_title);
							$results[$n]["description"] = htmlspecialchars($category->category_description);
							$results[$n]["last_post_date"] = $category->last_post_date;
							$results[$n]["last_post_user"] = $category->last_user_posted;
							$results[$n]["last_post_topic"] = $category->last_topic_posted;
						}
					}
				}
				$n++;
			}
		}
		return $results;
	}
	
	public function getLatestDiscussions($group_id = null){
		$data = $this->_db->orderAll('topics', 'topic_reply_date', 'DESC LIMIT 50')->results();
		$n = 0;
		$i = 0;
		
		if(count($data) < 10){
			$max = count($data);
		} else {
			$max = 10;
		}
		
		while ($i < $max){
			$topic_category = $this->_db->get('categories', array('id', '=', $data[$n]->category_id))->results()[0];
			$posts = count($this->_db->get('posts', array('topic_id', '=', $data[$n]->id))->results());
			if($group_id !== null){
				if($group_id == 1){
					if($topic_category->view_access == '0'){
						$discussion[$i]["category_id"] = $topic_category->id;
						$discussion[$i]["category"] = htmlspecialchars($topic_category->category_title);
						$discussion[$i]["id"] = $data[$n]->id;
						$discussion[$i]["title"] = htmlspecialchars($data[$n]->topic_title);
						$discussion[$i]["creator"] = htmlspecialchars($data[$n]->topic_creator);
						$discussion[$i]["last_user"] = htmlspecialchars($data[$n]->topic_last_user);
						$discussion[$i]["date"] = $data[$n]->topic_date;
						$discussion[$i]["reply_date"] = $data[$n]->topic_reply_date;
						$discussion[$i]["views"] = $data[$n]->topic_views;
						$discussion[$i]["locked"] = $data[$n]->locked;
						$discussion[$i]["replies"] = $posts;
						$i++;
					}
				} else if($group_id == 2){
					$discussion[$i]["category_id"] = $topic_category->id;
					$discussion[$i]["category"] = htmlspecialchars($topic_category->category_title);
					$discussion[$i]["id"] = $data[$n]->id;
					$discussion[$i]["title"] = htmlspecialchars($data[$n]->topic_title);
					$discussion[$i]["creator"] = htmlspecialchars($data[$n]->topic_creator);
					$discussion[$i]["last_user"] = htmlspecialchars($data[$n]->topic_last_user);
					$discussion[$i]["date"] = $data[$n]->topic_date;
					$discussion[$i]["reply_date"] = $data[$n]->topic_reply_date;
					$discussion[$i]["views"] = $data[$n]->topic_views;
					$discussion[$i]["locked"] = $data[$n]->locked;
					$discussion[$i]["replies"] = $posts;
					$i++;
				} else if($group_id == 3){
					if($topic_category->view_access == '0' || $topic_category->view_access == '1'){
						$discussion[$i]["category_id"] = $topic_category->id;
						$discussion[$i]["category"] = htmlspecialchars($topic_category->category_title);
						$discussion[$i]["id"] = $data[$n]->id;
						$discussion[$i]["title"] = htmlspecialchars($data[$n]->topic_title);
						$discussion[$i]["creator"] = htmlspecialchars($data[$n]->topic_creator);
						$discussion[$i]["last_user"] = htmlspecialchars($data[$n]->topic_last_user);
						$discussion[$i]["date"] = $data[$n]->topic_date;
						$discussion[$i]["reply_date"] = $data[$n]->topic_reply_date;
						$discussion[$i]["views"] = $data[$n]->topic_views;
						$discussion[$i]["locked"] = $data[$n]->locked;
						$discussion[$i]["replies"] = $posts;
						$i++;
					}
				}
			} else {
				if($topic_category->view_access == '0'){
					$discussion[$i]["category_id"] = $topic_category->id;
					$discussion[$i]["category"] = htmlspecialchars($topic_category->category_title);
					$discussion[$i]["id"] = $data[$n]->id;
					$discussion[$i]["title"] = htmlspecialchars($data[$n]->topic_title);
					$discussion[$i]["creator"] = htmlspecialchars($data[$n]->topic_creator);
					$discussion[$i]["last_user"] = htmlspecialchars($data[$n]->topic_last_user);
					$discussion[$i]["date"] = $data[$n]->topic_date;
					$discussion[$i]["reply_date"] = $data[$n]->topic_reply_date;
					$discussion[$i]["views"] = $data[$n]->topic_views;
					$discussion[$i]["locked"] = $data[$n]->locked;
					$discussion[$i]["replies"] = $posts;
					$i++;
				}
			}
			$n++;
			if($n > $max){
				return $discussion;
			}
		}
		if(isset($discussion)){
			return $discussion;
		} else {
			return false;
		}
	}
	
	public function listCategories($group_id = null) {
		$data = $this->_db->orderAll('categories', "cat_order", "ASC");
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			while ($no < $numrows) {
				if($group_id !== null){
					if($group_id == 2){
						if($data->results()[$no]->parent !== '0'){
							$categories_id[] = $data->results()[$no]->id;
							$categories_titles[] = htmlspecialchars($data->results()[$no]->category_title);
							$categories_desc[] = htmlspecialchars($data->results()[$no]->category_description);
							$categories_last_post_date[] = $data->results()[$no]->last_post_date;
							$categories_last_user_posted[] = $data->results()[$no]->last_user_posted;
							$categories_last_topic_posted[] = $data->results()[$no]->last_topic_posted;
						}
					} else if($group_id == 3){
						if(($data->results()[$no]->parent !== '0') && ($data->results()[$no]->view_access == 0 || $data->results()[$no]->view_access == 1)){
							$categories_id[] = $data->results()[$no]->id;
							$categories_titles[] = htmlspecialchars($data->results()[$no]->category_title);
							$categories_desc[] = htmlspecialchars($data->results()[$no]->category_description);
							$categories_last_post_date[] = $data->results()[$no]->last_post_date;
							$categories_last_user_posted[] = $data->results()[$no]->last_user_posted;
							$categories_last_topic_posted[] = $data->results()[$no]->last_topic_posted;
						}
					} else {
						if($data->results()[$no]->parent !== '0' && $data->results()[$no]->view_access == 0){
							$categories_id[] = $data->results()[$no]->id;
							$categories_titles[] = htmlspecialchars($data->results()[$no]->category_title);
							$categories_desc[] = htmlspecialchars($data->results()[$no]->category_description);
							$categories_last_post_date[] = $data->results()[$no]->last_post_date;
							$categories_last_user_posted[] = $data->results()[$no]->last_user_posted;
							$categories_last_topic_posted[] = $data->results()[$no]->last_topic_posted;
						}
					}
				} else {
					if($data->results()[$no]->parent !== '0' && $data->results()[$no]->view_access == 0){
						$categories_id[] = $data->results()[$no]->id;
						$categories_titles[] = htmlspecialchars($data->results()[$no]->category_title);
						$categories_desc[] = htmlspecialchars($data->results()[$no]->category_description);
						$categories_last_post_date[] = $data->results()[$no]->last_post_date;
						$categories_last_user_posted[] = $data->results()[$no]->last_user_posted;
						$categories_last_topic_posted[] = $data->results()[$no]->last_topic_posted;
					}
				}
				$no++;
			}
		}
		return array($categories_id, $categories_titles, $categories_desc, $categories_last_post_date, $categories_last_user_posted, $categories_last_topic_posted);
	}
	
	public function catExist($cat_id) {
		$data = $this->_db->get('categories', array('id', '=', $cat_id));
		if($data->count()) {
			return true;
		}
		return false;
	}
	
	public function listTopics($cat_id) {
		$data = $this->_db->orderWhere('topics', 'category_id = ' . $cat_id, 'topic_reply_date', 'DESC');
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			while ($no < $numrows) {
				$topics_id[] = $data->results()[$no]->id;
				$topics_titles[] = $data->results()[$no]->topic_title;
				$topics_creator[] = $data->results()[$no]->topic_creator;
				$topics_last_user[] = $data->results()[$no]->topic_last_user;
				$topics_date[] = $data->results()[$no]->topic_date;
				$topics_reply_date[] = $data->results()[$no]->topic_reply_date;
				$topics_views[] = $data->results()[$no]->topic_views;
				$no = ($no + 1);
			}
			return array($topics_id, $topics_titles, $topics_creator, $topics_last_user, $topics_date, $topics_reply_date, $topics_views);
		}
		return false;
	}
	
	public function countPosts($cat_id, $from) {
		$data = $this->_db->get('posts', array($from, '=', $cat_id));
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			while ($no < $numrows) {
				$posts_id[] = $data->results()[$no]->id;
				$no = ($no + 1);
			}
			return count($posts_id);
		}
		return 0;
	}

	public function newThread($fields = array()) {
		if(!$this->_db->insert('topics', $fields)) {
			throw new Exception('There was a problem creating your topic. Please try again later.');
		}
	}
	
	public function updateCategories($cid, $fields = array()) {
		if(!$this->_db->update('categories', $cid, $fields)) {
			throw new Exception('There was a problem creating your post. Please try again later.');
		}
	}
	
	public function updateTopic($tid, $fields = array()) {
		if(!$this->_db->update('topics', $tid, $fields)) {
			throw new Exception('There was a problem creating your post. Please try again later.');
		}
	}
	
	public function newPost($fields = array()) {
		if(!$this->_db->insert('posts', $fields)) {
			throw new Exception('There was a problem creating your post. Please try again later.');
		}
	}
	
	public function lastId() {
		return $this->_db->lastid();
	}
	
	public function getPost($topic_id) {
		$data = $this->_db->get('posts', array('topic_id', '=', $topic_id));
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			while ($no < $numrows) {
				$post_id[] = $data->results()[$no]->id;
				$post_creator[] = $data->results()[$no]->post_creator;
				$post_content[] = $data->results()[$no]->post_content;
				$post_date[] = $data->results()[$no]->post_date;
				$cat_id[] = $data->results()[$no]->category_id;
				$no = ($no + 1);
			}
			return array($post_id, $post_creator, $post_content, $post_date, $cat_id);
		}
		return false;
	}
	
	public function getIndividualPost($post_id) {
		$data = $this->_db->get('posts', array('id', '=', $post_id));
		if($data->count()) {
			$post_creator[] = $data->results()[0]->post_creator;
			$post_content[] = $data->results()[0]->post_content;
			$post_date[] = $data->results()[0]->post_date;
			$cat_id[] = $data->results()[0]->category_id;
			return array($post_creator, $post_content, $post_date, $cat_id);
		}
		return false;
	}
	
	public function getTitle($topic_id) {
		$data = $this->_db->get('topics', array('id', '=', $topic_id));
		return $data->results()[0]->topic_title;
	}
	
	public function getCategoryTitle($cat_id) {
		$data = $this->_db->get('categories', array('id', '=', $cat_id));
		return $data->results()[0]->category_title;
	}
	
	public function updateCatLatestPosts(){
		$categories = $this->_db->get('categories', array('id', '<>', 0))->results();
		$latest_posts = array();
		$n = 0;
		
		foreach($categories as $category){
			if($category->parent != 0){
				$latest_post = $this->_db->orderWhere('posts', 'category_id = ' . $category->id, 'post_date', 'DESC LIMIT 1')->results()[0];
				
				$latest_posts[$n]["cat_id"] = $category->id;
				$latest_posts[$n]["date"] = $latest_post->post_date;
				$latest_posts[$n]["author"] = $latest_post->post_creator;
				$latest_posts[$n]["topic_id"] = $latest_post->topic_id;
				
				$n++;
			}
		};
		
		$categories = null;
		
		foreach($latest_posts as $latest_post){
			if(!empty($latest_post["date"])){
				$this->_db->update('categories', $latest_post["cat_id"],  array(
					'last_post_date' => $latest_post["date"],
					'last_user_posted' => $latest_post["author"],
					'last_topic_posted' => $latest_post["topic_id"]
				));
			}
		}
		
		$latest_posts = null;
		
		return true;
	}
	
	public function updateTopicLatestPosts(){
		$topics = $this->_db->get('topics', array('id', '<>', 0))->results();
		$latest_posts = array();
		$n = 0;
		
		foreach($topics as $topic){
			$latest_post = $this->_db->orderWhere('posts', 'topic_id = ' . $topic->id, 'post_date', 'DESC LIMIT 1')->results()[0];
			
			$latest_posts[$n]["topic_id"] = $topic->id;
			$latest_posts[$n]["date"] = $latest_post->post_date;
			$latest_posts[$n]["author"] = $latest_post->post_creator;
			
			$n++;
		};
		
		foreach($latest_posts as $latest_post){
			if(!empty($latest_post["date"])){
				$this->_db->update('topics', $latest_post["topic_id"],  array(
					'topic_reply_date' => $latest_post["date"],
					'topic_last_user' => $latest_post["author"]
				));
			}
		}
		
		return true;
	}
	
	public function isLocked($topic_id) {
		$data = $this->_db->get('topics', array('id', '=', $topic_id));
		if($data->results()[0]->locked == 1){
			return true;
		} else {
			return false;
		}
	}
	
	public function getQuote($post_id) {
		$data = $this->_db->get('posts', array('id', '=', $post_id));
		$user = $data->results()[0]->post_creator;
		$date = $data->results()[0]->post_date;
		$content = $data->results()[0]->post_content;
		return array($user, $date, $content);
	}
	
	public function deletePost($post_id, $fields = array()){
		if(!$this->_db->update('posts', $post_id, $fields)) {
			throw new Exception('There was a problem deleting the post.');
		}
	}
	
	public function getLatestPosts($table, $order, $sort) {
		$data = $this->_db->orderAll($table, $order, $sort);
		return $data->results();
	}
	
	public function getReputation($post_id) {
		$data = $this->_db->get('reputation', array('post_id', '=', $post_id));
		return $data->results();
	}
	
	public function hasUnreadMessages($user_id){
		$messages = $this->_db->get('private_messages', array('id', '<>', 0))->results();
		$has_messages = false;
		foreach($messages as $message){
			$parts = explode('_', $message->user_1);
			$message_user_id = $parts[0];
			$message_is_read = $parts[1];
			if($message_user_id == $user_id && $message_is_read == 0){
				$has_messages = true;
				break;
			}
			
			$parts = explode('_', $message->user_2);
			$message_user_id = $parts[0];
			$message_is_read = $parts[1];
			if($message_user_id == $user_id && $message_is_read == 0){
				$has_messages = true;
				break;
			}
		}
		return $has_messages;
	}
	
	public function getAllMessages($user_id){
		$messages = $this->_db->orderWhere('private_messages', 'id <> 0', 'sent_date', 'DESC')->results();
		$return = array();
		$i = 0;
		foreach($messages as $message){
			$parts = explode('_', $message->user_1);
			$message_user_id = $parts[0];
			if($message_user_id == $user_id){
				$return[$i]["id"] = $message->id;
				$return[$i]["direction"] = "To";
				$return[$i]["title"] = htmlspecialchars($message->title);
				$return[$i]["other_user"] = htmlspecialchars(explode('_', $message->user_2)[0]);
				$return[$i]["date"] = $message->sent_date;
				$i++;
			}
			
			$parts = explode('_', $message->user_2);
			$message_user_id = $parts[0];
			if($message_user_id == $user_id){
				$return[$i]["id"] = $message->id;
				$return[$i]["direction"] = "From";
				$return[$i]["title"] = htmlspecialchars($message->title);
				$return[$i]["other_user"] = htmlspecialchars(explode('_', $message->user_1)[0]);
				$return[$i]["date"] = $message->sent_date;
				$i++;
			}
		}
		return $return;
	}
	
}