<?php
define( 'MQ_SERVER_ADDR', $default_ip );
define( 'MQ_SERVER_PORT', $default_port );
define( 'MQ_TIMEOUT', 1 );

require('inc/integration/status/MinecraftServerPing.php');

$Timer = MicroTime( true );

$Info = false;
$Query = null;

try
{
	$Query = new MinecraftPing( $server_ip, $server_port, MQ_TIMEOUT );
	if(!$pre17){
		$Info = $Query->Query( );
	} else {
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

$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );

?>
