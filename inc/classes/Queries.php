<?php
class Queries {
	private $_db,
			$_data;
	
	public function __construct() {
		$this->_db = DB::getInstance();
	}
	
	public function getWhere($table, $where) {
		$data = $this->_db->get($table, $where);
		return $data->results();
	}
	
	public function getAll($table, $where = array()) {
		$data = $this->_db->get($table, $where);
		return $data->results();
	}
	
	public function orderAll($table, $order, $sort = null) {
		$data = $this->_db->orderAll($table, $order, $sort);
		return $data->results();
	}
	
	public function orderWhere($table, $where, $order, $sort = null) {
		$data = $this->_db->orderWhere($table, $where, $order, $sort);
		return $data->results();
	}
	
	public function getLike($table, $where, $like){
		$data = $this->_db->like($table, $where, $like);
		return $data->results();
	}
	
	public function update($table, $id, $fields = array()) {
		if(!$this->_db->update($table, $id, $fields)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function create($table, $fields = array()) {
		if(!$this->_db->insert($table, $fields)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function delete($table, $where) {
		if(!$this->_db->delete($table, $where)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function increment($table, $id, $field) {
		if(!$this->_db->increment($table, $id, $field)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function decrement($table, $id, $field) {
		if(!$this->_db->decrement($table, $id, $field)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function createTable($table, $columns, $other) {
		if(!$this->_db->createTable($table, $columns, $other)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function convertCurrency($id) {
		if($id == "0"){
			return '$';
		} else if($id == "1"){
			return '£';
		} else if($id == "2"){
			return '€';
		}
	}
	
	public function convertQuestionType($type) {
		if($type == "1"){
			return 'Dropdown';
		} else if($type == "2"){
			return 'Text';
		} else if($type == "3"){
			return 'Textarea';
		}
	}
	
	public function getLastId() {
		return $this->_db->lastid();
	}
	
	public function alterTable($table, $column, $attributes){
		if(!$this->_db->alterTable($table, $column, $attributes)) {
			throw new Exception('There was a problem performing that action.');
		}
	}
	
	public function tableExists($table){
		return $this->_db->showTables($table);
	}
	
	public function dbInitialise(){
		echo '<div class="alert alert-info">';
		$data = $this->_db->createTable("buycraft_data", " `id` int(11) NOT NULL AUTO_INCREMENT, `time` datetime NOT NULL, `uuid` varchar(32) NOT NULL, `ign` varchar(20) NOT NULL, `price` varchar(5) NOT NULL, `package` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Buycraft data</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("custom_pages", " `id` int(11) NOT NULL AUTO_INCREMENT, `url` varchar(20) NOT NULL, `title` varchar(30) NOT NULL, `content` mediumtext NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Custom pages</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("donation_categories", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `cid` int(11) NOT NULL, `order` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Donation categories</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("donation_packages", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `description` varchar(2048) NOT NULL, `cost` varchar(8) NOT NULL, `package_id` int(8) NOT NULL, `active` tinyint(4) NOT NULL DEFAULT '0', `package_order` int(11) NOT NULL, `category` int(11) NOT NULL DEFAULT '0', `url` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Donation packages</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("forums", " `id` int(11) NOT NULL AUTO_INCREMENT, `forum_title` varchar(150) NOT NULL, `forum_description` varchar(255) NOT NULL, `last_post_date` datetime DEFAULT NULL, `last_user_posted` int(11) DEFAULT NULL, `last_topic_posted` int(11) DEFAULT NULL, `parent` int(11) NOT NULL DEFAULT '0', `forum_order` int(11) NOT NULL, `news` tinyint(4) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Forum</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("forums_permissions", " `id` int(11) NOT NULL AUTO_INCREMENT, `group_id` int(11) NOT NULL, `forum_id` int(11) NOT NULL, `view` tinyint(4) NOT NULL DEFAULT '1', `create_topic` tinyint(4) NOT NULL DEFAULT '1', `create_post` tinyint(4) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Forum permissions</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("friends", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `friend_id` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Friends</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("groups", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `buycraft_id` int(11) DEFAULT NULL, `group_html` varchar(1024) NOT NULL, `group_html_lg` varchar(1024) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Groups</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("infractions", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` int(11) NOT NULL, `punished` int(11) NOT NULL, `staff` int(11) NOT NULL, `reason` text NOT NULL, `infraction_date` datetime NOT NULL, `acknowledged` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Infractions</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("infraction_appeals", " `id` int(11) NOT NULL AUTO_INCREMENT, `infraction_type` int(11) NOT NULL, `status` int(11) NOT NULL, `date_appealed` datetime NOT NULL, `date_updated` datetime NOT NULL, `updated_by` int(11) NOT NULL, `appeal_content` mediumtext NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Infraction appeals</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("infraction_appeals_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `appeal_id` int(11) NOT NULL, `commenter_id` int(11) NOT NULL, `comment_date` datetime NOT NULL, `comment_content` mediumtext NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Infraction appeals comments</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("logs", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` varchar(20) NOT NULL, `user_id` int(11) NOT NULL, `user_ip` varchar(45) NOT NULL, `info` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Logs</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("mc_servers", " `id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(64) NOT NULL, `name` varchar(20) NOT NULL, `is_default` tinyint(4) NOT NULL DEFAULT '0', `display` tinyint(4) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Minecraft servers</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("plugins", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(128) NOT NULL, `enabled` tinyint(4) NOT NULL DEFAULT '0', `description` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Plugins</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("posts", " `id` int(11) NOT NULL AUTO_INCREMENT, `forum_id` int(11) NOT NULL, `topic_id` int(11) NOT NULL, `post_creator` int(11) NOT NULL, `post_content` mediumtext NOT NULL, `post_date` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Posts</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("private_messages", " `id` int(11) NOT NULL AUTO_INCREMENT, `author_id` int(11) NOT NULL, `title` varchar(128) NOT NULL, `content` mediumtext NOT NULL, `sent_date` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Private messages</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("private_messages_users", " `id` int(11) NOT NULL AUTO_INCREMENT, `pm_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `read` tinyint(4) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Private messages users</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("reports", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` tinyint(4) NOT NULL, `reporter_id` int(11) NOT NULL, `reported_id` int(11) NOT NULL, `status` tinyint(4) NOT NULL, `date_reported` datetime NOT NULL, `date_updated` datetime NOT NULL, `report_reason` varchar(255) NOT NULL, `updated_by` int(11) NOT NULL, `reported_post` int(11) NOT NULL, `reported_post_topic` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Reports</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("reports_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `report_id` int(11) NOT NULL, `commenter_id` int(11) NOT NULL, `comment_date` datetime NOT NULL, `comment_content` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Reports comments</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("reputation", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_received` int(11) NOT NULL, `post_id` int(11) NOT NULL, `topic_id` int(11) NOT NULL, `user_given` int(11) NOT NULL, `time_given` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Reputation</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("settings", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(128) NOT NULL, `value` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Settings</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("staff_apps_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `aid` int(11) NOT NULL, `uid` int(11) NOT NULL, `time` int(11) NOT NULL, `content` mediumtext NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Staff applications comments</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("staff_apps_questions", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` int(11) NOT NULL, `name` varchar(16) NOT NULL, `question` varchar(256) NOT NULL, `options` text, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Staff applications questions</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("staff_apps_replies", " `id` int(11) NOT NULL AUTO_INCREMENT, `uid` int(11) NOT NULL, `time` int(11) NOT NULL, `content` mediumtext NOT NULL, `status` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Staff applications replies</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("topics", " `id` int(11) NOT NULL AUTO_INCREMENT, `forum_id` int(11) NOT NULL, `topic_title` varchar(150) NOT NULL, `topic_creator` int(11) NOT NULL, `topic_last_user` int(11) NOT NULL, `topic_date` int(11) NOT NULL, `topic_reply_date` int(11) NOT NULL, `topic_views` int(11) NOT NULL, `locked` tinyint(4) NOT NULL, `sticky` tinyint(4) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Topics</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("users", " `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(20) NOT NULL, `password` varchar(60) NOT NULL, `pass_method` varchar(12) NOT NULL DEFAULT 'default', `mcname` varchar(20) NOT NULL, `uuid` varchar(32) NOT NULL, `joined` int(11) NOT NULL, `group_id` int(11) NOT NULL, `email` varchar(64) NOT NULL, `isbanned` tinyint(4) NOT NULL DEFAULT '0', `lastip` varchar(45) NOT NULL, `active` tinyint(4) NOT NULL DEFAULT '0', `signature` varchar(1024) DEFAULT NULL, `reputation` int(11) NOT NULL DEFAULT '0', `reset_code` varchar(60) DEFAULT NULL, `has_avatar` tinyint(4) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Users</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("users_ips", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `ip` varchar(45) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Users IPs</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("users_session", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `hash` varchar(50) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Users session</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("users_admin_session", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `hash` varchar(50) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Users admin session</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("users_signin_attempts", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `ip` varchar(45) NOT NULL, `attempted` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Users signin attempts</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("uuid_cache", " `id` int(11) NOT NULL AUTO_INCREMENT, `mcname` varchar(20) NOT NULL, `uuid` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>UUID cache</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("vote_sites", " `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(512) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Vote sites</strong> table successfully initialised<br />';
		$data = $this->_db->createTable("vote_top", " `id` int(11) NOT NULL AUTO_INCREMENT, `uuid` varchar(64) NOT NULL, `time_saved` int(11) NOT NULL, `number` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		echo '<strong>Vote top</strong> table successfully initialised<br />';
		echo '</div>';
		return '<div class="alert alert-success">Database successfully initialised.</div>';
	}
}