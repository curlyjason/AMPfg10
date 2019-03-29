<?php
App::uses('MessageAbstract', 'Lib/Notifiers');
/**
 * Description of Available
 *
 * @author dondrake
 */
class Available extends MessageAbstract {
	
	protected $id;
	
	protected $customer_item_code;
	
	protected $name;
	
	protected $quantity;
	
	protected $available_qty;
	
	protected $pending_qty;
	
	protected $reorder_level;
	
	protected $reorder_qty;
	
	protected $product_id;
	
	protected $sell_quantity;
	
	protected $sell_unit;
	
	protected $customer_user_id;


	private $properties;
	
	private $itemFields = array(
		'id' => NULL,
		'name' => NULL,
		'customer_item_code' => NULL,
		'quantity' => NULL,
		'pending_qty' => NULL,
		'reorder_level' => NULL,
		'reorder_qty' => NULL
	);
	
	private $productFields = array(
		'sell_unit' => NULL,
		'sell_quantity' => NULL
	);

	/**
	 * Set up a the data properties
	 * 
	 * @param array $product The Catalog and Item record array
	 * @param string $avaialble The current calculated avaialble quantity
	 */
	public function __construct($product = NULL, $avaialble = NULL) {
		if ($product === NULL) {
			return $this;
		}
		
		$this->product_id = $product['id'];
		$this->available_qty = $avaialble;
		
		// sets many of the base data properties
		$fields = array_intersect_key($product, $this->productFields);
		$this->_set($fields);
		$fields = array_intersect_key($product['Item'], $this->itemFields);
		$this->_set($fields);
		
		$this->properties = array_merge($this->itemFields, $this->productFields, array('product_id' => NULL, 'available_qty' => NULL, 'customer_user_id' => NULL));
		
		// to get compatiblity with message notification system
		foreach ($this->properties as $key => $dummy) {
			$this->data[$key] = $this->$key;
		}
	}
	
	public function customerUserId($customer_user_id = NULL) {
		if (!is_null($customer_user_id)) {
			$this->data['customer_user_id'] = $customer_user_id;
			$this->customer_user_id = $customer_user_id;
		}
		return $this->data['customer_user_id'];
	}
	/**
	 * Move associative array vals to matching properties
	 * 
	 * @param array $vals Malues to move to properties
	 */
	private function _set($vals) {
		$vars = get_object_vars($this);
		foreach ($vals as $key => $val) {
			if (array_key_exists($key, $vars)) {
				$this->{$key} = $val;
			}
		}
	}
	
	public function data($data = NULL) {
		if ($data !== NULL) {
			parent::data($data);
			$this->_set($data);
		}
		return $this->data;
	}


	public function get($property) {
		if (array_key_exists($property, $this->properties)) {
			return $this->$property;
		}
		return NULL;
	}
	
	public function low(){
		
		if ($this->lowItemQty()) {
			return LOW_ITEM;
			
		} elseif ($this->lowProductQty()) {
			return LOW_PRODUCT;
		} else {
			
			return IN_STOCK;
		}
	}

	/**
	 * Is this item low on inventory
	 * 
	 * @return boolean low = true, ok = false
	 */
	public function lowItemQty() {
		if ($this->available_qty <= $this->reorder_level) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * This Product, if ordered, would show low inventory levels for the underying Item
	 * 
	 * Because Product implementations of Items are have a sell_quantity 
	 * they might drive inventory levels down very fast or exceed the 
	 * inventory earlier than expected
	 * 
	 * @return boolean
	 */
	public function lowProductQty() {
		if ($this->availableProduct() <= $this->reorder_level) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Make the Product array key for the DOM update process
	 * 
	 * This string is the js property name that links dom objects to their new data. 
	 * The array returned by $this->domVals() will be the data set under this key.
	 * 
	 * @return string
	 */
	public function productKey(){
		return "I{$this->id}-C{$this->product_id}";
	}
	
	/**
	 * Make the Item array key to group Products under their Item
	 * 
	 * @return string
	 */
	public function itemKey(){
		return "I{$this->id}";
	}

	
	public function availableProduct(){
		return $this->available_qty / $this->sell_quantity;
	}

	/**
	 * Return the DOM update array values
	 * 
	 * This is the DOM element hook and data point to 
	 * update a page that contains 'available quantity' information 
	 * for a Catalog entry. The key uniquely identifies every Catalog 
	 * use for an item so when the full array is built, all product 
	 * variants can get updated.
	 * 
	 * @return array
	 */
	public function domVals(){
		return array(
			"-{$this->productKey()}-",
			$this->availableProduct(),
			$this->sell_quantity,
			$this->sell_unit
		);
	}
	
	/**
	 * Get array data or string notification of for inventory status of this item
	 * 
	 * @param string $type 'array' or 'string'
	 * @return mixed array or false on unknown $type
	 */
	public function notify($type) {
		$low = $this->low();
		if($type === 'array') {
			return $this->aNode($low);
		} elseif ($type === 'string') {
			return $this->message($low);
		}
		return FALSE;
	}
	
	/**
	 * Return array of notification data
	 * 
	 * Used as notification data for Robot processes
	 * 
	 * @return array
	 */
	private function aNode($low) {
		$response = $this->data;
		if ($low === LOW_PRODUCT) {
			$response += array('details' => "One of the products (id $this->product_id) based on this item sells in quantities of $this->sell_quantity. \rAfter fulfilling the current orders, there will be {$this->availableProduct()} available at this sell quantity.");
		}
		return $response;
	}
	
	/**
	 * Return string notification message
	 * 
	 * Used as the notification to humans (via email, etc.)
	 * 
	 * @return string
	 */
	private function message($low) {
		if ($low === LOW_ITEM) {
			return "<b>$this->name inventory is low.</b>"
					. "<br /><span style=\"font-size: 90%; margin-left: 6px;\">After fulfilling the current orders, there will be $this->available_qty items.</span><br />"
					. "<span style=\"font-size: 90%; margin-left: 6px;\">At the moment, there are <b>$this->quantity on the shelf</b>"
					. " and $this->pending_qty expected to replenish stock.</span>";
		} elseif ($low === LOW_PRODUCT) {
			return "<b>$this->name inventory is low.</b>"
					. "<br /><span style=\"font-size: 90%; margin-left: 6px;\">One of the products (id $this->product_id) sells in quantities of $this->sell_quantity.<br />"
					. "After fulfilling the current orders, there will be {$this->availableProduct()} available at this sell quantity.</span><br />"
					. "<span style=\"font-size: 90%; margin-left: 6px;\">At the moment, there are <b>$this->quantity individual pieces on the shelf</b>"
							. " and $this->pending_qty expected to replenish stock.</span>";
		}
	}
}
