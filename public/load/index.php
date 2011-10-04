<?php
//termi.lv
/////////////////////////////////////////////////////////////////
define( '_V', 1 );
/////////////////////////////////////////////////////////////////
//base class
require_once ('../configuration.php');
require_once ('functions.php');
require_once ('base.php');
require_once ('ATMs.php');


$db = new Mysql($dbHost, $dbUser, $dbPass, $dbName, $dbFix);
$atms = new ATMs($db);
$result = false;
$type = array_key_exists('l', $_GET) ? $_GET['l'] : false;
switch($type){
	case 'type':
		$result = array(
			'0'=>array(
				'name'=>'Rubļa svaidiejs'
				, 'active'=>true
			)
			, '1'=>array(
				'name'=>'Klientu servisi'
				, 'active'=>false
			)
		);
	break;
	case 'bank':
		$result = array(
			'1'=>array(
				'name'=>'lkb'
				, 'logo'=>'design/images/lkb.png'
				, 'active'=>true
			)
			,'2'=>array(
				'name'=>'swb'
				, 'logo'=>'design/images/swb.png'
				, 'active'=>true
			)
		);
	break;
	case 'mark':
		$params = array_key_exists('b', $_POST) ? $_POST['b'] : '';
		$params = explode(';',$params);
		if(isset($params[1])&&$atms->setType($params[1])&&isset($params[2])&&$atms->setBank($params[2])&&$atms->setCoordinates($params[0])){
			$atms->load();
			$result = $atms->get();
		}
	break;
	default:
//		$atms->load();
//		$result = $atms->get();
	break;
}
//header('Content-type: application/json');
echo json_encode($result);
