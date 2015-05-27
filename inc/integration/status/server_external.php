<?php
// External query
// Check cache

$cache->setCache($server->name . 'query_cache');

if(!$cache->isCached('query')){
	// Not cached, query the server
	// Use cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_URL, 'https://mcapi.us/server/status?ip=' . $server->ip . '&port=' . $server->port);
	
	// Execute
	$ret = curl_exec($ch);

	// Store in cache
	$cache->store('query', json_decode($ret, true), 5000);
	
	// Format the query
	$ret = json_decode($ret, true);
	
	if($ret['online'] != 1){
		echo '<span class="label label-danger">Offline</span>';
	} else {
		$Info = array(
			'players' => array(
				'online' => $ret['players']['now'],
				'max' => $ret['players']['max']
			)
		);
		echo '<span class="label label-success">Online</span> <strong>' . $Info['players']['online'] . '/' . $Info['players']['max'];
	}
	
} else {
	// Cached, don't query
	$query = $cache->retrieve('query');
	
	if($query['online'] != 1){
		echo '<span class="label label-danger">Offline</span>';
	} else {
		$Info = array(
			'players' => array(
				'online' => $query['players']['now'],
				'max' => $query['players']['max']
			)
		);
		echo '<span class="label label-success">Online</span> <strong>' . $Info['players']['online'] . '/' . $Info['players']['max'];
	}
}