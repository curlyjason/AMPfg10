<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Component
 * @author dondrake
 */

App::uses('CakeEvent', 'Event');
App::uses('InventoryEvent', 'Lib');

class RobotOrderToolsComponent extends Object {



    public $components = array();
	public $settings = array();

	function initialize(&$controller) {
		$this->controller = $controller;
		$this->InventoryEvent = new InventoryEvent;
		$this->controller->getEventManager()->attach($this->InventoryEvent);
//		$this->settings = $settings;
	}

	function startup(&$controller) {
		
	}

	function beforeRender() {
		
	}

	function beforeRedirect() {
		
	}

	function shutDown(&$controller) {
		
	}

	/**
	 * Save a robot-submitted Order
     *
     * Array passed:
     *
     * array(
        'billing_company' => 'Sad New Vistas in Testing',
        'first_name' => 'Jason',
        'last_name' => 'Tempestini',
        'phone' => '925-895-4468',
        'billing_address' => '1107 Fountain Street',
        'billing_address2' => '',
        'billing_city' => 'Alameda',
        'billing_state' => 'CA',
        'billing_zip' => '94501',
        'billing_country' => 'US',
        'order_reference' => 'order2',
        'note' => 'This is a note for this shipment.',
        'OrderItems' => array(
            'OrderItem' => array(
                (int) 0 => array(
                    'item_id' => '45',
                    'name' => 'Eucalyptus',
                    'sell_quantity' => '1',
                    'sell_unit' => 'ea',
                    'price' => '0.00',
                    'type' => '4',
                    'catalog_id' => '52',
                    'quantity' => '10',
                    'catalogType' => '4',
                    'each_quantity' => (int) 10,
                    'subtotal' => (float) 0
                ),
                (int) 1 => array(
                    'item_id' => '48',
                    'name' => 'Ball Cap',
                    'sell_quantity' => '1',
                    'sell_unit' => 'ea',
                    'price' => '0.00',
                    'type' => '4',
                    'catalog_id' => '56',
                    'quantity' => '10',
                    'catalogType' => '4',
                    'each_quantity' => (int) 10,
                    'subtotal' => (float) 0
                )
            )
        ),
        'Shipment' => array(
            (int) 0 => array(
                'billing' => 'Sender',
                'carrier' => 'UPS',
                'method' => '1DA',
                'billing_account' => '',
                'first_name' => 'Jason',
                'last_name' => 'Tempestini',
                'email' => 'jason@tempestinis.com',
                'phone' => '925-895-4468',
                'company' => 'Curly Media',
                'address' => '1107 Fountain Street',
                'address2' => '',
                'city' => 'Alameda',
                'state' => 'CA',
                'zip' => '94501',
                'country' => 'US',
                'tpb_company' => '',
                'tpb_address' => '',
                'tpb_city' => '',
                'tpb_state' => '',
                'tpb_zip' => '',
                'tpb_phone' => ''
            )
        ),
        'user_customer_id' => '25',
        'user_id' => '25',
        'order_type' => 'robot',
        'status' => 'Submitted'
    )
	 *
     * @param $order{}
	 * @return boolean
	 */
	public function saveOrder($order) {

	    $this->controller->jsonReturn = [];
		$save = $this->controller->Order->saveAll($order->getOrder());
		if ($save) {
			//update order_number
			$order_number = $this->controller->Order->getOrderNumber($this->controller->Order->id);
			if($order_number){
				$this->controller->Order->saveField('order_number', $order_number);
				$this->controller->Order->Shipment->saveField('shipment_code', $order_number);
				//Also update the object
                $order->setOrderNumber($order_number);
                $order->setOrderId($this->controller->Order->id);
                //And update the Shipment for the blank tracking number for response
                $order->setTrackingNumber('na');
                $order->setShippingCost(0.00);
			} else {
				$this->controller->Order->removeOrder($this->controller->Order->id);
			}


			$this->controller->set(compact('shop'));
			$id = $this->controller->Order->id;
			$status = 'Submit';
			
			if($this->controller->Auth->user('group') == NULL){
				$robot = TRUE;
				$redirect = '';
				$userId = '';
			} else {
				$robot = FALSE;
				$this->controller->Cart->clear();
				$redirect = $this->controller->Auth->user('group').'/status';
				$userId = $this->controller->Auth->user('id');
			}

			$this->controller->Catalog->Item->manageUncommittedSeries($this->controller->uncommittedItems);
			$this->controller->request->params['named']['robot'] = $robot;
			$this->controller->jsonReturn = $this->updateOrder($id);

			// send potential low inventory notifications
			$event = new CakeEvent('Item.Availability', $this->controller->Item->AvailableEntries);
			$this->controller->getEventManager()->dispatch($event);

			$this->controller->Order->Document->moveDocs($this->controller->Order->id, $userId);
			
			return $this->controller->requestAction(array('controller' => 'orders', 'action' => 'statusChange', 'robot' => $robot),array('pass' => array($id,$status,$redirect)));
			
		} else {
			$this->controller->ddd($save, 'save');
			$this->controller->ddd($this->controller->saveArray, 'save array');
			$this->controller->ddd($this->controller->Order->validationErrors, 'validation errors');
			$errors = $this->controller->Order->invalidFields();
			$this->controller->ddd($errors, 'invalidFields()');
			die;
			$this->controller->set(compact('errors'));
			$this->controller->redirect(array('controller' => 'shop', 'action' => 'address'));
		}
		return TRUE;
	}

	/**
	 * Establish all the Order dollar values or delete the order
	 * 
	 * Read in the Order Items and related data
	 * Set all the accumulator values in the order
	 * Save the updated Order
	 * Resolve the user's budget
	 * Return json data reflecting the new values
	 * 
	 * @param string $id order id
	 * @param boolean $remove indicates a request to delete an order
	 * @return mixed json data or false if the order is zapped
	 * @throws NotFoundException
	 */
	public function updateOrder($id, $remove = false) {
		if (!$this->controller->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		$order = $this->controller->Order->withItemAndShipSums($id);
		if (empty($order['OrderItem']) || $remove) {
			$this->clearOrder($id);
		} else {
			$this->controller->Order->create($order['Order']);

			$this->controller->Order->set('ItemCount',			$order['OrderItem'][0]['OrderItem'][0]['COUNT(id)'] );
			
			$this->controller->Order->set('ItemSubtotalSum',	$order['OrderItem'][0]['OrderItem'][0]['SUM(subtotal)'] );
			$this->controller->Order->set('ItemWeightSum',		$order['OrderItem'][0]['OrderItem'][0]['SUM(weight)'] );
			$this->controller->Order->set('ShipmentSum',		$order['Shipment'][0]['Shipment'][0]['SUM(shipment_cost)'] );
			
			// These two lines can be refactored out. We're not doing tax any more 6/19/14
			$this->controller->Order->set('ShipmentTaxPercent', $order['Shipment'][0]['tax_percent'] );
			$this->controller->Order->set('Taxable',			$order['Order']['taxable'] );

			$order['Order'] = array_merge($order['Order'], $this->controller->Order->createTotalsFromSums());


			
			$this->controller->Order->save($order);
		}

		// this only works on the logged in user. May not have meaning in this context
//		$this->controller->installComponent('Budget');
		$this->Budget = $this->controller->Components->load('Budget');
		$this->Budget->initialize($this->controller);
		$budget = $this->Budget->refreshBudget($order['Order']['budget_id']);
//		$budget = $this->Order->refreshBudget($order['Order']['budget_id']);

		$this->controller->jsonReturn = array_merge($this->controller->jsonReturn, $budget, $order);
		if (!isset($this->controller->request->params['named']['robot']) || (isset($this->controller->request->params['named']['robot']) && !$this->controller->request->params['named']['robot'])) {
			echo json_encode($this->controller->jsonReturn);
		}//		debug($this->controller->jsonReturn);
		return json_encode($this->controller->jsonReturn);
	}

/**
 * When an order has no more items it should be deleted
 * 
 * Order and Shippments should be dumped
 * Make sure OrderItems are gone too
 * 
 * @param string $id The order to eliminate
 * @return json Data to return to update page display
 */
	private function clearOrder($id) {
		$this->controller->Order->removeOrder($id);
		$this->controller->jsonReturn = array_merge($this->controller->jsonReturn, array('deletedOrder' => $id));
		return;
	}
	
}
