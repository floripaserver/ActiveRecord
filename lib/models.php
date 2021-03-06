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

/**
 * Model collection.
 *
 * @property-read Connections $connections
 * @property-read array[string]array $definitions
 * @property-read array[string]Model $instances
 */
class Models implements \ArrayAccess
{
	/**
	 * Instanciated models.
	 *
	 * @var array[string]Model
	 */
	protected $instances = array();

	/**
	 * Models definitions.
	 *
	 * @var array[string]array
	 */
	protected $definitions = array();

	/**
	 * Connections manager.
	 *
	 * @var Connections
	 */
	protected $connections;

	/**
	 * Initializes the {@link $connections} and {@link $definitions} properties.
	 *
	 * @param Connections $connections Connections manager.
	 * @param array[string]array $definitions Model definitions.
	 */
	public function __construct(Connections $connections, array $definitions=array())
	{
		$this->connections = $connections;

		foreach ($definitions as $id => $definition)
		{
			$this[$id] = $definition;
		}
	}

	public function __get($property)
	{
		switch ($property)
		{
			case 'connections': return $this->connections;
			case 'definitions': return new \ArrayIterator($this->definitions);
			case 'instances': return new \ArrayIterator($this->instances);
		}
	}

	/**
	 * Checks if a model is defined.
	 *
	 * @return bool
	 */
	public function offsetExists($id)
	{
		return isset($this->definitions[$id]);
	}

	/**
	 * Sets the definition of a model.
	 *
	 * The {@link Model::ID} and {@link Model::NAME} are set to the provided id if they are not
	 * defined.
	 *
	 * @param string $id Identifier of the model.
	 * @param array $definition Definition of the model.
	 *
	 * @throws ModelAlreadyInstantiated in attempt to write a model already instantiated.
	 */
	public function offsetSet($id, $definition)
	{
		if (isset($this->instances[$id]))
		{
			throw new ModelAlreadyInstantiated($id);
		}

		$this->definitions[$id] = $definition + array
		(
			Model::ID => $id,
			Model::NAME => $id
		);
	}

	/**
	 * Returns a {@link Model} instance.
	 *
	 * @param string $id Identifier of the model.
	 *
	 * @return Model
	 *
	 * @throws ModelNotDefined when the model is not defined.
	 */
	public function offsetGet($id)
	{
		if (isset($this->instances[$id]))
		{
			return $this->instances[$id];
		}

		if (!isset($this->definitions[$id]))
		{
			throw new ModelNotDefined($id);
		}

		$properties = $this->definitions[$id] + array
		(
			Model::CONNECTION => 'primary'
		);

		if (is_string($properties[Model::CONNECTION]))
		{
			$properties[Model::CONNECTION] = $this->connections[$properties[Model::CONNECTION]];
		}

		return new Model($properties);
	}

	/**
	 * Unset the definition of a model.
	 *
	 * @throws ModelAlreadyInstantiated in attempt to unset the definition of an already
	 * instantiated model.
	 */
	public function offsetUnset($id)
	{
		if (isset($this->instances[$id]))
		{
			throw new ModelAlreadyInstantiated($id);
		}

		unset($this->definitions[$id]);
	}
}

/*
 * EXCEPTIONS
 */

/**
 * Exception thrown when a requested model is not defined.
 */
class ModelNotDefined extends ActiveRecordException
{
	public function __construct($id, $code=500, \Exception $previous=null)
	{
		parent::__construct("Model not defined: $id.", $code, $previous);
	}
}

/**
 * Exception thrown in attempt to set/unset the definition of an already instantiated model.
 */
class ModelAlreadyInstantiated extends ActiveRecordException
{
	public function __construct($id, $code=500, \Exception $previous=null)
	{
		parent::__construct("Model already instanciated: $id.", $code, $previous);
	}
}