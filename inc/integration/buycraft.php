<?php
/*
 *  Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  MIT License
 */ 
if(!isset($queries)){
	$queries = new Queries();
}
$API = $queries->getWhere("settings", array("name", "=", "buycraft_key"))[0]->value;
 
$buycraft = file_get_contents('https://api.buycraft.net/v3?action=payments&secret='.$API);

$buycraft = (json_decode($buycraft, true));
?>