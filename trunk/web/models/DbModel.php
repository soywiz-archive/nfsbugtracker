<?php

class DbModel extends Model {
	//public $db;
	
	/**
	 * Enter description here ...
	 * @param Db $db
	 * @return DbModelQuery
	 */
	static public function getAll(Db $db) {
		return static::select($db);
	}

	/**
	 * Enter description here ...
	 * @param Db $db
	 * @return DbModelQuery
	 */
	static public function select(Db $db) {
		return $db->select(get_called_class());
	}

	public function validate() {
		
	}

	public function save(Db $db) {
		$this->validate();

		$sets = array(); foreach ($this->getFields() as $field) $sets[$field] = $this->$field;

		if (isset($this->rowid)) {
			$db->update(get_called_class(), $sets, array('rowid' => $this->rowid));
		} else {
			$db->insert(get_called_class(), $sets);
			$this->rowid = $db->lastInsertId();
		}
	}
	
	public function delete(Db $db) {
		if (!isset($this->rowid)) throw(new Exception("Can't delete model because not saved yet"));
		$db->delete(get_called_class(), array('rowid' => $this->rowid));
	}
	
	public function getFields() {
		static $caches = array();
		$fields = &$caches[get_called_class()];
		if (!isset($fields)) {
			$fields = array();
			$class = new ReflectionClass($this);
			foreach ($class->getProperties() as $property) {
				$fields[] = $property->getName();
			}
		}
		return $fields;
	}
	
	static public function upgradeTable(Db $db, $classModel = NULL) {
		if ($classModel === NULL) $classModel = get_called_class();

		$propertiesInfo = array();
		$indexes = array();
		
		$class = new ReflectionClass($classModel);
		foreach ($class->getProperties() as $property) {
			//$property = new ReflectionProperty($classModel, $property->getName());
			$propertyDoc  = $property->getDocComment();
			$propertyName = $property->getName();
			$propertyInfo = (object)array(
				'type'  => 'string',
			);
			
			if ($propertyName == 'ROWID') continue;
			
			foreach (explode("\n", $propertyDoc) as $propertyDocLine) {
				$propertyDocLine = trim($propertyDocLine, " \t*");
				if (preg_match('/@\\s*(\\w+)\\s*([$\\w+]+)?/msi', $propertyDocLine, $matches)) {
					switch ($matches[1]) {
						case 'unique':
							$indexes[] = (object)array('type' => 'UNIQUE INDEX', 'name' => 'unique_' . $propertyName, 'fields' => array($propertyName));
						break;
						case 'index':
							$indexes[] = (object)array('type' => 'INDEX', 'name' => 'index_' . $propertyName, 'fields' => array($propertyName));
						break;
						case 'var':
							$propertyInfo->type = $matches[2];
						break;
					}
					//print_r($matches);
					//echo "$propertyDocLine\n";
					
				}
			}
			$propertiesInfo[$propertyName] = $propertyInfo;
			
		}
		
		$db->updateTableSchema($classModel, $propertiesInfo, $indexes);
	}
	
	static public function upgradeTables(Db $db) {
		foreach (static::getDbModels() as $model) DbModel::upgradeTable($db, $model);
	}
	
	static public function getDbModels() {
		return array_filter(Core::getAllModels(), function($model) { return is_subclass_of($model, 'DbModel'); });
	}
}
