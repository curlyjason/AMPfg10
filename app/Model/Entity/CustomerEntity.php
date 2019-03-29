<?php

/**
 * Description of CustomerEntityClass
 *
 * @author jasont
 */

App::uses('Entity', 'Model/Entity');
App::uses('Item', 'Model');
App::uses('ItemCollection', 'Model/Entity');

class CustomerEntity extends Entity {
	
	public $ItemCollection;

	public function __construct(EntityCollection $collection, $data) {
		parent::__construct($collection, $data);
		$Catalog = ClassRegistry::init('Catalog');
		$itemsArray = $Catalog->allItemsForCustomer($this->id);

		$this->ItemCollection = new ItemCollection($itemsArray, array('path' => '{n}', 'sortBy' => $this->Collection->option('sortBy')));
	}
}
