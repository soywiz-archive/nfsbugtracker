<?php

class Db extends PDO {
	function __construct($dsn) {
		parent::__construct($dsn);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function q($sql, $params) {
		$stm = $this->prepare($sql);
		$stm->execute($params);
		return $stm;
	}
	
	public function getTableFields($tableName) {
		$fields = array();
		foreach ($this->q("SELECT * FROM sqlite_master WHERE type=? AND name=?;", array('table', $tableName)) as $row) {
			$sql = $row['sql'];
			list(, $params) = explode('(', $sql, 2);
			$params = preg_split('@,\\s*@', $params);
			foreach ($params as $param) {
				preg_match('@^\\w+@', $param, $matches);
				$fields[] = $matches[0];
			}
		}
		return $fields;
	}
}
