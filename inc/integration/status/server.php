<?php
class ServerStatus {
	public function serverPlay($server_ip, $server_port, $server_name, $pre17){
		$Info = false;
		$Query = null;
		try
		{
			$Query = new MinecraftPing( $server_ip, $server_port, MQ_TIMEOUT );
			if($pre17 == 0){
				$Info = $Query->Query( );
			} else {
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
        if($pre17 == 0){
            function startsWith($haystack, $needle) {
                return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
            }
            if(startsWith($Info['version']['name'], 'BungeeCord')){
                if($Info['players']['online'] == 0) {
                    echo'<div class="alert alert-warning">There are no players online!</div>';
                } else {
                    echo'<div class="alert alert-warning">There are ' . $Info['players']['online'] . ' players online!</div>';
                }
            } else {
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
        } else {
            if($Info['Players'] == 0) {
                echo'<div class="alert alert-warning">There are no players online!</div>';
            } else {
                echo'<div class="alert alert-warning">There are ' . $Info['Players'] . ' players online!</div>';
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
