#!/usr/bin/php -q
<?php
//termi.lv
/////////////////////////////////////////////////////////////////
define( '_V', 1 );
/////////////////////////////////////////////////////////////////
require_once (dirname(__FILE__).'/../configuration.php');
require_once (dirname(__FILE__).'/../load/base.php');
require_once (dirname(__FILE__).'/../load/functions.php');
require_once (dirname(__FILE__).'/simple_html_dom.php');
mb_internal_encoding("UTF-8");

function arrToQuery($arr){
	foreach($arr as $key=>$value){
		$arr[$key] = ($key=='lat'|$key=='lng') ? "`$key`=$value" : "`$key`='$value'";
	}
	return $arr;
}
function arrToInsert($arr){
	$in = array();
	$values = array();
	foreach($arr as $key=>$value){
		$in[] = "`$key`";
		$values[] = $value=='NOW()' ? "$value" : "'$value'";
	}
	return "
		(".implode(', ', $in).")
		VALUES
		(".implode(', ', $values).")
	";
}
function trimText($str){
	$str = trim($str);
//	$str = preg_replace('/\h+/', ' ', $str);
	$str = str_replace(array('<br>', '<br />', '<br/>'), "\n", $str);
	$str = preg_replace('/\n\s+\n/', "\n", $str);
	$str = preg_replace('/\n+/', "\n", $str);
	return $str;
}
//var_dump(trimText('super
//<br />
//  <br /><br /><br /><br />asdf
//   Client Service Center<br><br>Brīvības
//
//
//
//
//
//  as dasdf
//
//duper')); die;
$db = new Mysql($dbHost, $dbUser, $dbPass, $dbName, $dbFix);
$elements = array(
	'type',
	'name',
	'address',////////////////////////////////
	'comment',
	'working',///////////////////////////////
	'phone',///////////////////////////////
	'img',///////////////////////////////
	'out',///////////////////////////////
	'in',///////////////////////////////
	'updated',
	'lat',
	'lng'
);
$xmls = array(
	array(
		'xml'=>'http://maps.google.com/maps/ms?ie=UTF8&hl=en&source=embed&msa=0&output=georss&msid=106243649824848070469.00047429199c9e1372aed'
		, 'type'=>'0'
		, 'bank'=>'1'
	)
	, array(
		'xml'=>'http://maps.google.com/maps/ms?ie=UTF8&oe=UTF8&msa=0&output=georss&msid=106243649824848070469.00047115d6d69715f30c3'
		, 'type'=>'1'
		, 'bank'=>'1'
	)
	, array(
		'xml'=>'http://maps.google.com/maps/ms?ie=UTF8&hl=lv&msa=0&output=georss&msid=111861497277574895668.00045d73335eb5fc56033'
//		'xml'=>'../datas/hansa.xml'
		, 'type'=>'0'
		, 'bank'=>'2'
	)
);
foreach($xmls as $params){
	$bankIds = $db->sqla("
		SELECT
			`id`
		FROM
			`#__atm`
		WHERE
			`bank_id`=".$params['bank']."
			AND `type`=".$params['type']."
	", 'id', true);
	$bankIds = array_keys($bankIds);
	$c = str_get_html(file_get_contents($params['xml']));
	foreach($c->find('item') as $b){
		$tmp = array();
		$description = $b->find('description', 0)->xmltext();
		$description = str_replace("\n", "<br />\n", trimText(strip_tags($description, '<a><br>')));
//		if(strpos($description, 'ATM')===false){
//			var_dump($description);
//			continue;
//		}
		foreach($elements as $e){
			$value = '';
			switch($e){
				case 'type':
					$value=$params['type'];
				break;
				case 'name':
					$value=mb_substr($b->find('title', 0)->innertext, 0, 256);
				break;
				case 'comment':
					$value=mb_substr($description, 0, 512);
					$value=$description;
				break;
				case 'updated':
					$value=date('Y-m-d H:i:s', strtotime($b->find('pubDate', 0)->innertext));
				break;
				case 'lat':
					$value=reset(explode(' ', trim($b->find('georss:point', 0)->innertext)));
				break;
				case 'lng':
					$value=end(explode(' ', trim($b->find('georss:point', 0)->innertext)));
				break;
			}
			$tmp[$e]=$value;
		}
		$tmp['bank_id']=$params['bank'];
		$id = $db->sqlf1("
			SELECT
				`id`
			FROM
				`#__atm`
			WHERE
				".implode(' AND ', arrToQuery($tmp))."
			LIMIT
				1
		");
		$query = $db->sqlget("
			SELECT
				`id`
			FROM
				`#__atm`
			WHERE
				".implode(' AND ', arrToQuery($tmp))."
			LIMIT
				1
		");
		$tmp['published'] = '1';
		$tmp['added'] = 'NOW()';
		if(empty($id)){
//			var_dump($query); die;
			$db->sqin("
				INSERT INTO
					`#__atm`
				".arrToInsert($tmp)."
			");
		} else {
			$index = array_search($id, $bankIds);
			unset($bankIds[$index]);
		}
	}
	if(count($bankIds)>0){
		$db->squp("
			UPDATE
				`#__atm`
			SET
				`published`='0'
			WHERE
				`id` IN(".implode(', ', $bankIds).")
		");
	}
}

