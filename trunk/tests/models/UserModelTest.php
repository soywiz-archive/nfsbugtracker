<?php

require_once(__DIR__ . '/../../web/classes/Core.php');

class UserModelTest extends PHPUnit_Framework_TestCase {
	protected $db;
	
	public function setUp() {
		$this->db = new Db('sqlite::memory:');
		UserModel::upgradeTable($this->db);
	}
	
	public function testCreateUser() {
		$user = new UserModel();
		$user->name = 'test';
		$user->setPassword('test');
		$user->save($this->db);
		
		$this->assertEquals(1, count(UserModel::getAll($this->db)));
		$this->assertNotEquals('test', $user->getPasswordHash());
	}

    /**
     * @expectedException PDOException
     */
	public function testCreateUserTwice() {
		$user = new UserModel();
		$user->name = 'test';
		$user->setPassword('test');
		$user->save($this->db);

		$user = new UserModel();
		$user->name = 'test';
		$user->setPassword('test');
		$user->save($this->db);
	}
	
    /**
     * @expectedException Exception
     */
	public function testCreateInvalidUser() {
		$user = new UserModel();
		$user->save($this->db);
	}
}