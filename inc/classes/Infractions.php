<?php
class Infractions {
	private $_db,
			$_data;
	
	public function __construct() {
		$this->_db = DB_Bungee::getInstance();
	}
	
	// Receive a list of all infractions for BungeeAdminTools, either for a single user or for all users
	// Params: $uuid (string), UUID of a user. If null, will list all infractions
	public function bat_getAllInfractions($uuid = null) {
		if($uuid !== null){
			$field = "uuid";
			$symbol = "=";
			$equals = $uuid;
		} else {
			$field = "uuid";
			$symbol = "<>";
			$equals = "0";
		}
		$bans = $this->_db->get('BAT_ban', array($field, $symbol, $equals))->results();
		$kicks = $this->_db->get('BAT_kick', array($field, $symbol, $equals))->results();
		$mutes = $this->_db->get('BAT_mute', array($field, $symbol, $equals))->results();
		
		$results = array();
		$i = 0;
		
		foreach($bans as $ban){
			$results[$i]["id"] = $ban->ban_id;
			$results[$i]["uuid"] = $ban->UUID;
			$results[$i]["staff"] = htmlspecialchars($ban->ban_staff);
			$results[$i]["issued"] = strtotime($ban->ban_begin);
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", strtotime($ban->ban_begin));
			if($ban->ban_reason !== null){
				$results[$i]["reason"] = htmlspecialchars($ban->ban_reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			if($ban->ban_unbandate !== null){
				$results[$i]["unbanned"] = "true";
				$results[$i]["unbanned_by"] = htmlspecialchars($ban->ban_unbanstaff);
				$results[$i]["unbanned_date"] = htmlspecialchars($ban->ban_unbandate);
				if($ban->ban_unbanreason !== "noreason"){
					$results[$i]["unbanned_reason"] = htmlspecialchars($ban->ban_unbanreason);
				}
			}
			if($ban->ban_end !== null){
				$results[$i]["type"] = "temp_ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Temp Ban</span>";
				if($ban->ban_state == 0){
					$results[$i]["expires"] = strtotime($ban->ban_end);
					$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expired: " . date("jS M Y", strtotime($ban->ban_end)) . "\">Expired</span>";
				} else {
					$results[$i]["expires"] = strtotime($ban->ban_end);
					$results[$i]["expires_human"] = "<span class=\"label label-danger\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", strtotime($ban->ban_end)) . "\">Active</span>";
				}
			} else {
				$results[$i]["type"] = "ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}
			$i++;
		}
		
		foreach($kicks as $kick){
			$results[$i]["id"] = $kick->kick_id;
			$results[$i]["uuid"] = $kick->UUID;
			$results[$i]["staff"] = htmlspecialchars($kick->kick_staff);
			$results[$i]["issued"] = strtotime($kick->kick_date);
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", strtotime($kick->kick_date));
			$results[$i]["reason"] = htmlspecialchars($kick->kick_reason);
			$results[$i]["type"] = "kick";
			$results[$i]["type_human"] = "<span class=\"label label-primary\">Kick</span>";
			$results[$i]["expires_human"] = "n/a";
			$i++;
		}
		
		foreach($mutes as $mute){
			$results[$i]["id"] = $mute->mute_id;
			$results[$i]["uuid"] = $mute->UUID;
			$results[$i]["staff"] = htmlspecialchars($mute->mute_staff);
			$results[$i]["issued"] = strtotime($mute->mute_begin);
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", strtotime($mute->mute_begin));
			if($mute->mute_reason !== null){
				$results[$i]["reason"] = htmlspecialchars($mute->mute_reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			$results[$i]["type"] = "mute";
			$results[$i]["type_human"] = "<span class=\"label label-warning\">Mute</span>";
			if($mute->mute_unmutedate !== null){
				$results[$i]["unmuted"] = "true";
				$results[$i]["unmuted_by"] = htmlspecialchars($mute->mute_unmutestaff);
				$results[$i]["unmuted_date"] = htmlspecialchars($mute->mute_unmutedate);
				if($mute->mute_unmutereason !== "noreason"){
					$results[$i]["unmuted_reason"] = htmlspecialchars($mute->mute_unmutereason);
				}
			}
			if($mute->mute_end !== null){
				if($mute->mute_state == 0){
					$results[$i]["expires"] = strtotime($mute->mute_end);
					$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expired: " . date("jS M Y", strtotime($mute->mute_end)) . "\">Expired</span>";
				} else {
					$results[$i]["expires"] = strtotime($mute->mute_end);
					$results[$i]["expires_human"] = "<span class=\"label label-danger\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", strtotime($mute->mute_end)) . "\">Active</span>";
				}
			} else {
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}
			$i++;
		}

		function date_compare($a, $b)
		{
			$t1 = $a['issued'];
			$t2 = $b['issued'];
			return $t2 - $t1;
		}    
		usort($results, 'date_compare');
		return $results;
	}
	
	// Receive an object containing infraction information for a specified infraction ID and type (BungeeAdminTools)
	// Params: $type (string), either ban, kick or mute; $id (int), ID of infraction
	public function bat_getInfraction($type, $id) {
		if($type === "ban" || $type === "temp_ban"){
			$result = $this->_db->get('BAT_ban', array("ban_id", "=", $id))->results();
			return $result;
		} else if($type === "kick"){
			$result = $this->_db->get('BAT_kick', array("kick_id", "=", $id))->results();
			return $result;
		} else if($type === "mute"){
			$result = $this->_db->get('BAT_mute', array("mute_id", "=", $id))->results();
			return $result;
		}
		return false;
	}

	// Receive a list of all infractions for Ban Management, either for a single user or for all users
	// Params: $uuid (string), UUID of a user. If null, will list all infractions
	public function bm_getAllInfractions($uuid = null) {
		// First, we need to get the player ID (if specified)
		if($uuid !== null){
			$field = "player_id";
			$symbol = "=";
			$equals = pack("H*", str_replace('-', '', $uuid));
		} else {
			$field = "player_id";
			$symbol = "<>";
			$equals = "0";
		}
		$bans = $this->_db->get('bm_player_bans', array($field, $symbol, $equals))->results();
		$kicks = $this->_db->get('bm_player_kicks', array($field, $symbol, $equals))->results();
		$mutes = $this->_db->get('bm_player_mutes', array($field, $symbol, $equals))->results();
		$warnings = $this->_db->get('bm_player_warnings', array($field, $symbol, $equals))->results();
		
		$results = array();
		$i = 0;
		
		// Bans - first, current bans
		foreach($bans as $ban){
			$results[$i]["id"] = $ban->id;
			$results[$i]["uuid"] = bin2hex($ban->player_id);
			
			// Console or a player?
			if(bin2hex($ban->actor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $ban->actor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = $ban->created;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $ban->created);
			
			// Is a reason set?
			if($ban->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($ban->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			// Is it a temp-ban?
			if($ban->expires != 0){
				$results[$i]["type"] = "temp_ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Temp Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", $ban->expires) . "\">Active</span>";
				$results[$i]["expires"] = $ban->expires;
			} else {
				$results[$i]["type"] = "ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}
			$i++;
		}
		// Bans - next, previous bans
		$bans = $this->_db->get('bm_player_ban_records', array($field, $symbol, $equals))->results();
		foreach($bans as $ban){
			$results[$i]["id"] = $ban->id;
			$results[$i]["uuid"] = bin2hex($ban->player_id);
			
			// Console or a player?
			if(bin2hex($ban->pastActor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $ban->pastActor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = $ban->pastCreated;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $ban->pastCreated);
			
			// Is a reason set?
			if($ban->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($ban->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			// Was it a temp-ban?
			if($ban->expired != 0){
				$results[$i]["type"] = "temp_ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Temp Ban</span>";
				$results[$i]["expires"] = strtotime($ban->expired);
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expired: " . date("jS M Y", $ban->expired) . "\">Expired</span>";
			} else {
				$results[$i]["type"] = "ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\">Unbanned</span>";
			}
			$i++;
		}
		
		// Kicks
		foreach($kicks as $kick){
			$results[$i]["id"] = $kick->id;
			$results[$i]["uuid"] = bin2hex($kick->player_id);
			
			// Console or a player?
			if(bin2hex($kick->actor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $kick->actor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = $kick->created;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $kick->created);
			$results[$i]["reason"] = htmlspecialchars($kick->reason);
			$results[$i]["type"] = "kick";
			$results[$i]["type_human"] = "<span class=\"label label-primary\">Kick</span>";
			$results[$i]["expires_human"] = "n/a";
			$i++;
		}
		
		// Mutes - first, current mutes
		foreach($mutes as $mute){
			$results[$i]["id"] = $mute->id;
			$results[$i]["uuid"] = bin2hex($mute->player_id);
			
			// Console or a player?
			if(bin2hex($mute->actor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $mute->actor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = $mute->created;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $mute->created);
			
			// Is a reason set?
			if($mute->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($mute->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			$results[$i]["type"] = "mute";
			$results[$i]["type_human"] = "<span class=\"label label-warning\">Mute</span>";
			
			// Is it a temp mute?
			if($mute->expires != 0){
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", $mute->expires) . "\">Active</span>";
				$results[$i]["expires"] = $mute->expires;
			} else {
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}

			$i++;
		}
		
		// Mutes - next, previous mutes
		$mutes = $this->_db->get('bm_player_mute_records', array($field, $symbol, $equals))->results();
		foreach($mutes as $mute){
			$results[$i]["id"] = $mute->id;
			$results[$i]["uuid"] = bin2hex($mute->player_id);
			
			// Console or a player?
			if(bin2hex($mute->pastActor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $mute->pastActor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = strtotime($mute->created);
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", strtotime($mute->created));
			
			// Is a reason set?
			if($mute->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($mute->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			$results[$i]["type"] = "mute";
			$results[$i]["type_human"] = "<span class=\"label label-warning\">Mute</span>";
			
			// Was it a temp-ban?
			if($mute->expired != 0){
				$results[$i]["expires"] = strtotime($mute->expired);
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expired: " . date("jS M Y", $mute->expired) . "\">Expired</span>";
			} else {
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Mute</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\">Unmuted</span>";
			}
			$i++;
		}
		
		// Warnings
		foreach($warnings as $warning){
			$results[$i]["id"] = $warning->id;
			$results[$i]["uuid"] = bin2hex($warning->player_id);
			
			// Console or a player?
			if(bin2hex($warning->actor_id) == '2b28d0bed7484b93968e8f4ab16999b3'){
				$results[$i]["staff"] = 'Console';
			} else {
				// We need to get the player's username first
				$username = $this->_db->get('bm_players', array('id', '=', $warning->actor_id))->results();
				$username = htmlspecialchars($username[0]->name);
				$results[$i]["staff"] = $username;
			}
			
			$results[$i]["issued"] = $warning->created;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $warning->created);
			$results[$i]["reason"] = htmlspecialchars($warning->reason);
			$results[$i]["type"] = "warning";
			$results[$i]["type_human"] = "<span class=\"label label-info\">Warning</span>";
			$results[$i]["expires_human"] = "n/a";
			$i++;
		}

		// Order by date, most recent first
		function date_compare($a, $b)
		{
			$t1 = $a['issued'];
			$t2 = $b['issued'];
			return $t2 - $t1;
		}    
		usort($results, 'date_compare');
		return $results;
	}
	
	// Receive an object containing infraction information for a specified infraction ID and type (Ban Management)
	// Params: $type (string), either ban, kick or mute; $id (int), ID of infraction
	public function bm_getInfraction($type, $id) {
		if($type === "ban" || $type === "temp_ban"){
			$result = $this->_db->get('bm_player_bans', array("id", "=", $id))->results();
			if(!count($result)){
				// unbanned or expired?
				$result = $this->_db->get('bm_player_ban_records', array("id", "=", $id))->results();
			}
			return $result;
		} else if($type === "kick"){
			$result = $this->_db->get('bm_player_kicks', array("id", "=", $id))->results();
			return $result;
		} else if($type === "mute"){
			$result = $this->_db->get('bm_player_mutes', array("id", "=", $id))->results();
			if(!count($result)){
				// unmuted or expired?
				$result = $this->_db->get('bm_player_mute_records', array("id", "=", $id))->results();
			}
			return $result;
		} else if($type === "warning"){
			$result = $this->_db->get('bm_player_warnings', array("id", "=", $id))->results();
			return $result;
		}
		return false;
	}
	
	// Receive the username from an ID (Ban Management)
	// Params: $id (string (binary)), player_id of user to lookup
	public function bm_getUsernameFromID($id) {
		$result = $this->_db->get('bm_players', array('id', '=', $id))->results();
		if(count($result)){
			return htmlspecialchars($result[0]->name);
		}
		return false;
	}
	
	// Receive a list of all infractions for MaxBans, either for a single user or for all users
	// Params: $name (string), ingame name of a user. If null, will list all infractions
	public function mb_getAllInfractions($name = null) {
		if($name !== null){
			$field = "name";
			$symbol = "=";
			$equals = $name;
		} else {
			$field = "name";
			$symbol = "<>";
			$equals = "0";
		}
		$bans = $this->_db->get('bans', array($field, $symbol, $equals))->results();
		$mutes = $this->_db->get('mutes', array($field, $symbol, $equals))->results();
		$warnings = $this->_db->get('warnings', array($field, $symbol, $equals))->results();
		
		$results = array();
		$i = 0;
		
		// Bans
		foreach($bans as $ban){
			// get username
			$username = $this->_db->get('players', array('name', '=', $ban->name))->results();
			$results[$i]["id"] = htmlspecialchars($username[0]->actual) . '.' .  $ban->time;
			$results[$i]["staff"] = htmlspecialchars($ban->banner);
			
			$results[$i]["issued"] = $ban->time / 1000;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $ban->time / 1000);
			
			// Is a reason set?
			if($ban->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($ban->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			// Is it a temp-ban?
			if($ban->expires != 0){
				$results[$i]["type"] = "temp_ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Temp Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", $ban->expires / 1000) . "\">Active</span>";
				$results[$i]["expires"] = $ban->expires;
			} else {
				$results[$i]["type"] = "ban";
				$results[$i]["type_human"] = "<span class=\"label label-danger\">Ban</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}
			$i++;
		}
		
		// Mutes
		foreach($mutes as $mute){
			// get username
			$username = $this->_db->get('players', array('name', '=', $mute->name))->results();
			$results[$i]["id"] = htmlspecialchars($username[0]->actual) . '.' .  $mute->time;
			$results[$i]["staff"] = htmlspecialchars($mute->muter);
			
			$results[$i]["issued"] = $mute->time / 1000;
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", $mute->time / 1000);
			
			// Is a reason set?
			if($mute->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($mute->reason);
			} else {
				$results[$i]["reason"] = "-";
			}
			
			// Is it a temp-mute?
			if($mute->expires != 0){
				$results[$i]["type"] = "mute";
				$results[$i]["type_human"] = "<span class=\"label label-warning\">Mute</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", $mute->expires / 1000) . "\">Active</span>";
				$results[$i]["expires"] = $mute->expires;
			} else {
				$results[$i]["type"] = "mute";
				$results[$i]["type_human"] = "<span class=\"label label-warning\">Mute</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-danger\">Permanent</span>";
			}
			$i++;
		}
		
		// Warnings
		foreach($warnings as $warning){
			// get username
			$username = $this->_db->get('players', array('name', '=', $warning->name))->results();
			$results[$i]["id"] = htmlspecialchars($username[0]->actual) . '.' .  $warning->expires;
			$results[$i]["staff"] = htmlspecialchars($warning->banner);
			
			$results[$i]["issued"] = strtotime('-3 days', $warning->expires / 1000);
			$results[$i]["issued_human"] = date("jS M Y, H:i:s", strtotime('-3 days', $warning->expires / 1000));
			
			// Is a reason set?
			if($warning->reason !== null){
				$results[$i]["reason"] = htmlspecialchars($warning->reason);
			} else {
				$results[$i]["reason"] = "-";
			}

			if($warning->expires != 0){
				$results[$i]["type"] = "warning";
				$results[$i]["type_human"] = "<span class=\"label label-info\">Warning</span>";
				$results[$i]["expires_human"] = "<span class=\"label label-success\" rel=\"tooltip\" data-trigger=\"hover\" data-original-title=\"Expires: " . date("jS M Y", $warning->expires / 1000) . "\">Active</span>";
				$results[$i]["expires"] = $warning->expires;
			}
			$i++;
		}

		// Order by date, most recent first
		function date_compare($a, $b)
		{
			$t1 = $a['issued'];
			$t2 = $b['issued'];
			return $t2 - $t1;
		}    
		usort($results, 'date_compare');
		return $results;
	}
	
	// Receive an object containing infraction information for a specified infraction ID and type (MaxBans)
	// Params: $type (string), either ban, kick or mute; $id (int), ID of infraction (contains both name and time)
	public function mb_getInfraction($type, $id) {
		// explode the ID to get name and time
		$name = explode('.', $id);
		$time = $name[1];
		$name = $name[0];
		
		// return false by default
		$return = false;
		
		if($type === "ban" || $type === "temp_ban"){
			$results = $this->_db->get('bans', array("time", "=", $time))->results();
			foreach($results as $result){
				if($result->name == strtolower($name)){
					$return = $result;
					break;
				}
			}
			return $return;
		} else if($type === "mute"){
			$results = $this->_db->get('mutes', array("time", "=", $time))->results();
			foreach($results as $result){
				if($result->name == strtolower($name)){
					$return = $result;
					break;
				}
			}
			return $return;
		} else if($type === "warning"){
			$results = $this->_db->get('warnings', array("expires", "=", $time))->results();
			foreach($results as $result){
				if($result->name == strtolower($name)){
					$return = $result;
					break;
				}
			}
			return $return;
		}
		return false;
	}
	
	// Receive the username from a lower case name (MaxBans)
	// Params: $name (string), player_id of user to lookup
	public function mb_getUsernameFromName($name) {
		$result = $this->_db->get('players', array('name', '=', $name))->results();
		if(count($result)){
			return htmlspecialchars($result[0]->actual);
		}
		return false;
	}
	
}