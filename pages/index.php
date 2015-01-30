<?php
/* 
 *	Made by Samerton
 *  http://worldscapemc.co.uk
 *
 *  License: MIT
 */

require('inc/includes/password.php');

$hash = password_hash('password' . 'asd98023592835uq98aef', PASSWORD_BCRYPT, array("cost" => 13));

$length = 32;

echo strlen($hash) . '<br />';

if(password_verify('password' . 'asd98023592835uq98aef', $hash)){
	echo 'Valid';
} else {
	echo 'Invalid';
}

?>