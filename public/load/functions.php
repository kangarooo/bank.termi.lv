<?php
//termi.lv
/////////////////////////////////////////////////////////////////
defined( '_V' ) or die( 'Restricted access' );
/////////////////////////////////////////////////////////////////
function lng($arg, $transArg = null){
    global $lang;
    if (isset($lang[$arg])){
        $translate = $lang[$arg];
    } else {
		$translate = $arg;
	}
	if(!empty($transArg)){
		foreach($transArg as $id=>$value){
			$translate = str_replace($id, $value, $translate);
		}
	}
    return $translate;
}