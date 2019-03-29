<?php
class CartComponent extends Component {

//////////////////////////////////////////////////

	public $components = array('Session');

//////////////////////////////////////////////////

	public $controller;

//////////////////////////////////////////////////

	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->controller = $collection->getController();
		parent::__construct($collection, array_merge($this->settings, (array)$settings));
	}

//////////////////////////////////////////////////

	public function startup(Controller $controller) {
		//$this->controller = $controller;
	}


	public $maxQuantity = 1000000;

//////////////////////////////////////////////////

    /**
     * Add (or replace) an item to the cart
     * 
     * If the item is already in the cart
     * the new quantity will be used, the old overwritten
     * 
     * @param string $id The CatalogProduct to add to the cart
     * @param int $quantity The number of these items to add to the cart
     * @return mixed array of the added/removed product, false if no product
     */
	public function add($id, $quantity = 1) {

		if(!is_numeric($quantity)) {
			$quantity = 1;
		}

		$quantity = abs($quantity);

		if($quantity > $this->maxQuantity) {
			$quantity = $this->maxQuantity;
		}

		if($quantity == 0) {
			return $this->remove($id);
		}
		
		//do the basic catalog find
		$product = $this->controller->Catalog->find('first', array(
//			'recursive' => 1,
			'conditions' => array(
				'Catalog.id' => $id
			),
			'contain' => array(
				'Item' => array(
					'Image'
				),
				'ParentCatalog' => array(
					'Item'
				)
			)
		));
		//exit if there is no return
		if(empty($product)) {
			return false;
		}
        
		//modify the found array to move the Image to the top level
		$product['Image'] = $product['Item']['Image'];
		unset($product['Item']['Image']);

        //check customer of item and cart
        $ancestors = explode(',', $product['Catalog']['ancestor_list']);
        $topCatalogNode = $ancestors[2];
        $itemCustomer = $this->controller->Catalog->field('customer_id', array('id' => $topCatalogNode));
        $cartCustomer = $this->Session->read('Shop.Customer.id');
        if(empty($cartCustomer)){
            $this->writeCustomerToSession($itemCustomer);
        } else if($cartCustomer != $itemCustomer) {
            return false;
        }
		
		//set note for all new items
		$shopOrderItem = $this->Session->read('Shop.OrderItem');
		if (!empty($shopOrderItem)) {
			$cartItemKeys = array_keys($shopOrderItem);
			$cartNote = $shopOrderItem[$cartItemKeys[0]]['note'];
		} else {
			$cartNote = '';
		}
		
		
		$data['item_id'] = $product['Item']['id'];
		$data['catalog_id'] = $product['Catalog']['id'];
		$data['name'] = $product['Catalog']['name'];
		$data['price'] = $product['Catalog']['price'];
		$data['quantity'] = $quantity;
		$data['subtotal'] = sprintf('%01.2f', $product['Catalog']['price'] * $quantity);
		$data['sell_quantity'] = $product['Catalog']['sell_quantity'];
		$data['sell_unit'] = $product['Catalog']['sell_unit'];
		$data['each_quantity'] = $product['Catalog']['sell_quantity'] * $quantity;
		$data['note'] = $cartNote;
		$data['Item'] = $product['Item'];
		$data['Catalog'] = $product['Catalog'];
		$data['Catalog']['ParentCatalog'] = $product['ParentCatalog'];
		$data['Image'] = $product['Image'];
		$this->Session->write('Shop.OrderItem.' . $data['catalog_id'], $data);
		$this->Session->write('Shop.Order.shop', 1);

		$this->Cart = ClassRegistry::init('Cart');

		$cartdata['Cart']['sessionid'] = $this->Session->id();
		$cartdata['Cart']['customer_id'] = $itemCustomer;
		$cartdata['Cart']['catalog_id'] = $product['Catalog']['id'];
		$cartdata['Cart']['user_id'] = $this->Session->read('Auth.User.id');
		$cartdata['Cart']['quantity'] = $quantity;
		$cartdata['Cart']['item_id'] = $product['Item']['id'];
		$cartdata['Cart']['name'] = $product['Catalog']['name'];
		$cartdata['Cart']['price'] = $product['Catalog']['price'];
		$cartdata['Cart']['sell_quantity'] = $product['Catalog']['sell_quantity'];
		$cartdata['Cart']['sell_unit'] = $product['Catalog']['sell_unit'];
		$cartdata['Cart']['each_quantity'] = $product['Catalog']['sell_quantity'] * $quantity;
		$cartdata['Cart']['note'] = $cartNote;
		$cartdata['Cart']['subtotal'] = sprintf('%01.2f', $product['Catalog']['price'] * $quantity);

		$existing = $this->Cart->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'Cart.sessionid' => $this->Session->id(),
				'Cart.catalog_id' => $product['Catalog']['id'],
			)
		));
		if($existing) {
			$cartdata['Cart']['id'] = $existing['Cart']['id'];
		} else {
			$this->Cart->create();
		}
		$this->Cart->save($cartdata, false);

        $this->controller->Item->manageUncommitted($product['Item']['id']);
		// !!!!!!! hey, these two setting below would need to write to ItemId, not CatalogId, if they ever come back  !!!!!!!!!!!!
//        $this->Session->write('Shop.OrderItem.' . $product['Catalog']['id'] . '.available_qty', $this->controller->Item->available['available']);
//        $this->Session->write('Shop.OrderItem.' . $product['Catalog']['id'] . '.Item.available_qty', $this->controller->Item->available['available']);
        
		$this->cart();

		return $product;
	}
    
    private function writeCustomerToSession($customerId) {
//		debug($customerId);
        $this->Customer = ClassRegistry::init('Customer');
        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id' => $customerId
            ),
            'contain' => array(
                'Address',
                'User'
            )
        ));
//		debug($customer);
        $this->Session->write('Shop.Customer', $customer['Customer']);
        $this->Session->write('Shop.Customer.Address', $customer['Address']);
        $this->Session->write('Shop.Customer.User', $customer['User']);
    }

    /**
     * Remove the specified item from the cart
     * 
     * Remove an item from the shopping cart
     * Adjust the Item uncommited qty
     * Nudge the cart wrapper into the present
     * 
     * @param string $id The item to remove
     * @return mixed The item removed or false if it didn't exist
     */
	public function remove($id) {
		if($this->Session->check('Shop.OrderItem.' . $id)) {
			$product = $this->Session->read('Shop.OrderItem.' . $id);
			$this->Session->delete('Shop.OrderItem.' . $id);

			ClassRegistry::init('Cart')->deleteAll(
				array(
					'Cart.sessionid' => $this->Session->id(),
					'Cart.catalog_id' => $id,
				),
				false
			);
			
			$this->controller->Item->manageUncommitted($product['item_id']);

			$this->cart();
			return $product;
		}
		return false;
	}

    /**
     * Evaluate and write cart wrapper to Session
     * 
     * If there are existing cart items in session, 
     * accumulate values to the cart wrapper
     * and write the wrapper to the session
     * 
     * @return boolean true = cart updated, false = empty cart created
     */
	public function cart() {
		$shop = $this->Session->read('Shop');
		$quantity = 0;
		$subtotal = 0;
		$total = 0;
		$order_item_count = 0;

		if (count($shop['OrderItem']) > 0) {
//			debug($shop);
			foreach ($shop['OrderItem'] as $index => $item) {
//				debug($index);
//				debug($item);
				$quantity += $item['quantity'];
				$subtotal += $item['subtotal'];
				$total += $item['subtotal'];
				$order_item_count++;
			}
			$d['order_item_count'] = $order_item_count;
			$d['quantity'] = $quantity;
			$d['subtotal'] = sprintf('%01.2f', $subtotal);
			if(!empty($shop['Customer']['id'])){
				$d['handling'] = ClassRegistry::init('Price')->fetchPullFee($shop['Customer']['id'], $quantity);			
			}
			$d['total'] = sprintf('%01.2f', $total);
			$this->Session->write('Shop.Order', $d + $shop['Order']);
			return true;
		} else {
			$d['quantity'] = 0;
			$d['subtotal'] = 0;
			$d['total'] = 0;
			$d['handling'] = 0;
			$this->Session->write('Shop.Order', $d + $shop['Order']);
			return false;
		}
	}
	
    /**
     * Eliminate a cart from the db and Session
     */
	public function clear() {
		$products = $this->Session->read('Shop.OrderItem');
		ClassRegistry::init('Cart')->deleteAll(array('Cart.sessionid' => $this->Session->id()), false);
		$this->Session->delete('Shop');
		if ($products) {
			foreach ($products as $catalogId => $product) {
				$this->controller->Item->manageUncommitted($product['item_id']);
			}
		}
	}
	
}
