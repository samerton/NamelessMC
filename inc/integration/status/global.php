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
	$Query = new MinecraftPing( MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_TIMEOUT );
	
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

$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );

?>
