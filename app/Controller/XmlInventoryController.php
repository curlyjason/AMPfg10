<?php
App::uses('AppController', 'Controller');
App::uses('XmlRobotIO', 'Controller/Template');

/**
 * CakePHP XmlInventoryController
 * 
 * This is the Robot clients call point for a full inventory report
 * 
 * @author dondrake
 */
class XmlInventoryController extends XmlRobotIO {
	
	public $uses = array('Item');
	
	public $mode = 'inventory report';
	
	public $model = 'Item';

	public $inventory;
	
	public $xsd = 'xmlInventory.xsd';

	public function beforeFilter() {
        parent::beforeFilter();
//        $this->Auth->allow('output');
        $this->disableCache();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('all');
		$this->accessPattern['Guest'] = array ('all');
    }
	
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

	/**
	 * Pull the current invetory snapshot for the requesting customer
	 * 
	 * Includes quantity, available_qty, pending
	 * for a complete report of current conditions
	 */
	public function processRequest() {
		$data = $this->Item->customerInventorySnapshot($this->validCustomer['User']['id']);
		$this->Item->data = array(
			'Body' => array(
				'Customer' => $this->validCustomer['Customer']['name'],
				'Date' => date('r', time()),
				'Items' => $data['Items']
			));
	}
	
	public function transformRequest() {
		// un-needed
	}
	
	/**
	 * Return the xml data report to the requesting user and log success
	 * 
	 * @return boolean
	 */
	public function respond() {
		$response = Xml::fromArray($this->Item->data);
		$this->set('response', $response->asXML());
		CakeLog::write('robotIO', "XML $this->mode from {$this->validCustomer['Customer']['name']} processed successfully.");
		return true;
	}

}