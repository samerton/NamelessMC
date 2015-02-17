<?php
class ServerStatus {
	public function serverPlay($server_ip, $server_port, $server_name){
		$Info = false;
		$Query = null;

		try
		{
			$Query = new MinecraftPing( $server_ip, $server_port, MQ_TIMEOUT );
			
			$Info = $Query->Query( );
			
			if( $Info === false )
			{
				/*
				 * If this server is older than 1.7, we can try querying it again using older protocol
				 * This function returns data in a different format, you will have to manually map
				 * things yourself if you want to match 1.7's output
				 *
				 * If you know for sure that this server is using an older version,
				 * you then can directly call QueryOldPre17 and avoid Query() and then reconnection part
				 */
				
				$Query->Close( );
				$Query->Connect( );
				
				$Info = $Query->QueryOldPre17( );
			}
		}
		catch( MinecraftPingException $e )
		{
			$Exception = $e;
		}

		if( $Query !== null )
		{
			$Query->Close( );
		}

		if($Info['players']['online'] == 0) {
			echo'<div class="alert alert-warning">There are no players online!</div>';
		} else {
			if($Info['players']['online'] > 12){
				$extra = ($Info['players']['online'] - 12);
				$max = 12;
			} else {
				$max = $Info['players']['online'];
			}
			for ($row = 0; $row < $max; $row++) {
				echo '<span rel="tooltip" data-trigger="hover" data-original-title="'.$Info['players']['sample'][$row]['name'].'"><a href="/profile/' . $Info['players']['sample'][$row]['name'] . '"><img src="https://cravatar.eu/avatar/' .$Info['players']['sample'][$row]['name'] . '/50.png" style="width: 40px; height: 40px; margin-bottom: 5px; margin-left: 5px; border-radius: 3px;" /></a></span>';
			}
			if(isset($extra)){
				echo ' <span class="label label-info">And ' . $extra . ' more</span>';
			}
		}
	}
	public function isOnline($server_ip, $server_port, $player_name){
		$Info = false;
		$Query = null;

		try
		{
			$Query = new MinecraftPing( $server_ip, $server_port, MQ_TIMEOUT );
			
			$Info = $Query->Query( );
			
			if( $Info === false )
			{
				/*
				 * If this server is older than 1.7, we can try querying it again using older protocol
				 * This function returns data in a different format, you will have to manually map
				 * things yourself if you want to match 1.7's output
				 *
				 * If you know for sure that this server is using an older version,
				 * you then can directly call QueryOldPre17 and avoid Query() and then reconnection part
				 */
				
				$Query->Close( );
				$Query->Connect( );
				
				$Info = $Query->QueryOldPre17( );
			}
		}
		catch( MinecraftPingException $e )
		{
			$Exception = $e;
		}

		if( $Query !== null )
		{
			$Query->Close( );
		}

		if($Info['players']['online'] == 0) {
			return false;
		} else {
			for ($row = 0; $row <  $Info['players']['online']; $row++) {
				if(in_array($player_name, $Info['players']['sample'][$row])){
					return true;
					break;
				}
			}
		}
	}
}
?>
