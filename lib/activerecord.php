<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

/**
 * Active Record faciliates the creation and use of business objects whose data require persistent
 * storage via database.
 *
 * @property-read Model $_model Model managing the active record.
 * @property-read string $_model_id Identifier of the model managing the active record.
 */
class ActiveRecord extends \ICanBoogie\Object
{
	/**
	 * Model managing the active record.
	 *
	 * @var Model
	 */
	protected $_model;

	/**
	 * Identifier of the model managing the active record.
	 *
	 * @var string
	 */
	protected $_model_id;

	/**
	 * Initializes the {@link $_model} and {@link $_model_id} properties.
	 *
	 * @param string|Model $model Model managing the active record. A {@link Model}
	 * object can be provided or a model id. If a model id is provided, the model object
	 * is resolved when the {@link $_model} property is accessed.
	 */
	public function __construct($model)
	{
		if (is_string($model))
		{
			unset($this->_model);
			$this->_model_id = $model;
		}
		else
		{
			$this->_model = $model;
			$this->_model_id = $model->id;
		}
	}

	/**
	 * Returns the model for the active record.
	 *
	 * This getter is used when the model has been provided as a string during construct.
	 *
	 * @return Model
	 */
	protected function volatile_get__model()
	{
		return ActiveRecord\get_model($this->_model_id);
	}

	/**
	 * @throws PropertyNotWritable in attempt to set the {@link _model} property.
	 */
	protected function volatile_set__model()
	{
		throw new PropertyNotWritable(array('_model', $this));
	}

	/**
	 * Saves the active record using its model.
	 *
	 * @return int Primary key value of the active record.
	 */
	public function save()
	{
		$model = $this->_model;
		$primary = $model->primary;

		$properties = get_object_vars($this);
		$key = null;

		if (isset($properties[$primary]))
		{
			$key = $properties[$primary];

			unset($properties[$primary]);
		}

		/*
		 * We discard null values so that we don't have to define every properties before saving
		 * our active record.
		 *
		 * FIXME-20110904: we should check if the schema allows the column value to be null
		 */

		foreach ($properties as $identifier => $value)
		{
			if ($value !== null)
			{
				continue;
			}

			unset($properties[$identifier]);
		}

		return $model->save($properties, $key);
	}

	/**
	 * Deletes the active record using its model.
	 */
	public function delete()
	{
		$model = $this->_model;
		$primary = $model->primary;

		return $model->delete($this->$primary);
	}
}

namespace ICanBoogie\ActiveRecord;

/**
 * Generic Active Record exception class.
 */
class ActiveRecordException extends \Exception
{

}