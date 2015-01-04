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
	
	public function convertCurrency($id) {
		if($id == "0"){
			return '$';
		} else if($id == "1"){
			return '£';
		} else if($id == "2"){
			return '€';
		}
	}
	
	public function dbInitialise(){
		$data = $this->_db->action('SELECT 1', 'settings', array('id', '<>', 0));
		if(!empty($data)){
			return "<div class=\"alert alert-warning\">Database already initialised!</div>";
		} else {
			$data = $this->_db->createTable("buycraft_data", " `id` int(11) NOT NULL AUTO_INCREMENT, `time` datetime NOT NULL, `uuid` varchar(32) NOT NULL, `ign` varchar(20) NOT NULL, `price` varchar(5) NOT NULL, `package` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo '<div class="alert alert-info">Buycraft data table successfully initialised</br />';
			$data = $this->_db->createTable("categories", " `id` int(11) NOT NULL AUTO_INCREMENT, `category_title` varchar(150) NOT NULL, `category_description` varchar(255) NOT NULL, `last_post_date` datetime DEFAULT NULL, `last_user_posted` int(11) DEFAULT NULL, `last_topic_posted` int(11) DEFAULT NULL, `parent` int(11) NOT NULL DEFAULT '0', `cat_order` int(11) NOT NULL, `access` int(11) NOT NULL DEFAULT '0', `news` int(11) NOT NULL DEFAULT '0', `view_access` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Categories table successfully initialised<br />';
 			$data = $this->_db->createTable("custom_pfs", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `order` int(11) NOT NULL, `length` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Custom profile fields table successfully initialised<br />';
 			$data = $this->_db->createTable("custom_pfs_users", " `id` int(11) NOT NULL AUTO_INCREMENT, `pfid` int(11) NOT NULL, `uid` int(11) NOT NULL, `value` varchar(1024) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Custom profile fields users table successfully initialised<br />';
  			$data = $this->_db->createTable("donation_packages", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(64) NOT NULL, `description` varchar(2048) NOT NULL, `cost` varchar(8) NOT NULL, `package_id` int(8) NOT NULL, `active` int(11) NOT NULL DEFAULT '0', `package_order` int(11) NOT NULL, `category` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Donation packages table successfully initialised<br />';
			$data = $this->_db->createTable("friends", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `friend_id` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Friends table successfully initialised<br />';
			$data = $this->_db->createTable("groups", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `permissions` text NOT NULL, `buycraft_id` int(11) DEFAULT NULL, `group_html` varchar(1024) NOT NULL, `group_html_lg` varchar(1024) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Groups table successfully initialised<br />';
			$data = $this->_db->createTable("infractions", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` int(11) NOT NULL, `punished` int(11) NOT NULL, `staff` int(11) NOT NULL, `reason` varchar(256) NOT NULL, `infraction_date` datetime NOT NULL, `acknowledged` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Infractions table successfully initialised<br />';
			$data = $this->_db->createTable("infraction_appeals", " `id` int(11) NOT NULL AUTO_INCREMENT, `infraction_type` int(11) NOT NULL, `status` int(11) NOT NULL DEFAULT '0', `date_appealed` datetime NOT NULL, `date_updated` datetime NOT NULL, `updated_by` int(11) NOT NULL, `appeal_content` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Infraction appeals table successfully initialised<br />';
			$data = $this->_db->createTable("infraction_appeals_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `appeal_id` int(11) NOT NULL, `commenter_id` int(11) NOT NULL, `comment_date` datetime NOT NULL, `comment_content` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Infraction appeals comments table successfully initialised<br />';
			$data = $this->_db->createTable("logs", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` varchar(20) NOT NULL, `user_id` int(11) DEFAULT NULL, `user_ip` int(11) NOT NULL, `info` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Logs table successfully initialised<br />';
			$data = $this->_db->createTable("mc_servers", " `id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(64) NOT NULL, `name` varchar(20) NOT NULL, `is_default` int(11) NOT NULL DEFAULT '0', `display` int(11) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Minecraft servers table successfully initialised<br />';
			$data = $this->_db->createTable("posts", " `id` int(11) NOT NULL AUTO_INCREMENT, `category_id` tinyint(4) NOT NULL, `topic_id` int(11) NOT NULL, `post_creator` int(11) NOT NULL, `post_content` text NOT NULL, `post_date` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Posts table successfully initialised<br />';
			$data = $this->_db->createTable("private_messages", " `id` int(11) NOT NULL AUTO_INCREMENT, `conversation` int(11) NOT NULL, `title` varchar(64) NOT NULL, `user_1` varchar(10) NOT NULL, `user_2` varchar(10) NOT NULL, `content` varchar(2048) NOT NULL, `sent_date` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Private messages table successfully initialised<br />';
			$data = $this->_db->createTable("reports", " `id` int(11) NOT NULL AUTO_INCREMENT, `type` int(11) NOT NULL, `reporter_id` int(11) NOT NULL, `reported_id` int(11) NOT NULL, `status` int(11) NOT NULL, `date_reported` datetime NOT NULL, `date_updated` datetime NOT NULL, `report_reason` varchar(255) DEFAULT NULL, `updated_by` int(11) DEFAULT NULL, `reported_post` int(11) DEFAULT NULL, `reported_post_topic` int(11) DEFAULT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Reports table successfully initialised<br />';
			$data = $this->_db->createTable("reports_comments", " `id` int(11) NOT NULL AUTO_INCREMENT, `report_id` int(11) NOT NULL, `commenter_id` int(11) NOT NULL, `comment_date` datetime NOT NULL, `comment_content` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Report comments table successfully initialised<br />';
			$data = $this->_db->createTable("reputation", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_received` int(11) NOT NULL, `post_id` int(11) NOT NULL, `topic_id` int(11) NOT NULL, `user_given` int(11) NOT NULL, `time_given` datetime NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Reputation table successfully initialised<br />';
			$data = $this->_db->createTable("settings", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `value` varchar(2048) DEFAULT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Settings table successfully initialised<br />';
			$data = $this->_db->createTable("topics", " `id` int(11) NOT NULL AUTO_INCREMENT, `category_id` tinyint(4) NOT NULL, `topic_title` varchar(150) NOT NULL, `topic_creator` int(11) NOT NULL, `topic_last_user` int(11) DEFAULT NULL, `topic_date` datetime NOT NULL, `topic_reply_date` datetime NOT NULL, `topic_views` int(11) NOT NULL DEFAULT '0', `locked` int(11) NOT NULL DEFAULT '0', `sticky` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Topics table successfully initialised<br />';
			$data = $this->_db->createTable("users", " `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(20) NOT NULL, `password` varchar(64) NOT NULL, `salt` varchar(32) NOT NULL, `mcname` varchar(20) NOT NULL, `uuid` varchar(32) NOT NULL, `joined` datetime NOT NULL, `group_id` int(11) NOT NULL, `email` varchar(64) NOT NULL, `isbanned` tinyint(1) NOT NULL DEFAULT '0', `lastip` varchar(45) NOT NULL, `active` int(11) NOT NULL DEFAULT '0', `signature` varchar(255) NOT NULL, `reputation` int(11) NOT NULL DEFAULT '0', `reset_code` varchar(60) DEFAULT NULL, `theme_id` int(11) NOT NULL DEFAULT '1', `pf_location` varchar(255) DEFAULT NULL, `has_avatar` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Users table successfully initialised<br />';
			$data = $this->_db->createTable("users_ips", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `ip` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'User IPs table successfully initialised<br />';
			$data = $this->_db->createTable("users_session", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `hash` varchar(50) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
			echo 'Users session table successfully initialised<br />';
			$data = $this->_db->createTable("uuid_cache", " `id` int(11) NOT NULL AUTO_INCREMENT, `mcname` varchar(64) NOT NULL, `uuid` varchar(255) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=latin1");
			echo 'UUID Cache table successfully initialised<br />';
			$data = $this->_db->createTable("vote_sites", " `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(255) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Vote sites table successfully initialised<br />';
			$data = $this->_db->createTable("vote_top", " `id` int(11) NOT NULL AUTO_INCREMENT, `uuid` varchar(255) NOT NULL, `time_saved` datetime NOT NULL, `number` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			echo 'Top voters table successfully initialised<br /></div>';
			return '<div class="alert alert-success">Database successfully initialised.</div>';
		}
	}
}