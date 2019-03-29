<?php
App::uses('Catalog', 'Model');

/**
 * Accumulate and manage a set of Available objects
 *
 * @package Inventory.Utilities
 * @author jasont
 */
class AvailableEntries{
	
	protected $customer_user_id;
	
	protected $entries = array();
	
	protected $messages = array();
	
	/**
	 * Add another entry
	 * 
	 * @param Available $available
	 */
	public function append(Available $available){
		array_push($this->entries, $available);
		$this->entries[count($this->entries)-1]->customerUserId($this->getCustomerUserId());
	}

	/**
	 * Return low inventory notices from the set of Available objects
	 * 
	 * @param string $type 'array' for array data node, 'string' for human readable message
	 */
	public function messages($type) {
		// make this call a more generic name to smooth path to better uniformity and possible abstraction
		$object = new ArrayObject($this->entries);
		$iterator = new LowInventoryFilter($object->getIterator());
		
		foreach($iterator as $entry) {
			$low = $entry->low();
			if (!isset($this->messages[$entry->itemKey()])) {
				$this->messages[$entry->itemKey()] = $entry->notify($type);
			} 
		}
		return array('LowInventory' => $this->messages);
	}
	
	public function getCustomerUserId() {
		if (!empty($this->entries)) {
			$this->Catalog = ClassRegistry::init('Catalog');
			$a = $this->Catalog->field('ancestor_list', array('item_id' => $this->entries[0]->get('id')));
			$key = explode(',', $a);
			$this->customer_user_id = $this->Catalog->field('customer_user_id', array('id' => $key[2]));
		}
		return $this->customer_user_id;
	}
}

/**
 * Filter iterator to return only low-inventory Available objects
 */
class LowInventoryFilter extends FilterIterator {

	public function __construct(Iterator $iterator){
        parent::__construct($iterator);
    }

	public function accept() {
        $entry = $this->getInnerIterator()->current();
		$low = $entry->low();
		if ($low !== IN_STOCK) {
			return $low;
		}
        
    }

}
