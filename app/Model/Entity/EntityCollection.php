<?php

/**
 * EntityCollection is the base class for managing collections of entities
 * 
 * It is an iterator and provides other default methods for managing 
 * a set of Entity objects
 *
 * @author dondrake
 */

App::uses('Hash', 'Utility');

class EntityCollection implements Iterator {

	/**
	 * Iterator pointer
	 *
	 * @var int
	 */
	private $position = 0;
	
	/**
	 * Holds the Entity objects for the collection
	 *
	 * @var array
	 */
	public $entities = array();
	
	/**
	 * Name of the Entity class
	 * 
	 * Calculated fromt he name of the Collection class
	 *
	 * @var string
	 */
	protected $entityClass;
	protected $options = array();

	/**
	 * 
	 * @param array $data_array 
	 * @param array $options_array path = dot notation path to use when flattening the data array
	 */
	public function __construct($data_array, $options_array) {
		$this->position = 0;
		$this->options = $options_array;
		$data = Hash::extract($data_array, $options_array['path']);
		$this->entityClass = preg_replace('/Collection/', 'Entity', get_class($this));
		$this->constructEntities($data);
	}

	/** 
	 * Iterator interface implementation
	 */
	public function rewind() {$this->position = 0;}
	public function current() {return $this->entities[$this->position];}
	public function key() {return $this->position;}
	public function next() {++$this->position;}
	public function valid() {return isset($this->entities[$this->position]);}
	
	public function count() {
		return count($this->entities);
	}

	/**
	 * Default filter implementation 
	 * 
	 * @param string $state 'active' or 'inactive'
	 * @return \EntityStateFilter
	 */
	public function filter($state) {
		return new EntityStateFilter($this, $state);
	}
	
	/**
	 * Default process to store array data as entity objects
	 * 
	 * @param array $data
	 */
	protected function constructEntities($data) {
		foreach ($data as $entity) {
			$this->entities[] = new $this->entityClass($this, $entity);
		}
	}
	
	/**
	 * Retrieve one of the settings values
	 * 
	 * These values were passed when the collection was constructed 
	 * and are used to control the behavior of the object
	 * 
	 * @param string $node
	 * @return mixed
	 */
	public function option($node) {
		if(isset($this->options[$node])){
			return $this->options[$node];
		} else {
			return NULL;
		}
	}
	

}


/**
 * Filter Iterator for the Collection classes
 * 
 * Can only filter based on the 'active' field in the entity 
 * and that field must be true or false
 */
class EntityStateFilter extends FilterIterator{
	
	private $stateFilter;
	
	public function __construct(Iterator $iterator, $filter) {
		parent::__construct($iterator);
		$this->stateFilter = ($filter == 'active') ? 1 : 0;
	}
	
	public function accept() {
		$entity = $this->getInnerIterator()->current();
		if($entity->active == $this->stateFilter){
			return TRUE;
		}
		return FALSE;
	}

}
