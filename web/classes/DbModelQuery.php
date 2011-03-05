<?php

class DbModelQuery implements IteratorAggregate {
	/**
	 * @var Db
	 */
	protected $db;
	protected $fields = 'ROWID, *';
	protected $model;
	
	/**
	 * @var DbModelQueryExpression
	 */
	protected $where;

	protected $sort = array();
	
	const GENERATE_MODE_NORMAL = 0;
	const GENERATE_MODE_COUNT  = 1;
	
	public function __construct($db, $model) {
		$this->db    = $db;
		$this->model = $model;
	}

	/**
	 * @return DbModelQuery
	 */
	public function where(DbModelQueryExpression $where) {
		$this->where = $where;
		
		return $this;
	}

	/**
	 * @param string $field
	 * @param ...
	 * 
	 * @return DbModelQuery
	 */
	public function sort($field) {
		$this->sort = array();
		foreach (func_get_args() as $sortField) {
			$sortDirection = +1;
			switch ($sign = substr($sortField, 0, 1)) {
				case '-': case '+':
					$sortDirection = ($sign == '+') ? +1 : -1;
					$sortField = substr($sortField, 1);
				break;
			}
			$this->sort[$sortField] = $sortDirection;
		}
		return $this;
	}

	/**
	 * @param GENERATE_MODE $mode
	 * @return string
	 */
	public function generateSQL($mode = DbModelQuery::GENERATE_MODE_NORMAL) {
		$sql  = 'SELECT ';
		if ($mode & DbModelQuery::GENERATE_MODE_COUNT) {
			$sql .= 'COUNT(*)';
		} else {
			$sql .= $this->fields;
		}
		$sql .= ' FROM ' . $this->model;
		if (!empty($this->where)) {
			$sql .= ' WHERE ' . $this->where->serialize($this->db);
		}
		if (!empty($this->sort)) {
			$sql .= ' ORDER BY ' . implode(', ', array_map(function($k, $v) { return $k . ' ' . (($v > 0) ? 'ASC' : 'DESC'); }, array_keys($this->sort), array_values($this->sort)));
		}
		$sql .= ';';
		return $sql;
	}

	/**
	 * @return int
	 */
	public function count() {
		$rows = iterator_to_array($this->db->q($this->generateSQL(DbModelQuery::GENERATE_MODE_COUNT)));
		$row = array_values($rows[0]);
		return $row[0];
	}

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 * 
	 * @return Traversable
	 */
	public function getIterator() {
		return $this->db->q($this->generateSQL(), array(), $this->model);
	}
}

class DbModelQueryExpression {
	protected $callback;
	
	public function __construct($callback) {
		$this->callback = $callback;
	}
	
	/**
	 * @param Db $db
	 * @return string
	 */
	public function serialize(Db $db) {
		$callback = $this->callback; 
		return $callback($db);
	}
}

/**
 * @return DbModelQueryExpression
 */
function DbEquals($field, $value) {
	return new DbModelQueryExpression(function(Db $db) use ($field, $value) {
		return $field . '==' . $db->quote($value);
	});
}

/**
 * @return DbModelQueryExpression
 */
function DbIn($field, $values) {
	return new DbModelQueryExpression(function(Db $db) use ($field, $values) {
		return $field . ' IN (' . implode(',', array_map(array($db, 'quote'), $values)) . ')';
	});
}

/**
 * @return DbModelQueryExpression
 */
function DbBinary() {
	$args = func_get_args();
	$operator = array_shift($args);
	return new DbModelQueryExpression(function(Db $db) use ($operator, $args) {
		return implode(' ' . $operator . ' ', array_map(function(DbModelQueryExpression $v) use ($db) { return '(' . $v->serialize($db) . ')'; }, $args));
	});
}


/**
 * @return DbModelQueryExpression
 */
function DbAnd() {
	return call_user_func_array('DbBinary', array_merge(array('AND'), func_get_args()));
}

/**
 * @return DbModelQueryExpression
 */
function DbOr() {
	return call_user_func_array('DbBinary', array_merge(array('OR'), func_get_args()));
}
