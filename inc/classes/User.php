<?php
class User {
	private $_db,
			$_data,
			$_sessionName,
			$_cookieName,
			$_isLoggedIn;
	
	public function __construct($user = null) {
		$this->_db = DB::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');
		
		if(!$user) {
			if(Session::exists($this->_sessionName)) {
				$user = Session::get($this->_sessionName);
				if($this->find($user)){
					$this->_isLoggedIn = true;
				} else {
					// process logout
				}
			}
		} else {
			$this->find($user);
		}
		
	}

	public function getGroupName($group_id) {
		$data = $this->_db->get('groups', array('id', '=', $group_id));
		if($data->count()) {
			return htmlspecialchars($data->results()[0]->name);
		} else {
			return false;
		}
	}
	
	public function getIP() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		  $ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		  $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	public function addfriend($user1id, $user2id) {	
		$this->_db->insert('friends', array(
			'user_id' => $user1id,
			'friend_id' => $user2id
		));
		$this->_db->insert('friends', array(
			'user_id' => $user2id,
			'friend_id' => $user1id
		));
	}
	
	public function removefriend($user1, $user2) {	
		$data = $this->_db->get('friends', array('user_id', '=', $user1));
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			$finalno = 0;
			while ($no < $numrows) {
				$isfriend = $data->results()[$no]->friend_id;
					if ($isfriend == $user2) {
						$finalno = $data->results()[$no]->id;
						$no = ($numrows + 1);
					}
				$no = ($no + 1);
			}
		}
		$this->_db->delete('friends', array('id', '=', $finalno));
		
		$data = $this->_db->get('friends', array('user_id', '=', $user2));
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			$finalno = 0;
			while ($no < $numrows) {
				$isfriend = $data->results()[$no]->friend_id;
					if ($isfriend == $user1) {
						$finalno = $data->results()[$no]->id;
						$no = ($numrows + 1);
					}
				$no = ($no + 1);
			}
		}
		$this->_db->delete('friends', array('id', '=', $finalno));

	}
	
	public function isfriend($user1, $user2) {
		$returnbool = 0;
		$data = $this->_db->get('friends', array('user_id', '=', $user1));
		if($data->count()) {
			$numrows = (count($data->results()));
			$no = 0;
			while ($no < $numrows) {
				$isfriend = $data->results()[$no]->friend_id;
					if ($isfriend == $user2) {
						$returnbool = 1;
						$no = ($numrows + 1);
					}
				$no = ($no + 1);
			}
		}
		return $returnbool;
	}
	
	public function listFriends($user_id) {
		$data = $this->_db->get('friends', array('user_id', '=', $user_id));
		if($data->count()) {
			return $data->results();
		} else { 
		return false;
		}
	}
	
	public function update($fields = array(), $id = null) {
	
		if(!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}
	
		if(!$this->_db->update('users', $id, $fields)) {
			throw new Exception('There was a problem updating your details.');
		}
	}
	
	public function create($fields = array()) {
		if(!$this->_db->insert('users', $fields)) {
			throw new Exception('There was a problem creating an account.');
		}
	}
	
	public function find($user = null) {
		if ($user) {
			$field = (is_numeric($user)) ? 'id' : 'username';
			$data = $this->_db->get('users', array($field, '=', $user));
			
			if($data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}
	
	public function IdToName($id = null) {
		if ($id) {
			$data = $this->_db->get('users', array('id', '=', $id));
			
			if($data->count()) {
				return $data->results()[0]->username;
			}
		}
		return false;
	}
	
	public function IdToMCName($id = null) {
		if ($id) {
			$data = $this->_db->get('users', array('id', '=', $id));
			
			if($data->count()) {
				return $data->results()[0]->mcname;
			}
		}
		return false;
	}
	
	public function login($username = null, $password = null, $remember = false) {
		
		
		if(!$username && !$password && $this->exists()){
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($username);
			if($user){
				if(password_verify($password, $this->data()->password)) {
					Session::put($this->_sessionName, $this->data()->id);
				
					if($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));
					
						if(!$hashCheck->count()) {
							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->first()->hash;
						}
					
						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					
					}
				
					return true;
				}
			}
		}
		return false;
	}
	
	public function hasPermission($key) {
		$group = $this->_db->get('groups', array('id', '=', $this->data()->group));
		
		if($group->count()) {
			$permissions = json_decode($group->first()->permissions, true);
			
			if($permissions[$key] == true) {
				return true;
			}
			return false;
		}
		
	}
	
	public function getGroup($id, $html = null, $large = null) {
		$data = $this->_db->get('users', array('id', '=', $id));
		if($html === null){
			if($large === null){
				return $data->results()[0]->group_id;
			} else {
				$data = $this->_db->get('groups', array('id', '=', $data->results()[0]->group_id));
				return $data->results()[0]->group_html_lg;
			}
		} else {
			$data = $this->_db->get('groups', array('id', '=', $data->results()[0]->group_id));
			return $data->results()[0]->group_html;
		}
	}
	
	public function getSignature($id) {
		$data = $this->_db->get('users', array('id', '=', $id));
		if(!empty($data->results()[0]->signature)){
			return $data->results()[0]->signature;
		} else {
		return "";
		}
	}
	
	public function getAvatar($id, $path) {
		$exts = array('gif','png','jpg');
		foreach($exts as $ext) {
			if(file_exists($path . "user/avatars/" . $id . "." . $ext)){
				$avatar_path = $path . "user/avatars/" . $id . "." . $ext;
				break;
			}
		}
		if(isset($avatar_path)){
			return $avatar_path;
		} else {
			return false;
		}
	}
	
	public function hasInfraction($user_id){
		$data = $this->_db->get('infractions', array('punished', '=', $user_id))->results();
		if(empty($data)){
			return false;
		} else {
			$return = array();
			$n = 0;
			foreach($data as $infraction){
				if($infraction->acknowledged == '0'){
					$return[$n]["id"] = $infraction->id;
					$return[$n]["staff"] = $infraction->staff;
					$return[$n]["reason"] = $infraction->reason;
					$return[$n]["date"] = $infraction->infraction_date;
					$n++;
				}
			}
			return $return;
		}
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}
	
	public function logout() {
		
		$this->_db->delete('users_session', array('user_id', '=', $this->data()->id));
		
		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
	}
	
	public function data() {
		return $this->_data;
	}
	
	public function isLoggedIn() {
		return $this->_isLoggedIn;
	}
	
}