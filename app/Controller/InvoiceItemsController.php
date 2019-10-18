<?php

App::uses('AppController', 'Controller');
App::uses('FileExtension', 'Lib');

/**
 * InvoiceItems Controller
 *
 * @property InvoiceItem $InvoiceItem
 */
class InvoiceItemsController extends AppController {
	
	public $helpers = array('Invoice', 'InvoiceHeader');

	public $invoiceItems = array();
	
	public $invoiceTotals = array();
	
	public $invoiceContext = array();
	
	public $invoiceCustomer = array();
    
    public $ordItems = array();

    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->InvoiceItem->recursive = 0;
        $this->set('invoiceItems', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->InvoiceItem->exists($id)) {
            throw new NotFoundException(__('Invalid invoice item'));
        }
        $options = array('conditions' => array('InvoiceItem.' . $this->InvoiceItem->primaryKey => $id));
        $this->set('invoiceItem', $this->InvoiceItem->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->InvoiceItem->create();
            if ($this->InvoiceItem->save($this->request->data)) {
                $this->Flash->set(__('The invoice item has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The invoice item could not be saved. Please, try again.'));
            }
        }
        $invoices = $this->InvoiceItem->Invoice->find('list');
        $this->set(compact('invoices'));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->InvoiceItem->exists($id)) {
            throw new NotFoundException(__('Invalid invoice item'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->InvoiceItem->save($this->request->data)) {
                $this->Flash->set(__('The invoice item has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The invoice item could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('InvoiceItem.' . $this->InvoiceItem->primaryKey => $id));
            $this->request->data = $this->InvoiceItem->find('first', $options);
        }
        $invoices = $this->InvoiceItem->Invoice->find('list');
        $this->set(compact('invoices'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
		$this->autoRender = FALSE;
        $this->InvoiceItem->id = $id;
        if (!$this->InvoiceItem->exists()) {
            throw new NotFoundException(__('Invalid invoice item'));
        }
        $this->request->allowMethod(['post', 'delete', 'get']);
        if ($this->InvoiceItem->delete()) {
            $this->Flash->set(__('Invoice item deleted'));
        }
        $this->Flash->set(__('Invoice item was not deleted'));
    }
    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function jsDelete($id = null) {
		$this->layout = 'ajax';
        $this->InvoiceItem->id = $id;
        if (!$this->InvoiceItem->exists()) {
			$result = FALSE;
        }
		
		// get info for total calc later
		$this->InvoiceItem->id = $id;
		$idSet = $this->InvoiceItem->read(array('customer_id', 'order_id'));
		
        $this->request->allowMethod(['post', 'delete', 'get']);
        if ($this->InvoiceItem->delete()) {
			if (empty($idSet['InvoiceItem']['order_id'])){
				$headerTotal = $this->InvoiceItem->fetchTotalGeneralCharges($idSet['InvoiceItem']['customer_id']);
				$headerId = 'general';
			} else {
				$headerTotal = $this->InvoiceItem->fetchTotalOrderCharges($idSet['InvoiceItem']['order_id']);
				$headerId = $idSet['InvoiceItem']['order_id'];
			}
			$invoiceTotal = $this->InvoiceItem->fetchTotalCharges($idSet['InvoiceItem']['customer_id']);

			$result = $id;
        } else {
			$result = FALSE;
		}
		$this->set(compact('result', 'headerTotal', 'headerId', 'invoiceTotal'));
    }
	
	/**
	 * Fetch invoicing line items
	 * 
	 * InvoiceItems can be fetched based upon an OrderItem, a full Order or
	 * all orders for a customer. When a customer is chosen, the method will
	 * also return all of the storage fees associated with the customer
	 * 
	 * @param string $id, the customer, order, or orderItem id
	 * @param string $alias, Order, OrderItem or Customer
	 * @return array
	 */
	public function fetchInvoiceLis($id, $alias = NULL) {
        if($alias === NULL || preg_match('/[0-9a-f\-]+/',$id) == 0){
            exit();
        }
		if(FileExtension::hasExtension($alias)){
			$this->layout = 'default';
			$alias = FileExtension::stripExtension($alias);
		}
		$this->fetchContext($id, $alias);
		if($alias == 'OrderItem'){
			//Need to fix the status helper to make the Charges button reappear on individual items
			$this->layout = 'ajax';
			$this->fetchLiCharges($id);
			//set $this->invoiceCustomer via the provided OrderItem id, see under 'Order' below
			//set 'invoiceTotal' from the total of the line item charges, see under 'Order' below
			$this->set('labelList', array($id => 'Order Item '));
			$this->set('labelList', array(key($this->invoiceItems) => 'Order Item '));
			
		} elseif($alias == 'Order'){
			$this->layout = 'ajax';
			$this->fetchOrderCharges($id);
			$this->set('invoiceTotal', $this->invoiceTotals[$id]);
			$this->invoiceCustomer = $this->InvoiceItem->Order->fetchCustomer($id);
			$this->set('labelList', array($id => 'Order '));
			
		} elseif($alias == 'Customer') {
			//from this point forward, we are working with a customer and $id is a customer $id
			$this->fetchGeneralCharges($id);
			$this->invoiceTotals['general'] = $this->InvoiceItem->fetchTotalGeneralCharges($id);
			$orderInList = array();
			if (!stristr($this->request->url, '/view')) {
				$orderInList = $this->InvoiceItem->Order->fetchShippedOrderInList($id);
				$this->generateShippingCharges($orderInList);
				$this->generateTotalOrderItemCharges($orderInList);
			} else {
				$orderInList = $this->InvoiceItem->Order->fetchInvoicingOrderInList($id);
			}		
			$this->set('invoiceHeader', $this->InvoiceItem->Order->fetchInvoicingOrderHeader($orderInList));
            foreach ($orderInList as $job_number => $order_id) {
                $this->ordItems[$order_id] = $this->InvoiceItem->OrderItem->fetchByOrder($order_id);
            }
			$this->set('invoiceTotal', $this->InvoiceItem->fetchTotalCharges($id));
			$this->set('labelList', array_flip($orderInList));
			if (!empty($orderInList)) {
				foreach ($orderInList as $orderId) {
					$this->fetchOrderCharges($orderId);
				}
			}		
			
		} else {
			$this->Flash->error("The alias $alias is unsupported");
		}
		$this->set('invoiceItems', $this->invoiceItems);
		$this->set('invoiceTotals', $this->invoiceTotals);
		$this->set('invoiceContext', $this->invoiceContext);
		$this->set('invoiceCustomer', $this->invoiceCustomer);
		$this->set('ordItems', $this->ordItems);
		$this->set(compact('id', 'alias'));
		
		// set properties to Invoice model for later (during Invoice XML/Soap)
		$this->InvoiceItem->Invoice->labelList = $this->viewVars['labelList'];
		$this->InvoiceItem->Invoice->invoiceTotals = $this->invoiceTotals;
		$this->InvoiceItem->Invoice->invoiceCustomer = $this->invoiceCustomer;
		
		return $this->invoiceItems;
	}
	
	/**
	 * Generate (as necessary) shipping charges from all listed orders
	 * 
	 * Shipping charges are saved as special InvoiceItems, named "Shipping"
	 * 
	 * @param array $orderInList
	 */
	private function generateShippingCharges($orderInList) {
		if (!empty($orderInList)) {
			$shipments = $this->InvoiceItem->Order->Shipment->fetchShipmentCharges($orderInList);
			$ordersWithShipments = $this->InvoiceItem->fetchExistingShipments($this->invoiceCustomer['User']['id']);
		} else {
			$shipments = array();
		}
		
		if(!empty($shipments)){
			$out = array();
			foreach ($shipments as $order_id => $cost) {
                if (!isset($ordersWithShipments[$order_id])) {
                    $out[]['InvoiceItem'] = array(
                        'id' => isset($ordersWithShipments[$order_id]) ? $ordersWithShipments[$order_id] : '',
                        'order_id' => $order_id,
                        'price' => $cost,
                        'quantity' => 1,
                        'unit' => 'ea',
                        'customer_id' => $this->invoiceCustomer['User']['id'],
                        'name' => 'Shipping',
                        'description' => 'Shipping'
                    );
                }                
			}
			$this->InvoiceItem->saveAll($out);
		}
	}

	/**
	 * Generate (as necessary) order item charges from all listed orders
	 * 
	 * Order item charges are saved as special InvoiceItems, named "Total Item Charges"
	 * 
	 * @param array $orderInList
	 */
	private function generateTotalOrderItemCharges($orderInList) {
		if (!empty($orderInList)) {
			$orderCharges = $this->InvoiceItem->Order->fetchOrderCharges($orderInList);
			$existingOrderCharges = $this->InvoiceItem->fetchExistingOrderCharges($this->invoiceCustomer['User']['id']);
		} else {
			$orderCharges = array();
		}
		
		if(!empty($orderCharges)){
			$out = array();
			foreach ($orderCharges as $order_id => $cost) {
				$out[]['InvoiceItem'] = array(
					'id' => isset($existingOrderCharges[$order_id]) ? $existingOrderCharges[$order_id] : '',
					'order_id' => $order_id,
					'price' => $cost,
					'quantity' => 1,
					'unit' => 'ea',
					'customer_id' => $this->invoiceCustomer['User']['id'],
					'name' => 'Total Item Charges',
					'description' => 'Total Item Charges'
				);
			}
			$this->InvoiceItem->saveAll($out);
		}
	}


	/**
	 * Get the proper id package for the New Charge tool
	 * 
	 * This tool makes a new charge record and operates from three link-contexts
	 * Customer only, Customer&Order, Customer&Order&OrderItem
	 * These IDs will be placed in the first cell of the Tool row
	 * where the New Charge tool can get them for a POST
	 * 
	 * @param string $id the id of the associated item
	 * @param string $alias the alias of the calling function
	 */
	private function fetchContext($id, $alias) {
		if($alias == 'OrderItem'){
			$orderItem = $this->InvoiceItem->Order->OrderItem->find('first', array(
				'conditions' => array(
					'OrderItem.id' => $id
				),
				'contain' => array(
					'Order'
				)
			));
			$this->invoiceContext = array(
				'customer_id' => $orderItem['Order']['user_customer_id'],
				'order_id' => $orderItem['Order']['id'],
				'order_item_id' => $id
			);
			
		} elseif($alias == 'Order'){
			$order = $this->InvoiceItem->Order->find('first', array(
				'conditions' => array(
					'Order.id' => $id
				),
				'contain' => FALSE
			));
			$this->invoiceContext = array(
			'customer_id' => $order['Order']['user_customer_id'],
				'order_id' => $id
			);
			
		} elseif($alias == 'Customer') {
			$this->invoiceContext = array(
				'customer_id' => $id,
			);
			
		} else {
			$this->Flash->error("The alias $alias is unsupported");
		}
	}
	
	/**
	 * Fetch the invoice items for a specific order line item
	 *
	 * @param string $id, the OrderItem id
	 * @return array $this->invoiceItems
	 */
	private function fetchLiCharges($id) {
		$liCharges = $this->InvoiceItem->find('all', array(
			'conditions' => array(
				'InvoiceItem.order_item_id' => $id,
				'InvoiceItem.invoice_id IS NULL'
			), 
			'contain' => array(
				'Order'
			)
		));
		$orderItem = $this->InvoiceItem->Order->OrderItem->find('first', array(
			'conditions' => array(
				'OrderItem.id' => $id
			),
			'contain' => FALSE
		));
		$orderId = $orderItem['OrderItem']['order_id'];
		$this->invoiceItems = array(
			$orderId => $liCharges
		);
		$this->invoiceTotals[$orderId] = $this->InvoiceItem->fetchTotalOrderCharges($orderId);
	}
	
	/**
	 * Fetch the invoice items for an order
	 * 
	 * This function is also called in a loop for fetch all order items
	 * when fetching invoice items for a customer
	 * 
	 * @param string $id, the Order id
	 */
	private function fetchOrderCharges($id) {
		$orderCharges = $this->InvoiceItem->find('all', array(
			'conditions' => array(
				'InvoiceItem.order_id' => $id,
				'InvoiceItem.invoice_id IS NULL'
			),
			'contain' => array(
				'Order' => array(
                    'fields' => array(
                        'id',
                        'order_seed',
                        'order_number',
                        'status',
                        'budget_id',
                        'first_name',
                        'last_name',
                        'user_id',
                        'email',
                        'phone',
                        'billing_company',
                        'billing_address',
                        'billing_address2',
                        'billing_city',
                        'billing_zip',
                        'billing_state',
                        'billing_country',
                        'weight',
                        'order_item_count',
                        'subtotal',
                        'tax',
                        'shipping',
                        'total',
                        'order_type',
                        'authorization',
                        'transaction',
                        'ip_address',
                        'created',
                        'modified',
                        'user_customer_id',
                        'backorder_id',
                        'taxable',
                        'handling',
                        'note',
                        'order_reference',
                        'ship_date',
                        'exclude'
                    )
                )
			)
		));
		$this->invoiceItems[$id] = $orderCharges;
		$this->invoiceTotals[$id] = $this->InvoiceItem->fetchTotalOrderCharges($id);
	}
	
	/**
	 * Fetch all invoice items applied generally to the customer
	 * Plus fetch all storage charges for the customer
	 * 
	 * @param string $id, the customer's user id
	 */
	private function fetchGeneralCharges($id) {
		//check for exisiting storage charge
		$storage = $this->InvoiceItem->field('name', array(
				'InvoiceItem.customer_id' => $id,
				'InvoiceItem.name' => 'Storage',
				'InvoiceItem.invoice_id IS NULL'
			)
		);
		if (!$storage){
			//if there is no storage charge, create one
			$storageCharges['InvoiceItem'] = $this->InvoiceItem->Customer->fetchStorageCharges($id);
			$this->InvoiceItem->create();
			$save = $this->InvoiceItem->save($storageCharges);
			if ($save) {
				$storageCharges['id'] = $this->InvoiceItem->id;
			} else {
				$this->ddd($storageCharges, 'storageCharges');
				throw new BadRequestException('The general charges would not save.');
			}
		}
		//set property to the customer information
		$this->invoiceCustomer = $this->InvoiceItem->Customer->fetchCustomer($id);
		
		//set property to all general invoice items
		$this->invoiceItems['general'] = $this->InvoiceItem->find('all', array(
			'conditions' => array(
				'InvoiceItem.customer_id' => $id,
				'InvoiceItem.order_id IS NULL',
				'InvoiceItem.invoice_id IS NULL'
			),
			'contain' => false
		));
	}
	
	public function newInvoiceCharge() {
		$this->layout = 'ajax';
		$editable = array('quantity', 'description', 'unit', 'price');
		$this->InvoiceItem->create();
		$this->InvoiceItem->save($this->request->data);
		$id = $this->InvoiceItem->id;
		$this->InvoiceItem->recursive = -1;
		$new = $this->InvoiceItem->read();
		if($this->request->data['index'] == '#'){
			$this->set('index', 0);
			$index = 0;
		} else {
			$this->set('index', $this->request->data['index']);
			$index = $this->request->data['index'];
		}
		$index++;
		foreach ($new['InvoiceItem'] as $field => $value) {
			if (in_array($field, $editable)) {
				$resultField = Inflector::camelize($field);
				$result["InvoiceItem$index$resultField"] = $value;
			}
		}
		$this->set('new', $new);
		$this->set('result', $result);
	}
	
	/**
	 * Save a single field's data, and return a boolean of it's save status
	 * 
	 * This is called by javascript on the change of any InvoiceItem field.
	 * 
	 * <pre>
	 * array(
	 *	'field' => 'InvoiceItem2Unit', // id attribute of the field that changed
	 *	'InvoiceItem' => array(
	 *		2 => array(
	 *			'id' => '532b6da2-3501-45bb-b371-245b47139427',
	 *			'order_id' => '52d0842b-8a48-41a2-a773-4d5b47139427',
	 *			'order_item_id' => '',
	 *			'customer_id' => '10',
	 *			'unit' => 'packing'
	 *		)))
	 * </pre>
	 */
	public function saveInvoiceItemField(){
		$this->layout = 'ajax';
		
		// extract some values from posted data
		$index = key($this->request->data['InvoiceItem']);
		$field = Inflector::underscore(str_replace("InvoiceItem$index", '', $this->request->data['field']));
		$this->InvoiceItem->id = $this->request->data['InvoiceItem'][$index]['id'];
		
		$save = $this->InvoiceItem->saveField($field, $this->request->data['InvoiceItem'][$index][$field]);
		
		if ($save) {
			
			// read in the new subtotal
			$this->InvoiceItem->recursive = -1;
			$invoiceItem = $this->InvoiceItem->read(array('subtotal'));

			// prepare the new group header total and identify our target group
			if (empty($this->request->data['InvoiceItem'][$index]['order_id'])){
				$headerTotal = $this->InvoiceItem->fetchTotalGeneralCharges($this->request->data['InvoiceItem'][$index]['customer_id']);
				$headerId = 'general';
			} else {
				$headerTotal = $this->InvoiceItem->fetchTotalOrderCharges($this->request->data['InvoiceItem'][$index]['order_id']);
				$headerId = $this->request->data['InvoiceItem'][$index]['order_id'];
			}
			$invoiceTotal = $this->InvoiceItem->fetchTotalCharges($this->request->data['InvoiceItem'][$index]['customer_id']);
		
			$this->set('save', TRUE);
			$this->set('value', $this->request->data['InvoiceItem'][$index][$field]);
			$this->set(compact('index', 'headerTotal', 'headerId', 'invoiceTotal', 'invoiceItem' ));

		} else {
			
			$this->set('save', FALSE);
		}
	}

	/**
	 * Special call to save any charge items that didn't get saved earlier
	 * 
	 * Charge items normally save field-by-field (via ajax)
	 * But if anything goes wrong, when leaving the page (or closing the pallet)
	 * we can detect the unsaved fields. They'll get packaged up and
	 * sent here to make another attemp to save them all
	 */
	public function resave() {
		$this->autoRender = FALSE;
		$save = $this->InvoiceItem->saveAll($this->request->data);
		if($save){
			$return = array('return' => TRUE);
		} else {
			$return = array('return' => FALSE);
		}
		echo json_encode($return);
	}

}
