<?php
//termi.lv
/////////////////////////////////////////////////////////////////
defined( '_V' ) or die( 'Restricted access' );
/////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function convert_to_string_mysql($s)
{
    return "'$s'";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function prefix_key_compare_item(&$item, $key, $p)
{
    $params = array();

    if(!is_array($p))
        $params[0] = $p;
    else
        $params = $p;
    if(!array_key_exists(1, $params))
        $params[1]=' ';
    $keyKompare = '';
    if(array_key_exists(2, $params)&&$params[2])
        $keyKompare = $item;
    else
        $keyKompare = $key;
    $item = $params[0] .".`$keyKompare`" . $params[1] . $item;
}
/////////////////////////////////////////////////////////////////

Class Mysql
{
	var $dbLink;
	var $dbFix;
	var $queryString;
	var $params=array();
	var $lastResource;
	// connect to mysql
	function Mysql($host,$user,$pass,$dbName,$fix)
	{
		$this->params=array(
			'host'=>$host
			,'user'=>$user
			,'pass'=>$pass
			,'dbName'=>$dbName
			,'fix'=>$fix
		);
		$this->connect();
		$this->queryString = '';
		$this->display_error();
	}
	//connect
	function connect($reconnect=false)
	{
		if($reconnect){
			mysql_close($this->dbLink);
		}
		$this->dbLink = mysql_connect($this->params['host'],$this->params['user'],$this->params['pass']);
		mysql_select_db($this->params['dbName'], $this->dbLink);
		$this->sql("SET NAMES UTF8");
		$this->dbFix = $this->params['fix'];
		$this->display_error();
	}
	// escape string
	function sqst($arg)
	{
		if(is_array($arg)){
			foreach($arg as $key=>$item){
				$newArray[$key] = $this->sqst($item);
			}
			return $newArray;
		}
		$return = mysql_real_escape_string($arg);
		return $return;
	}
	// send query and return resource
	function sql($str)
	{
		$str = str_replace('#__', $this->dbFix, $str);
		$this->queryString = $str;
		$this->lastResource = mysql_query($str, $this->dbLink);
		$this->display_error();
		return $this->lastResource;
	}
	// create executable query
	function sqlget($str)
	{
		return str_replace('#__', $this->dbFix, $str);
	}
	/////////////////////////////////////////////////////////
	//self fetch and return array
	function self_fetch_assoc()
	{
		$this->sql($this->queryString);
		$return_array = false;
		$rows=array();
		while($row=mysql_fetch_assoc($this->lastResource)){
			$rows[]=$row;
		}
		return $rows;
	}
	// execute query and return array
	function sqla($str, $ident_in = null, $unique = null)
	{
		$this->queryString = $str;
		$return_array = false;
		$rows = $this->self_fetch_assoc();
		 foreach ($rows as $row){
			$ident = null;
			if(isset($ident_in)){
				if(!is_array($ident_in)){
					$ident[0] = $ident_in;
				} else {
					$ident = $ident_in;
				}
			}
			if ($ident && (isset($ident[0]))){
				if(isset($ident[1])){
					if($unique){
						$return_array[$row[$ident[0]]][$row[$ident[1]]] = $row;
					} else {
						$return_array[$row[$ident[0]]][$row[$ident[1]]][] = $row;
					}
				} else {
					if($unique){
						$return_array[$row[$ident[0]]] = $row;
					} else {
						$return_array[$row[$ident[0]]][] = $row;
					}
				}
			} else {
				if($unique){
					$return_array[] = reset($row);
				} else {
					$return_array[] = $row;
				}
			}
		}
		return $return_array;
	}
	// get one value from base
	function sql1($str, $ident = null)
	{
		$this->queryString = $str;
		$return_array = reset($this->self_fetch_assoc());
		return $return_array;
	}
	//get an first value from base
	function sqlf1($str, $ident = null)
	{
		$oneRes = $this->sql1($str, $ident);
		if(empty($oneRes)){
			return false;
		}
		return reset($oneRes);
	}
	//insert values in base
	function sqin($str)
	{
		$resource = $this->sql($str);
		if($resource){
			return mysql_insert_id();
		}
		return $resource;
	}
	// update values in base
	function squp($str)
	{
		$resource = $this->sql($str);
		if($resource){
			return mysql_affected_rows($this->dbLink);
		}
		return $resource;
	}
	// update values in base with arrrays
	function squparray($f, $format)
	{
		if(is_array($f)){
			foreach($f as $fValue){
				list($field, $value) = each($fValue);
				$up[]="`$field`='".$this->sqst($value)."'";
			}
		} else {
			return;
		}
		$str=sprintf($format, implode(',',$up));
		return $this->squp($str);
	}
	//delete value from base
	function sqde($str)
	{
		$resource = $this->sql($str);
		if($resource) {
			return mysql_affected_rows($this->dbLink);
		}
		return $resource;
	}
	// display errors
	function display_error()
	{
		global $developmentArea;
		if(!mysql_error($this->dbLink)){
			return;
		}
		//if musql gone away, lets reconnect and resend query
		if(2006==mysql_errno($this->dbLink)){
			echo mysql_errno($this->dbLink) . ':' .mysql_error($this->dbLink) ."\n\n" . $this->queryString."\n We will try to reconnect \n";
			$this->connect(true);
			$this->lastResource = mysql_query($this->queryString, $this->dbLink);
			return;
		}
		if($developmentArea){
			echo mysql_errno($this->dbLink) . ':' .mysql_error($this->dbLink) ."\n\n" . $this->queryString."\n\n";
		} else {
			die(lng('Something goes wrong'));
		}
	}
}
