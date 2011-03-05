<?php

require_once(__DIR__ . '/../../web/classes/Core.php');

class DbTest extends PHPUnit_Framework_TestCase {
	protected $db;
	
	public function setUp() {
		$this->db = new Db(':memory:');
	}
	
	public function testUpdateTableSchema() {
		$this->db->updateTableSchema(
			'TestTable',
			array(
				'field_unique' => 'string',
				'field_test' => 'string',
			),
			array(
				(object)array('type' => 'UNIQUE INDEX', 'name' => 'unique_field_unique', 'fields' => array('field_unique')),
				(object)array('type' => 'INDEX'       , 'name' => 'index_field_test', 'fields' => array('field_test')),
			)
		);
		
		$this->assertEquals('field_unique,field_test', implode(',', $this->db->getTableFields('TestTable')));
		$this->assertEquals('{"unique_field_unique":"UNIQUE INDEX","index_field_test":"INDEX"}', json_encode($this->db->getTableIndexes('TestTable'))); 

		$this->db->updateTableSchema(
			'TestTable',
			array(
				'field_unique' => 'string',
				'field_new' => 'integer',
			),
			array(
				(object)array('type' => 'UNIQUE INDEX', 'name' => 'unique_field_unique', 'fields' => array('field_unique')),
			)
		);

		$this->assertEquals('field_unique,field_new', implode(',', $this->db->getTableFields('TestTable')));
		$this->assertEquals('{"unique_field_unique":"UNIQUE INDEX"}', json_encode($this->db->getTableIndexes('TestTable'))); 
	}
}
