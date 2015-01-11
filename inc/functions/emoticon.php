<?php
function emoticon($string, $location){
	if($location === "forum"){
		$emoticon_path = "../inc/";
	} else if ($location === "news"){
		$emoticon_path = "inc/";
	}
	$string = str_replace(":alien:","<img src=\"" . $emoticon_path . "emoticons/alien.png\" />",$string);
	$string = str_replace(":angel:","<img src=\"" . $emoticon_path . "emoticons/angel.png\" />",$string);
	$string = str_replace(":angry:","<img src=\"" . $emoticon_path . "emoticons/angry.png\" />",$string);
	$string = str_replace("x(","<img src=\"" . $emoticon_path . "emoticons/angry.png\" />",$string);
	$string = str_replace("X(","<img src=\"" . $emoticon_path . "emoticons/angry.png\" />",$string);
	$string = str_replace(":blink:","<img src=\"" . $emoticon_path . "emoticons/blink.png\" />",$string);
	$string = str_replace(":blush:","<img src=\"" . $emoticon_path . "emoticons/blush.png\" />",$string);
	$string = str_replace(":cheerful:","<img src=\"" . $emoticon_path . "emoticons/cheerful.png\" />",$string);
	$string = str_replace(":cool:","<img src=\"" . $emoticon_path . "emoticons/cool.png\" />",$string);
	$string = str_replace("8)","<img src=\"" . $emoticon_path . "emoticons/cool.png\" />",$string);
	$string = str_replace("B)","<img src=\"" . $emoticon_path . "emoticons/cool.png\" />",$string);
	$string = str_replace(":cry:","<img src=\"" . $emoticon_path . "emoticons/cwy.png\" />",$string);
	$string = str_replace(":devil:","<img src=\"" . $emoticon_path . "emoticons/devil.png\" />",$string);
	$string = str_replace(":$","<img src=\"" . $emoticon_path . "emoticons/dizzy.png\" />",$string);
	$string = str_replace(":dizzy:","<img src=\"" . $emoticon_path . "emoticons/dizzy.png\" />",$string);
	$string = str_replace(":erm:","<img src=\"" . $emoticon_path . "emoticons/ermm.png\" />",$string);
	$string = str_replace(":getlost:","<img src=\"" . $emoticon_path . "emoticons/getlost.png\" />",$string);
	$string = str_replace(":grin:","<img src=\"" . $emoticon_path . "emoticons/grin.png\" />",$string);
	$string = str_replace(":D","<img src=\"" . $emoticon_path . "emoticons/grin.png\" />",$string);
	$string = str_replace(":happy:","<img src=\"" . $emoticon_path . "emoticons/happy.png\" />",$string);
	$string = str_replace(":heart:","<img src=\"" . $emoticon_path . "emoticons/heart.png\" />",$string);
	$string = str_replace(":*","<img src=\"" . $emoticon_path . "emoticons/kissing.png\" />",$string);
	$string = str_replace(":kiss:","<img src=\"" . $emoticon_path . "emoticons/kissing.png\" />",$string);
	$string = str_replace(":laugh:","<img src=\"" . $emoticon_path . "emoticons/laughing.png\" />",$string);
	$string = str_replace(":ninja:","<img src=\"" . $emoticon_path . "emoticons/ninja.png\" />",$string);
	$string = str_replace(":pinch:","<img src=\"" . $emoticon_path . "emoticons/pinch.png\" />",$string);
	$string = str_replace(">_<","<img src=\"" . $emoticon_path . "emoticons/pinch.png\" />",$string);
	$string = str_replace(":|","<img src=\"" . $emoticon_path . "emoticons/pouty.png\" />",$string);
	$string = str_replace(":straightface:","<img src=\"" . $emoticon_path . "emoticons/pouty.png\" />",$string);
	$string = str_replace(":(","<img src=\"" . $emoticon_path . "emoticons/sad.png\" />",$string);
	$string = str_replace(":sad:","<img src=\"" . $emoticon_path . "emoticons/sad.png\" />",$string);
	$string = str_replace(":o","<img src=\"" . $emoticon_path . "emoticons/shocked.png\" />",$string);
	$string = str_replace(":O","<img src=\"" . $emoticon_path . "emoticons/shocked.png\" />",$string);
	$string = str_replace(":shocked:","<img src=\"" . $emoticon_path . "emoticons/shocked.png\" />",$string);
	$string = str_replace(":#","<img src=\"" . $emoticon_path . "emoticons/sick.png\" />",$string);
	$string = str_replace(":sick:","<img src=\"" . $emoticon_path . "emoticons/sick.png\" />",$string);
	$string = str_replace(":sideways:","<img src=\"" . $emoticon_path . "emoticons/sideways.png\" />",$string);
	$string = str_replace(":silly:","<img src=\"" . $emoticon_path . "emoticons/silly.png\" />",$string);
	$string = str_replace(":zzz:","<img src=\"" . $emoticon_path . "emoticons/sleeping.png\" />",$string);
	$string = str_replace(":sleep:","<img src=\"" . $emoticon_path . "emoticons/sleeping.png\" />",$string);
	$string = str_replace(":)","<img src=\"" . $emoticon_path . "emoticons/smile.png\" />",$string);
	$string = str_replace(":smile:","<img src=\"" . $emoticon_path . "emoticons/smile.png\" />",$string);
	$string = str_replace(":P","<img src=\"" . $emoticon_path . "emoticons/tongue.png\" />",$string);
	$string = str_replace(":p","<img src=\"" . $emoticon_path . "emoticons/tongue.png\" />",$string);
	$string = str_replace(":tongue:","<img src=\"" . $emoticon_path . "emoticons/tongue.png\" />",$string);
	$string = str_replace(":unsure:","<img src=\"" . $emoticon_path . "emoticons/unsure.png\" />",$string);
	$string = str_replace(":woot:","<img src=\"" . $emoticon_path . "emoticons/w00t.png\" />",$string);
	$string = str_replace(":wut:","<img src=\"" . $emoticon_path . "emoticons/wassat.png\" />",$string);
	$string = str_replace(":whistle:","<img src=\"" . $emoticon_path . "emoticons/whistling.png\" />",$string);
	$string = str_replace(";)","<img src=\"" . $emoticon_path . "emoticons/wink.png\" />",$string);
	$string = str_replace(":wink:","<img src=\"" . $emoticon_path . "emoticons/wink.png\" />",$string);
	$string = str_replace(":wub:","<img src=\"" . $emoticon_path . "emoticons/wub.png\" />",$string);
	return $string;
}