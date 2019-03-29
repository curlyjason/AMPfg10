<?php
App::uses('AppModel', 'Model');
/**
 * InventoryTransaction Model
 *
 * @property Item $Item
 */
class InventoryTransaction extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Item' => array(
			'className' => 'Item',
			'foreignKey' => 'item_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);


    private function logInventoryTransaction(){
//        using provided item id, amount & action
//        derive the motion (adjust + = adjust, adjust - = deduct), 
//        write log record including item id, log type (adjust, deduct, receipt, transfer, allocate)
//        no return, or potentially only confirming return
    }
}
