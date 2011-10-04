<?php
//termi.lv
/////////////////////////////////////////////////////////////////
defined( '_V' ) or die( 'Restricted access' );
/////////////////////////////////////////////////////////////////
Class ATMs
{
	var $el;
	var $elOnMap = 20;
	var $types = array();
	var $banks = array();
	var $coordinates = array();
	/////////////////////////////////////////////////////////////////
	function ATMs($db)
	{
		$this->_db = $db;
	}
	/////////////////////////////////////////////////////////////////
	function setType($types)
	{
		if($types==''){
			return false;
		}
		$this->types = array();
		foreach(explode(',',$types) as $t){
			$this->types[]=(int)$t;
		}
		return true;
	}
	/////////////////////////////////////////////////////////////////
	function setBank($types)
	{
		if($types==''){
			return false;
		}
		$this->banks = array();
		foreach(explode(',',$types) as $t){
			$this->banks[]=(int)$t;
		}
		return true;
	}
	/////////////////////////////////////////////////////////////////
	function setCoordinates($c)
	{
		$coor = explode(',', $c);
		if(count($coor)!=3){
			return false;
		}
		$coor[0]=(int)$coor[0];
		$this->coordinates = $coor;
		return true;
	}
	/////////////////////////////////////////////////////////////////
	function load()
	{
		$lat = $this->_db->sqst($this->coordinates[1]);
		$lng = $this->_db->sqst($this->coordinates[2]);
		$this->el = $this->_db->sqla("
			SELECT
				a.`id`, a.`bank_id` AS `bank`, a.`type`, a.`name`, a.`address`, a.`comment`, a.`working`, a.`phone`, a.`out`, a.`in`, a.`lat`, a.`lng`
				, ((ACOS(SIN($lat * PI() / 180) * SIN(a.`lat` * PI() / 180) + COS($lat * PI() / 180) * COS(a.`lat` * PI() / 180) * COS(($lng - a.`lng`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
				 AS `distance`

			FROM
				`#__atm` AS a
			WHERE
				a.`published`=1
			".(count($this->types)>0 ? "
				AND a.`type` IN (".implode(',', $this->types).")
			" : '')."
			".(count($this->banks)>0 ? "
				AND a.`bank_id` IN (".implode(',', $this->banks).")
			" : '')."
			ORDER BY
				`distance` ASC
			LIMIT
				$this->elOnMap
		");
//		var_dump($this->el);
		/*UNION ALL
				(SELECT
					a.`id`, a.`bank_id` AS `bank`, a.`type`, a.`name`, a.`address`, a.`comment`, a.`working`, a.`phone`, a.`out`, a.`in`, a.`lat`, a.`lng`
					, ((ACOS(SIN($lat * PI() / 180) * SIN(a.`lat` * PI() / 180) + COS($lat * PI() / 180) * COS(a.`lat` * PI() / 180) * COS(($lng - a.`lng`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
					 AS `distance`

				FROM
					`#__atm` AS a
				WHERE
					a.`published`=1
				".(count($this->types)>0 ? "
					AND a.`type` IN (".implode(',', $this->types).")
				" : '')."
				".(count($this->banks)>0 ? "
					AND a.`bank_id` IN (".implode(',', $this->banks).")
				" : '')."
				".(count($this->coordinates)==3 ? "
					AND a.`lat`>'$lat'
					AND a.`lng`<='$lng'
				" : '')."

				ORDER BY
					a.`lat` DESC,
					a.`lng` ASC
				LIMIT
					$this->elOnMap )
			UNION ALL
				(SELECT
					a.`id`, a.`bank_id` AS `bank`, a.`type`, a.`name`, a.`address`, a.`comment`, a.`working`, a.`phone`, a.`out`, a.`in`, a.`lat`, a.`lng`
					, ((ACOS(SIN($lat * PI() / 180) * SIN(a.`lat` * PI() / 180) + COS($lat * PI() / 180) * COS(a.`lat` * PI() / 180) * COS(($lng - a.`lng`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
					 AS `distance`

				FROM
					`#__atm` AS a
				WHERE
					a.`published`=1
				".(count($this->types)>0 ? "
					AND a.`type` IN (".implode(',', $this->types).")
				" : '')."
				".(count($this->banks)>0 ? "
					AND a.`bank_id` IN (".implode(',', $this->banks).")
				" : '')."
				".(count($this->coordinates)==3 ? "
					AND a.`lat`<='$lat'
					AND a.`lng`<'$lng'
				" : '')."

				ORDER BY
					a.`lat` ASC,
					a.`lng` ASC
				LIMIT
					$this->elOnMap )
			UNION ALL
				(SELECT
					a.`id`, a.`bank_id` AS `bank`, a.`type`, a.`name`, a.`address`, a.`comment`, a.`working`, a.`phone`, a.`out`, a.`in`, a.`lat`, a.`lng`
					, ((ACOS(SIN($lat * PI() / 180) * SIN(a.`lat` * PI() / 180) + COS($lat * PI() / 180) * COS(a.`lat` * PI() / 180) * COS(($lng - a.`lng`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
					 AS `distance`

				FROM
					`#__atm` AS a
				WHERE
					a.`published`=1
				".(count($this->types)>0 ? "
					AND a.`type` IN (".implode(',', $this->types).")
				" : '')."
				".(count($this->banks)>0 ? "
					AND a.`bank_id` IN (".implode(',', $this->banks).")
				" : '')."
				".(count($this->coordinates)==3 ? "
					AND a.`lat`<'$lat'
					AND a.`lng`>='$lng'
				" : '')."

				ORDER BY
					a.`lat` ASC,
					a.`lng` DESC
				LIMIT
					$this->elOnMap )
				ORDER BY
					`distance` ASC*/
	}
	/////////////////////////////////////////////////////////////////
	function get()
	{
		return $this->el;
	}
	/////////////////////////////////////////////////////////////////
}
/////////////////////////////////////////////////////////////////