<?php

class Db extends PDO {
	function __construct($path) {
		$attr = array();
		if ($path != ':memory:') $attr[PDO::ATTR_PERSISTENT] = TRUE;
		parent::__construct('sqlite:' . $path, NULL, NULL, $attr);

		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// http://www.sqlite.org/pragma.html
		// Faster updates but much less safe.
		$this->query('PRAGMA synchronous = OFF;');
		//$this->query('PRAGMA synchronous = NORMAL;');
		//$this->query('PRAGMA synchronous = FULL;');
	}

	public function q($sql, $params = array(), $className = NULL) {
		$stm = $this->prepare($sql);
		if ($className !== NULL) $stm->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $className); 
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

	/**
	 * @param string $model
	 * @return DbModelQuery
	 */
	public function select($model) {
		return new DbModelQuery($this, $model);
	}
	
	public function update($table, $fields, $where) {
		$sets = array_map(function($v) { return "{$v}=?"; }, array_keys($fields));
		$wheres = array_map(function($v) { return "{$v}=?"; }, array_keys($where));
		$values = array_merge(array_values($fields), array_values($where));
		$this->q($sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . implode(', ', $wheres) . ';', $values);
	}
	
	public function insert($table, $fields) {
		$this->q($sql = 'INSERT INTO ' . $table . ' (' . implode(',', array_keys($fields)) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ');', array_values($fields));
	}
	
	public function delete($table, $where) {
		$wheres = array_map(function($v) { return "{$v}=?"; }, array_keys($where));
		$values = array_values($where);
		$this->q('DELETE FROM ' . $table . ' WHERE ' . implode(' AND ', $wheres) . ';', $values);
	}
}
