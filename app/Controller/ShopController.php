<?php

App::uses('AppController', 'Controller');
App::uses('CakeEvent', 'Event');
App::uses('InventoryEvent', 'Lib');


class ShopController extends AppController {
    public $components = array(
        'Cart'
    );

    public $uses = array('Item', 'Customer', 'TaxRate', 'Order', 'Document');
	
	public $uncommittedItems = array();

    //============================================================
    // USER ACCESS MANAGEMENT
    //============================================================

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('xmlOrderSubmit');
        $this->disableCache();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('all');
		$this->accessPattern['Guest'] = array ('all');
    }
	
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }
    
    public function clear() {
        $this->Cart->clear();
        $this->Session->setFlash('All item(s) removed from your shopping cart');
        return $this->redirect('/catalogs/shopping');
    }

    public function add() {
        if ($this->request->is('post')) {
            $id = $this->request->data['Item']['id'];
            $product = $this->Cart->add($id, 1);
        }
        if (!empty($product)) {
            $this->Flash->success($product['Item']['name'] . ' was added to your shopping cart.');
        }
        return $this->redirect($this->referer());
    }

    /**
     * Reflect a new item qty in the cart and budget
     * 
     * Probably this is ONLY an ajax call
     * Fix the cart, fix the budget, send all data back
     * so the page display can be resolved
     */
    public function itemupdate() {
        $this->autoRender = false;
        if ($this->request->is('ajax')) {
            $add = $this->Cart->add($this->request->data['id'], $this->request->data['quantity']);
            if(!$add){
				$company = $this->Session->read('Shop.Customer.name');
				echo json_encode(array('error' => "You've alread started shoping from $company's catalog. You can't mix in items from this catalog."));
                return;
            }
        }
        $cart = $this->Session->read('Shop');
		$this->installComponent('Budget');
		$budget = $this->Budget->refreshBudget();
        echo json_encode(array_merge($cart, $budget, $this->Item->available));
    }

    public function update() {
        $this->Cart->update($this->request->data['Item']['id'], 1);
    }

    public function remove($id = null) {
        $product = $this->Cart->remove($id);
        if (!empty($product)) {
            $this->Flash->error($product['Item']['name'] . ' was removed from your shopping cart');
        }
        return $this->redirect(array('action' => 'cart'));
    }

    public function cartupdate() {
        if ($this->request->is('post')) {
            foreach ($this->request->data['Item'] as $key => $value) {
                $p = explode('-', $key);
                $this->Cart->add($p[1], $value);
            }
			$this->installComponent('Budget');
			$this->Budget->updateRemainingBudget();
            $this->Flash->success('Shopping Cart is updated.');
        }
        return $this->redirect(array('action' => 'cart'));
    }

    public function cart() {
		//determine handling and write to session
		$this->loadModel('Price');
        $temp = $this->Session->read('Shop');
		$handlingFee = $this->Price->fetchPullFee($temp['Customer']['id'], $temp['Order']['quantity']);
		$this->Session->write('Shop.Order.handling', $handlingFee);
		
		//add available quantities to the session
		$this->updateAvailableQty();
		
		//pull revised session for display
        $shop = $this->Session->read('Shop');
		
		if (!isset($shop['OrderItem'])) {
			$this->Session->setFlash('The cart had no items in it. If this doesn\'t seem right, please contact your system administrator.');
			$this->redirect($this->referer());
		}
		//setup order note from the first cart item
		$index = array_keys($shop['OrderItem']);
		$shop['Order']['note'] = $shop['OrderItem'][$index[0]]['note'];
		
		//add kit data to shop
		$shop = $this->Order->injectKitData($shop);
		
        $pageHeading = 'Shopping Cart';
		$itemLimitBudget = $this->Auth->user('use_item_limit_budget');
        $this->set(compact('shop', 'pageHeading', 'itemLimitBudget'));
    }
	
	private function pullDocuments() {
		$this->Document = ClassRegistry::init('Document');
		$docs = $this->Document->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Document.user_id' => $this->Auth->user('id')
			)
		));
		if (!empty($docs) && $docs) {
			foreach ($docs as $doc) {
				$this->request->data['Document'][] = $doc['Document'];
			}
		}
	}

	private function updateAvailableQty(){
		$orderItems = $this->Session->read('Shop.OrderItem');
		
		foreach ($orderItems as $index => $orderItem) {
			$this->Session->write('Shop.OrderItem.'.$index.'.Item.available_qty', $this->Item->field('available_qty', array('Item.id' => $orderItem['item_id'])));
		}
	}

    public function googlecheckout() {
        $this->helpers[] = 'Google';
        $shop = $this->Session->read('Shop');
        $this->set(compact('shop'));
    }
	
	/**
	 * Ajax Sessionization of Note and Ref# from address page
	 */
	public function saveNoteAndReference() {
		if (
			$this->Session->write('Shop.Order.note', $this->request->data['Order']['note'])
			&& $this->Session->write('Shop.Order.order_reference', $this->request->data['Order']['order_reference'])
			&& $this->Session->write('Shop.Order.reference_approval', $this->request->data['Order']['reference_approval'])
		) {
			echo json_encode(array('save' => TRUE));
		} else {
			echo json_encode(array('save' => FALSE));
		}
		exit();
	}

    public function address() {
        $shop = $this->Session->read('Shop');
        if (!$shop['Order']['order_item_count']) {
            return $this->redirect(array('controller' => 'catalogs', 'action' => 'shopping'));
        }
            $this->loadModel('Order');
            $this->loadModel('Shipment');

        if ($this->request->is('post')) {
			
			if (isset($this->request->data['Document']) && !empty($this->request->data['Document'])) {
				$this->Document = ClassRegistry::init('Document');
				$this->linkDocumentsToUser();
				$docSave = $this->Document->saveMany($this->request->data['Document']);
			}
			
            //setup third party billing address for UPS, if any
            //for Customer and Receiver billing, we hold setting the tpb address until just before save
			//for Sender billing, we clear all fields before saving
			//for ThirdParty billing types, we have the address in the fields already
            if ($this->request->data['Shipment']['carrier'] == 'UPS') {
				$this->setTpb();
            }    
			
            //setup shipment for validation
            $this->Shipment->set($this->request->data['Shipment']);
            
            //write the shipment & order to the session for retreival, even if the data doesn't validate
            $shipment = $this->request->data['Shipment'];
            $this->Session->write('Shop.Shipment', $shipment);
            $sessionOrder = $this->request->data['Order'];
            $this->Session->write('Shop.Order', $sessionOrder);
			
			//check shipment validation
            if ($this->Shipment->validates()) {
				//write the update address to the user's address book, if requested
				if($this->request->data['Shipment']['save_to_my_address_book'] == 1){
					$address = $this->request->data['Shipment'];
					$address['type'] = 'shipping';
					if($address['company']){
						$address['name'] = $address['company'];
					} else {
						$address['name'] = $address['first_name'] . ' ' . $address['last_name'];
					}
					if($this->request->data['Order']['selectedAddressSource'] == 'OrderMyAddresses' || $this->request->data['Order']['access'] == 'Manager'){
						//if I'm a manager, or this is from my address book, set address up for potential update
						$address['id'] = $this->request->data['Order']['selectedAddress'];
					}
					if($address['id']==null){
						//if there's no update (there's no id), make sure the user id is set so the address shows up on the 'My' list
						$address['user_id'] = $this->Auth->user('id');
					}
					$this->User->Address->create();
					$this->User->Address->save($address);
					if($this->User->Address->created){
						//if we create a new record, set session appropriately for potential return to address page
						$this->Session->write('Shop.Order.selectedAddressSource', 'OrderMyAddresses');
						$this->Session->write('Shop.Order.selectedAddress', $this->User->Address->id);
						$this->Session->write('Shop.Order.myAddresses', $this->User->Address->id);
						$this->Session->write('Shop.Shipment.save_to_my_address_book', 0);
					}
				}
				
				//write order type to session and proceed to review
                $this->Session->write('Shop.Order.order_type', 'creditcard');
                return $this->redirect(array('action' => 'review'));
            } else {
                $validationMessages = $this->Shipment->fetchModelValidationMessage($this->Shipment->validationErrors);
                $this->Session->setFlash($validationMessages, 'validationError');
                return $this->redirect(array('action' => 'address'));
            }
        }
        if (!empty($shop['Order'])) {
            $this->request->data['Order'] = $shop['Order'];
        }
        if (!empty($shop['Shipment'])) {
            $this->request->data['Shipment'] = $shop['Shipment'];
        }
		//pull any documents
		$this->pullDocuments();

		// if you're arriving through the normal cart front end,
		// referer will be shop/cart. Returning to correct errors
		// or coming back from review, referer will be other locs.
		// only in the first case do we want to pull and use customer
		// shipping prefs. In other cases we might overwrite user data
		$customerId = $this->Session->read('Shop.Customer.user_id');
		$shippingPrefs = $this->Session->read("Prefs.ship.$customerId.customer");
		if (stristr($this->referer(), 'shop/cart') && $shippingPrefs) {
			$this->request->data['Shipment']['carrier'] = $shippingPrefs['carrier'];
			$this->request->data['Shipment']['method'] = $shippingPrefs['method'];
			$this->request->data['Shipment']['billing'] = $shippingPrefs['billing'];
		}
        $myAddresses = ($this->User->getSecureList($this->Auth->user('id'), 'myAddresses'));
        $connectedAddresses = ($this->User->getSecureList($this->Auth->user('id'), 'connectedAddresses'));
        $thirdParty = ($this->User->getSecureList($this->Auth->user('id'), 'thirdParty'));
        $customer = $this->Session->read('Shop.Customer.user_id');
        $this->billingAddress($customer, $this->secureHash($customer));
        $permittedCustomers = $this->secureSelect($customer);
        $pageHeading = $title_for_layout = 'Billing & Shipping Addresses';
        $carrier = $this->Order->carrier;
		$UPS = $this->Order->method['UPS'];
		$FedEx = $this->Order->method['FedEx'];
		$Other = $this->Order->method['Other'];
		if (isset($this->request->data['Shipment']['carrier']) && isset($this->Order->method[$this->request->data['Shipment']['carrier']])) {
			$method = array($this->Order->method[$this->request->data['Shipment']['carrier']]);
		} else {
			$method = array();
		}
		$shipmentBillingOptions = $this->getShipmentBillingOptions();
		
		$this->setBasicAddressSelects();
        $this->set(compact(
				'shop',
                'myAddresses', 
                'connectedAddresses', 
                'permittedCustomers', 
                'pageHeading', 
                'title_for_layout', 
                'carrier', 
                'method',
				'UPS',
				'FedEx',
				'Other',
				'shipmentBillingOptions',
				'thirdParty'));
		
        $this->layout = 'timed_simple';
        $this->render('address');
    }

	/**
	 * Link new document records to the logged in user id
	 * 
	 */
	private function linkDocumentsToUser() {
		$u = $this->Auth->user('id');
		foreach ($this->request->data['Document'] as $index => $record) {
			$this->request->data['Document'][$index]['user_id'] = $u;
		}
	}
	
	/**
	 * Set proper third party billing when Order is shipped UPS
	 */
	protected function setTpb() {
		switch ($this->request->data['Shipment']['billing']) {
			case 'Receiver':
				$this->request->data['Shipment']['tpb_company'] = $this->request->data['Order']['billing_company'];
				$this->request->data['Shipment']['tpb_address'] = $this->request->data['Order']['billing_address'];
				$this->request->data['Shipment']['tpb_city'] = $this->request->data['Order']['billing_city'];
				$this->request->data['Shipment']['tpb_state'] = $this->request->data['Order']['billing_state'];
				$this->request->data['Shipment']['tpb_zip'] = $this->request->data['Order']['billing_zip'];
		//      $this->request->data['Shipment']['tpb_phone'] = $this->request->data['Order']['billing_phone'];
				break;

			case 'ThirdParty':
				$this->request->data['Shipment']['tpb_company'] = $this->request->data['Shipment']['company'];
				$this->request->data['Shipment']['tpb_address'] = $this->request->data['Shipment']['address'];
				$this->request->data['Shipment']['tpb_city'] = $this->request->data['Shipment']['city'];
				$this->request->data['Shipment']['tpb_state'] = $this->request->data['Shipment']['state'];
				$this->request->data['Shipment']['tpb_zip'] = $this->request->data['Shipment']['zip'];
				$this->request->data['Shipment']['tpb_phone'] = $this->request->data['Shipment']['phone'];
				break;

			case 'Customer':
			default:
				$this->request->data['Shipment']['billing_account'] = '';
				$this->request->data['Shipment']['tpb_company'] = '';
				$this->request->data['Shipment']['tpb_address'] = '';
				$this->request->data['Shipment']['tpb_city'] = '';
				$this->request->data['Shipment']['tpb_state'] = '';
				$this->request->data['Shipment']['tpb_zip'] = '';
				$this->request->data['Shipment']['tpb_phone'] = '';
				break;
		}
	}

	/**
     * Returns the customer address for the selected customer
     * 
     * Provided with the id & hash of a chosen customer
     * this function finds the attached address for the customer
     * 
     * @param string $id
     * @param string $hash
     */
    public function billingAddress($id, $hash) {
        $billingAddress = array();
        $this->layout = 'ajax';
        if ($this->secureId($id, $hash)) {
            $billingAddress = $this->Customer->find('first', array(
                'conditions' => array(
                    'Customer.user_id' => $id
                )
            ));
            if (empty($billingAddress)) {
                $this->Session->setFlash('No address was returned for this customer');
            }
        } else {
            $this->Session->setFlash('There was a security hash violation on the customer id');
        }
        $this->set(compact('billingAddress'));
    }

    public function step1() {
        $paymentAmount = $this->Session->read('Shop.Order.total');
        if (!$paymentAmount) {
            return $this->redirect('/');
        }
        $this->Session->write('Shop.Order.order_type', 'paypal');
        $this->Paypal->step1($paymentAmount);
    }

    public function step2() {

        $token = $this->request->query['token'];
        $paypal = $this->Paypal->GetShippingDetails($token);

        $ack = strtoupper($paypal['ACK']);
        if ($ack == 'SUCCESS' || $ack == 'SUCESSWITHWARNING') {
            $this->Session->write('Shop.Paypal.Details', $paypal);
            return $this->redirect(array('action' => 'review'));
        } else {
            $ErrorCode = urldecode($paypal['L_ERRORCODE0']);
            $ErrorShortMsg = urldecode($paypal['L_SHORTMESSAGE0']);
            $ErrorLongMsg = urldecode($paypal['L_LONGMESSAGE0']);
            $ErrorSeverityCode = urldecode($paypal['L_SEVERITYCODE0']);
            echo 'GetExpressCheckoutDetails API call failed. ';
            echo 'Detailed Error Message: ' . $ErrorLongMsg;
            echo 'Short Error Message: ' . $ErrorShortMsg;
            echo 'Error Code: ' . $ErrorCode;
            echo 'Error Severity Code: ' . $ErrorSeverityCode;
            die();
        }
    }

    public function review() {
        $pageHeading = $title_for_layout = 'Order Review';
        $this->set(compact('pageHeading', 'title_for_layout'));
		
		//add available quantities to the session
		$this->updateAvailableQty();
		
        $shop = $this->Session->read('Shop');
		
		//setup order note from the first cart item
		$index = array_keys($shop['OrderItem']);

		//add kit data to shop
		$shop = $this->Order->injectKitData($shop);
		
        if (empty($shop)) {
            return $this->redirect(array('controller' => 'catalogs', 'action' => 'shopping'));
        }

        if ($this->request->is('post')) {
            $this->Order->set($shop['Order']);
            
            if ($this->Order->validates()) {
                $order = $shop;
                $order['Order']['status'] = 'Submitted';
                $order['Order']['user_id'] = $this->Auth->user('id');
				
 				$this->orderTax = $this->TaxRate->getTaxRate($order['Shipment']['city'], $order['Shipment']['state']);
                $order['Shipment']['tax_jurisdiction'] = $this->orderTax['tax_jurisdiction'];
                $order['Shipment']['tax_percent'] = $this->orderTax['tax_rate'];
 				$order['Order']['tax'] = $this->orderTotal * $this->orderTax['tax_rate'] * $order['Customer']['taxable'];
				$order['Order']['total'] = $order['Order']['subtotal'] + $order['Order']['tax'];
				
               if ($this->Auth->user('use_budget') || $this->Auth->user('use_item_budget')) {
                    $order['Order']['budget_id'] = $this->Auth->user('budget_id');
                }

                unset($order['Customer']);
				
                foreach ($order['OrderItem'] as $key => $record) {
					$order['OrderItem'][$key]['type'] = $order['OrderItem'][$key]['Catalog']['type'] & (KIT | PRODUCT | COMPONENT);
					$order['OrderItem'][$key]['catalog_type'] = $order['OrderItem'][$key]['Catalog']['type'];
                    unset($order['OrderItem'][$key]['Item']);
                    unset($order['OrderItem'][$key]['Image']);
                    unset($order['OrderItem'][$key]['Catalog']);
                    unset($order['OrderItem'][$key]['available_qty']);
					
					$this->uncommittedItems[] = $order['OrderItem'][$key]['item_id'];
                }
                $shipment = $order['Shipment'];
                unset($order['Shipment']);
                $order['Shipment'][0] = $shipment;
				$this->Order->data = $order;
				$this->installComponent('OrderTools');
				if(!$this->OrderTools->saveOrder()){
					throw new FailedSaveException('Requested Order did not save.');
				}
				//if OrderTools->saveOrder succeeds, it redirects and never returns here, unless it is a robot call
				
            } else {
                $this->Session->setFlash('Validation errors occured');
                $this->redirect(array('controller' => 'shop', 'action' => 'address'));
            }
        }
		
		//pull any documents
		$this->pullDocuments();

        $this->set('displayMethods', $this->Customer->User->Order->method);
        $this->set(compact('shop'));
    }

    public function success() {
        $shop = $this->Session->read('Shop');
        $this->Cart->clear();
        if (empty($shop)) {
            return $this->redirect('/');
        }
        $this->set(compact('shop'));
    }
	
}
