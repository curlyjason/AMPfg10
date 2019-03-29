<?php
App::uses('EntityCollection', 'Model/Entity');
App::uses('CustomerEntity', 'Model/Entity');
/**
 * CustomerCollection is the iterator and other methods for handling the set of Entities
 *
 * @author dondrake
 */
class CustomerCollection extends EntityCollection{
	
	public function __construct($data_array, $options_array) {
		parent::__construct($data_array, $options_array);
	}
	
	public function inlist() {
		$inlist = array();
		foreach ($this->entities as $customer) {
			$inlist[] = $customer->id;
		}
		return implode('-', $inlist);
	}
	
}