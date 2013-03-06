<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\ActiveRecord;

class ActiveRecordTest extends \PHPUnit_Framework_TestCase
{
	static private $connection;
	static private $model;

	static public function setUpBeforeClass()
	{
		self::$connection = new Connection('sqlite::memory:');
		self::$model = new Model
		(
			array
			(
				Model::CONNECTION => self::$connection,
				Model::NAME => 'testing',
				Model::SCHEMA => array
				(
					'fields' => array
					(
						'id' => 'serial',
						'title' => 'varchar'
					)
				)
			)
		);

		self::$model->install();
	}

	public function test_construct()
	{
		new ActiveRecord(self::$model);
		new ActiveRecord('testing');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_construct_invalid()
	{
		new ActiveRecord(new \stdClass());
	}

	public function test_get_model()
	{
		$record = new ActiveRecord(self::$model);
		$this->assertEquals(self::$model, $record->_model);
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_model()
	{
		$record = new ActiveRecord(self::$model);
		$record->_model = null;
	}

	public function test_get_model_id()
	{
		$record = new ActiveRecord(self::$model);
		$this->assertEquals(self::$model->id, $record->_model_id);
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_model_id()
	{
		$record = new ActiveRecord(self::$model);
		$record->_model_id = null;
	}

	public function test_sleep()
	{
		$record = new ActiveRecord(self::$model);
		$properties = $record->__sleep();

		$this->assertNotContains('_model', $properties);
		$this->assertContains('_model_id', $properties);
	}

	public function test_to_array()
	{
		$record = new ActiveRecord(self::$model);
		$array = $record->to_array();

		$this->assertNotContains('_model', $array);
		$this->assertNotContains('_model_id', $array);
	}

	public function test_create_return_key()
	{
		$model = self::$model;

		$a1 = new ActiveRecord($model);
		$a1->title = 'a1';

		$this->assertEquals(1, $a1->save());

		$a2 = new ActiveRecord($model);
		$a2->title = 'a2';

		$this->assertEquals(2, $a2->save());

		$a3 = new ActiveRecord($model);
		$a3->title = 'a3';

		$this->assertEquals(3, $a3->save());

		#

		$this->assertEquals(1, $a1->save());
	}

	public function test_delete()
	{
		$model = self::$model;
		$record = $model[1];
		$this->assertTrue($record->delete());

		$record = $model[2];
		$this->assertTrue($record->delete());

		$record = $model[3];
		$this->assertTrue($record->delete());
	}

	public function test_invalid_delete()
	{
		$record = new ActiveRecord(self::$model);
		$record->id = 999;
		$this->assertFalse($record->delete());
	}
}