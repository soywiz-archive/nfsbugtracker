<?php

require_once(__DIR__ . '/../../web/classes/Core.php');

class DbModelQueryTest extends PHPUnit_Framework_TestCase {
	protected $db;
	
	public function setUp() {
		$this->db = new Db(':memory:');
		DemoDbModelQueryTest::upgradeTable($this->db);
		
		$a = new DemoDbModelQueryTest(); $a->test = 'test1'; $a->field2 = 'b'; $a->save($this->db);
		$a = new DemoDbModelQueryTest(); $a->test = 'test2'; $a->field2 = 'b'; $a->save($this->db);
		$a = new DemoDbModelQueryTest(); $a->test = 'test3'; $a->field2 = 'a'; $a->save($this->db);
		$a = new DemoDbModelQueryTest(); $a->test = 'test4'; $a->field2 = 'a'; $a->save($this->db);
	}
	
	public function testGetAll() {
		$rows = iterator_to_array($this->getSelector());
		$this->assertEquals(4, count($rows));
	}

	public function testCount() {
		$this->assertEquals(4, $this->getSelector()->count());
	}
	
	public function testSort() {
		$rows = iterator_to_array($this->getSelector()->sort('+test'));
		$this->assertEquals('test1', $rows[0]->test);

		$rows = iterator_to_array($this->getSelector()->sort('-test'));
		$this->assertEquals('test4', $rows[0]->test);
	}

	public function testMultiSort() {
		$rows = iterator_to_array($this->getSelector()->sort('-field2', '+test'));
		$rowids = array_map(function($row) { return $row->rowid; }, $rows);
		$this->assertEquals('1,2,3,4', implode(',', $rowids));

		$rows = iterator_to_array($this->getSelector()->sort('-field2', '-test'));
		$rowids = array_map(function($row) { return $row->rowid; }, $rows);
		$this->assertEquals('2,1,4,3', implode(',', $rowids));
	}
	
	public function testSimpleFilter() {
		$rows = iterator_to_array($selector = $this->getSelector()->where(DbEquals('test', 'test2')));
		//echo $selector->generateSQL();
		$this->assertEquals(1, count($rows));
		$this->assertEquals('test2', $rows[0]->test);
	}

	public function testAndInFilter() {
		$rows = iterator_to_array($selector = $this->getSelector()
			->where(
				DbAnd(
					DbIn('field2', array('a', 'c')),
					DbEquals('test', 'test2')
				)
			)
		);
		$this->assertEquals(0, count($rows));
	}
	
	/**
	 * @return DbModelQuery
	 */
	protected function getSelector() {
		return DemoDbModelQueryTest::select($this->db);
	}
}

class DemoDbModelQueryTest extends DbModel {
	/**
	 * @unique
	 * 
	 * @var string
	 */
	public $test;
	
	/**
	 * @index
	 * 
	 * @var string
	 */
	public $field2;
}