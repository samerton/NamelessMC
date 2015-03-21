<?php
class Infractions {
	private $_db,
			$_data;
	
	public function __construct() {
		$this->_db = DB_Bungee::getInstance();
	}
	
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
	
	public function bat_getInfraction($type, $id) {
		if($type === "ban"){
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
	
}