<?php
/**
 * Robot Transaction Concrete Implementation for Xml Replenishment submission
 *
 * Concrete Class implementing the automated submission
 * of Replenishments via Xml
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author dondrake
 */
//App::uses('AppController', 'Controller');
App::uses('Item', 'Model');
App::uses('XmlArrayFromPrintArray', 'Lib');
App::uses('XmlRobotIO', 'Controller/Template');

/**
 * Robot Transaction Concrete Implementation for Xml Replenishment submission
 *
 * Concrete Class implementing the automated
 * submission of Replenishments via Xml
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author dondrake
 */
class XmlReplenishmentsController extends XmlRobotIO {
	
	public $uses = array('Replenishment');
	
	protected $model = 'Replenishment';
	
	protected $mode = 'replenishment';
	
	protected $xsd = 'xmlReplenishment.xsd';
	
	protected $itemList;
	
	protected $pendingItems;

	public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('input');
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
	 * Concrete implementation for XML orders
	 * 
	 * Handles gross reorganization of the data property and
	 * look up and add secure data user could not provide
	 * while providing deeper validation that specific products
	 * ordered belong to the customer
	 */
	public function transformRequest() {
		$this->transformXmlArray();
		$this->addSecureRecordData();
	}
	
	public function transformXmlArray(){
		// move the data into save array(s)
		$this->Replenishment->data['ReplenishmentItem'] = $this->Replenishment->data['Replenishment']['ReplenishmentItem'];
		unset($this->Replenishment->data['Replenishment']['ReplenishmentItem']);
		unset($this->Replenishment->data['Credentials']);
		return TRUE;
	}

	public function addSecureRecordData() {
		// verify all items are on the customer's catalog list
		$this->itemList = $this->User->Catalog->fetchItemList($this->validCustomer['Catalog']['id']);
		foreach ($this->Replenishment->data['ReplenishmentItem'] as $index => $replenishmentItem) {
			if(!in_array($replenishmentItem['item_id'], $this->itemList)){
				throw new RobotProcessException($this->xmlError("Item {$this->Replenishment->data['ReplenishmentItem'][$index]['name']} with item id {$replenishmentItem['item_id']} is not in the customer's catalog."));
			}
			//add item to pendingItems
			$this->pendingItems[] = $this->Replenishment->data['ReplenishmentItem'][$index]['item_id'];
		}
		// fill in data the user couldn't provide
		$this->Replenishment->set('Replenishment.user_id', $this->validCustomer['User']['id']);
		$address = array_intersect_key($this->validCustomer['Address'], array_flip(array('id', 'company', 'address', 'address2', 'city', 'state', 'zip', 'country')));
		
		$this->Replenishment->data['Replenishment']['status'] = 'Open';
		foreach ($address as $key => $value) {
			$this->Replenishment->data['Replenishment']["vendor_$key"] = $value;
		}
		$this->Replenishment->set('ReplenishmentItem.{n}.po_quantity', '1');
		$this->Replenishment->set('ReplenishmentItem.{n}.po_unit', 'ea');

		return TRUE;
	}
	
	public function processRequest() {
		$s = $this->Replenishment->saveAll($this->Replenishment->data);
		$o = $this->Replenishment->getReplenishmentNumber($this->Replenishment->id);
		$this->Replenishment->saveField('order_number', $o);
		
		$this->getEventManager()->attach($this->Replenishment->ReplenishmentItem->Item);
		$event = new CakeEvent('Model.ReplenishmentItem.quantityChange', $this, $this->pendingItems);
		$this->getEventManager()->dispatch($event);
		
		return $s;
	}
	
	/**
	 * Prepare final order response for an xml robot call
	 */
	public function respond() {
		// assemble return response
		$replenishment = $this->Replenishment->getReplenishmentForPrint($this->Replenishment->id);
		$transform = new XmlArrayFromPrintArray($this->model);
		$this->Replenishment->data = $transform->xmlArrayFromPrintArray($replenishment);
		$response = Xml::fromArray($this->Replenishment->data);
		$this->set('response', $response->asXML());
		CakeLog::write('robotIO', "XML replenishment from {$this->validCustomer['Customer']['name']} processed successfully.");
		return true;
	}

	public function testMe(){
		$ar = array ('Body' =>
			array(
				'Credentials' => array(
					'company' => 'Sad New Vistas in Testing',
					'token' => 'd27889affe5f30432a3723a5214d3d23363e'
				),
				'Replenishment' => array(
					'ReplenishmentItem' => array(
						187 => array(
							'index' => '0',
							'item_id' => '187',
							'name' => 'Bag',
							'quantity' => '1',
						),
						97 => array(
							'index' => '1',
							'item_id' => '97',
							'name' => 'Dark Blue Purses',
							'quantity' => '1',
						),
						91 => array(
							'index' => '2',
							'item_id' => '91',
							'name' => 'Black/Brown Purses',
							'quantity' => '1',
						)
					)
				)
			)
		);
		
		$xmlObj = Xml::fromArray($ar);
		debug($xmlObj->asXML());
		
	}
}
