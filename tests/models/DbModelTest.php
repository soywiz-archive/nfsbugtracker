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
	 * @expectedException Exception 
	 */
	public function testValidateExecuted() {
		$a = new DemoDbModel();
		$a->test = 'notest';
		$a->save($this->db);
	}
}

class DemoDbModel extends DbModel {
	/**
	 * @unique
	 * 
	 * @var string
	 */
	public $test;
	
	public function validate() {
		if ($this->test != 'test') throw(new Exception("Validate exception"));
	}
}
