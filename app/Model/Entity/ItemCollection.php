<?php
App::uses('EntityCollection', 'Model/Entity');
App::uses('ItemEntity', 'Model/Entity');
/**
 * ItemCollection is the iterator and other methods for handling the set of Entities
 *
 * @author dondrake
 */
class ItemCollection extends EntityCollection{
	
	protected $sortBy = NULL;

	/**
	 * Construct a new Item collection, sorting the data
	 * 
	 * Rush the settings into place because we'll need them for 
	 * creation of the heap before parent can do them.
	 * 
	 * 
	 * @param array $data_array
	 * @param array $options_array sortBy = field/property to sort the heap by
	 */
	public function __construct($data_array, $options_array) {
		$this->options = $options_array;
		$this->sortBy = $this->option('sortBy');
		parent::__construct($data_array, $options_array);
	}
	
	/**
	 * Place data into a new entities heap property
	 * 
	 * Will make a new heap with a new sorting key 
	 * rather than adding to an existing one
	 * 
	 * @param array $data The records to store
	 */
	protected function constructEntities($data) {
		$this->entities = new ItemHeap($this->sortBy);
		foreach ($data as $entity) {
			$this->entities->insert(new $this->entityClass($this, $entity));
		}
	}

	/**
	 * The entities property is a Heap in this collection. Override the iterator to cope.
	 */
	public function rewind() {$this->entities->rewind();}
	public function current() {return $this->entities->current(); }
	public function key() {return $this->entities->key();}
	public function next() {$this->entities->next();}
	public function valid() {return $this->entities->valid();}
	public function count() {return $this->entities->count();}
	
	/**
	 * Create a clone of the heap to iterate on
	 * 
	 * @param string $state
	 * @return \EntityStateFilter
	 */
	public function filter($state) {
		$collection = clone $this->entities;
		return new EntityStateFilter($collection, $state);
	}
	
}

/**
 * A Min Heap with configurable sorting key
 */
class ItemHeap extends SplHeap{
	
	private $sortBy;
	
	public function __construct($sortBy) {
		$this->sortBy = $sortBy;
	}
	
	protected function compare($value1, $value2) {
//		return ($value1->{$this->sortBy} < $value2->{$this->sortBy}) ? 1 : -1;
		return strcasecmp($value2->{$this->sortBy}, $value1->{$this->sortBy});
	}

}