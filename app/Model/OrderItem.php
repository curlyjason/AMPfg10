<?php
App::uses('AppModel', 'Model');
/**
 * OrderItem Model
 *
 * @property Order $Order
 */
class OrderItem extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'order_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
	    'Order' => array(
		'className' => 'Order',
		'foreignKey' => 'order_id',
		'conditions' => '',
		'fields' => '',
		'order' => ''
	    ),
	    'Item' => array(
		'className' => 'Item',
		'foreignKey' => 'item_id',
		'conditions' => '',
		'fields' => '',
		'order' => ''
	    ),
	    'Catalog' => array(
		'className' => 'Catalog',
		'foreignKey' => 'catalog_id',
		'conditions' => '',
		'fields' => '',
		'order' => ''
	    )

	);
	public $hasMany = array(
		'OrderItemCharges' => array(
			'className' => 'InvoiceItem',
			'foreignKey' => 'order_item_id'
			)
	);

	
	/**
	 * map OrderItem fields to Catalog fields
	 */
	public $fields = array(
		'item_id' => 'item_id',
		'name' => 'name',
		'sell_quantity' => 'sell_quantity',
		'sell_unit' => 'sell_unit',
		'price' => 'price',
		'catalog_id' => 'id',
		'catalog_type' => 'type'
		
	);
	
	/**
	 * Catalog type plus the inventory strategy flags
	 * 
	 * @var string 
	 */
	protected $catalogType;
	
	/**
	 * Catalog type without inventory strategy flags
	 * 
	 * @var string 
	 */
	protected $type;
	
	public function fetchOrderItem($id){
		$orderItem = $this->find('first', array(
			'conditions' => array(
				'OrderItem.id' => $id
			),
			'contain' => array(
				'Catalog',
				'Item'
			)
		)
		);
		return $orderItem;
	}
    
    /**
     * Return a list of order items based upon an order id
     * 
     * @param int $order_id
     * @return array
     */
    public function fetchByOrder($order_id) {
        $items = $this->find('all', array(
            'recursive' => -1,
            'conditions' => array(
                'OrderItem.order_id' => $order_id
            )
        ));
        return $items;
    }
	
	public function pullOrderNumber($orderItemId) {
		$orderId = $this->field('order_id', array('id' => $orderItemId));
		$orderNumber = $this->Order->field('order_number', array('id' => $orderId));
		return $orderNumber;
	}
	
//	public function duplicateOrderItem($orderItem){
//		unset($orderItem['id']);
//		if($this->save($orderItem)){
//			return $this->id;
//		} else {
//			return FALSE;
//		}
//		
//	}
//	
//	public function setOrderItemKitHeader($orderItem){
//		$orderItem['type'] = $orderItem['type'] & KIT_HEADER;
//		if($this->save($orderItem)){
//			return TRUE;
//		} else {
//			return FALSE;
//		}
//	}
	
	/**
	 * Make a Component OrderItem for a KitUp circumstance
	 * 
	 * When a Kit is ordered, but not in stock, its components
	 * will be placed on the order so they can be pulled to assemble
	 * the needed kits. This make a single component order line item.
	 * 
	 * @param type $catalog The component's Catalog record
	 * @param type $orderItem The source Kit OrderItem
	 * @param type $sequence To keep the OrderItems sorted
	 */
	public function catalogToOrderItem($catalog, $orderItem, $sequence) {
		$this->Order->OrderItem->create();
		$new = array();
		
		foreach ($this->fields as $oiField => $cField) {
			$new[$oiField] = $catalog[$cField];
		}
		
		$new['order_id'] = $orderItem['order_id'];
		$new['each_quantity'] = $orderItem['quantity'] * $catalog['sell_quantity'];
		$new['quantity'] = $orderItem['quantity'];
		$new['sequence'] = $sequence;
		$new['subtotal'] = 0;
		$new['pulled'] = FALSE;
		$new['type'] = KIT_COMPONENT;
		
		$this->save(array('OrderItem' => $new));
	}
	
	/**
	 * Return the catalog type without inventory strategy flags
	 * 
	 * Derive the pure catalog-entry type base on this->catalogType or the provided type
	 * 
	 * @param string $catalogType
	 * @return string
	 */
	public function getType($catalogType = NULL) {
		if($catalogType != NULL){
			$this->catalogType = $catalogType;
		}
		$this->type = $this->catalogType & (KIT | PRODUCT | COMPONENT);
		return $this->type;
	}
}
