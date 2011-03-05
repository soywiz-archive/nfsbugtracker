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
	
	public function getTableIndexes($tableName) {
		$indexes = array();
		foreach ($this->q("SELECT * FROM sqlite_master WHERE type=? AND tbl_name=?;", array('index', $tableName)) as $row) {
			$sql = $row['sql'];
			preg_match('@^CREATE\\s+((UNIQUE\\s+)?INDEX)@msi', $sql, $matches);
			$indexes[$row['name']] = $matches[1];
		}
		return $indexes;
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
	
	public function updateTableSchema($table, $propertiesInfo, $indexes) {
		$tableTemp = $table . 'Temp';

		$properties = array();
		foreach ($propertiesInfo as $propertyName => $propertyInfo) {
			$properties[] = $propertyName;
		}
		
		
		$this->beginTransaction();
		{
			$this->query('DROP TABLE IF EXISTS ' . $tableTemp . ';');
			$this->query('CREATE TABLE ' . $tableTemp . '(' . implode(', ', $properties) . ');');
			
			foreach ($indexes as $index) {
				$this->query($sql = 'DROP INDEX IF EXISTS ' . $index->name . ';');
				$this->query($sql = 'CREATE ' . $index->type . ' ' . $index->name . ' ON ' . $tableTemp . '(' . implode(', ', $index->fields) . ');');
				//echo "$sql\n";
			}
			//echo $sql;
	
			$current_properties = $this->getTableFields($table);
			$required_properties = array_keys($propertiesInfo);
			
			$intersect_properties = array_intersect($required_properties, $current_properties);
			$intersect_properties_str = implode(',', $intersect_properties);
			
			try {
				$this->query('INSERT OR IGNORE INTO ' . $tableTemp . ' (' . $intersect_properties_str . ') SELECT ' . $intersect_properties_str . ' FROM ' . $table . ';');
			} catch (Exception $e) {
				
			}
			
			$this->query('DROP TABLE IF EXISTS ' . $table . ';');
			$this->query('ALTER TABLE ' . $tableTemp . ' RENAME TO ' . $table . ';');
		}
		$this->commit();
	}
}
