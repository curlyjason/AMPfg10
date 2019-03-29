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

class OrderToolsComponent extends Object {

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
	 * @return boolean
	 */
	public function saveOrder() {
		$this->parseUpsEmails();
		if(isset($this->controller->Order->data['Order']['order_reference'])){
			$ref1 = $this->controller->Order->data['Order']['order_reference'];
		} else {
			$ref1 = '';
		}
		$save = $this->controller->Order->saveAll($this->controller->Order->data);
		if ($save) {
			//update order_number
			$order_number = $this->controller->Order->getOrderNumber($this->controller->Order->id);
			if($order_number){
				$this->controller->Order->saveField('order_number', $order_number);
				$this->controller->Order->Shipment->saveField('shipment_code', $order_number);
				$this->controller->Order->Shipment->saveField('ship_ref1', $ref1);
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
//			$this->controller->jsonReturn = $this->controller->requestAction(array('controller' => 'orders', 'action' => 'updateOrder', $id , 'robot' => $robot));
			
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
	
	public function parseUpsEmails() {
		$e = explode(',', $this->controller->Order->data['Shipment'][0]['email']);
		$i = 0;
		while($i<3){
			$j=$i+1;
			$this->controller->Order->data['Shipment'][0]["ups_email$j"] = (isset($e[$i]) && stristr($e[$i], '@')) ? trim($e[$i]) : '';
			$this->controller->Order->data['Shipment'][0]["ups_flag$j"] = (isset($e[$i]) && stristr($e[$i], '@')) ? 'y' : 'n';
			$i++;			
		}
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
//		debug(json_encode($this->controller->jsonReturn));
//		die;
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
