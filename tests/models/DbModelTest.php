<?php

require_once(__DIR__ . '/../../web/classes/Core.php');

class DbModelTest extends PHPUnit_Framework_TestCase {
	protected $db;
	
	public function setUp() {
		$this->db = new Db('sqlite::memory:');
		DemoDbModel::upgradeTable($this->db);
	}

	/**
	 * @expectedException PDOException 
	 */
	public function testCreateTwice() {
		$a = new DemoDbModel();
		$a->test = 'test';
		$a->save($this->db);

		DemoDbModel::upgradeTable($this->db);
		
		$a = new DemoDbModel();
		$a->test = 'test';
		$a->save($this->db);
	}

	/**
	 * @expectedException DemoDbModelException 
	 */
	public function testValidateExecuted() {
		$a = new DemoDbModel();
		$a->test = 'notest';
		$a->save($this->db);
	}
	
	public function testDelete() { 
		$this->db->beginTransaction();
		$a = new DemoDbModel(); $a->test = 'test1'; $a->save($this->db);
		$a = new DemoDbModel(); $a->test = 'test2'; $a->save($this->db);
		$a = new DemoDbModel(); $a->test = 'test3'; $a->save($this->db);
		$this->db->commit();
	
		$this->assertEquals(3, iterator_count(DemoDbModel::getAll($this->db)));
		
		foreach (DemoDbModel::getAll($this->db) as $a) {
			if ($a->test == 'test2') $a->delete($this->db);
		}
		
		$this->assertEquals(2, iterator_count(DemoDbModel::getAll($this->db)));
	}
	
	public function testUpdate() {
		$a = new DemoDbModel(); $a->test = 'test1'; $a->save($this->db);
		
		$rows1 = iterator_to_array(DemoDbModel::getAll($this->db));
		$this->assertEquals('[{"test":"test1","rowid":"1"}]', json_encode($rows1));
		
		$rows1[0]->test = 'test2'; $rows1[0]->save($this->db); 
		
		$rows2 = iterator_to_array(DemoDbModel::getAll($this->db));
		$this->assertEquals('[{"test":"test2","rowid":"1"}]', json_encode($rows1));
	}
}

class DemoDbModelException extends Exception { 
}

class DemoDbModel extends DbModel {
	/**
	 * @unique
	 * 
	 * @var string
	 */
	public $test;
	
	public function validate() {
		if ($this->test == 'notest') throw(new DemoDbModelException("Validate exception"));
	}
}
