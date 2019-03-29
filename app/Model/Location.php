<?php
App::uses('AppModel', 'Model');
/**
 * Location Model
 *
 * @property Item $Item
 */
class Location extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Item' => array(
			'className' => 'Item',
			'foreignKey' => 'item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public $rowMax = 25;
	
	public $binMax = 100;
}
