<?php
/**
 * Robot Transaction Concrete Implementation for Xml Order submission
 *
 * Concrete Class implementing the automated
 * submission of Orders
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author dondrake
 */
App::uses('RobotIO', 'Controller/Template');
App::uses('CakeEvent', 'Event');
App::uses('InventoryEvent', 'Lib');
App::uses('XMLOrders', 'Lib');
App::uses('JSONOrders', 'Lib');

/**
 * Robot Transaction Concrete Implementation for Xml Order submission
 *
 * Concrete Class implementing the automated
 * submission of Orders via Xml
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author dondrake
 */
class RobotOrdersController extends RobotIO {

    public $uses = array('Order');
    protected $model = 'Order';

    public $RobotOrders;

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
	 * Migrate data from supplied data type to object
	 *
	 */
	public function migrateRequest() {
	    //set mode based upon request type and being in the Order flow
        $mode = $this->requestType.'Order';

	    //create a classname based upon the type of request (ie XMLOrders or JSONOrders)
	    $className = $this->requestType.'Orders';

	    //instantiate the RobotOrders object with this derived concrete object name
	    $this->RobotOrders = new $className($this->RobotCredential);

	    //use the RobotOrders object to migrate the request
	    $this->RobotOrders->migrateRequest($this->request->data, $mode);

        //Setup RobotCredential object with proper company data
        $this->RobotCredential->setCredential($this->RobotOrders->getCredential('company'), $this->RobotOrders->getCredential('token'), $mode);
    }

    public function marshallRequest()
    {
        $this->RobotOrders->marshallPackets();
	}

	/**
	 * Save a robot-submitted Order
     *
     * Using RobotOrderTools, step through RobotOrders' order object, pulling the order element
     * from each order object to save.
     *
     * RobotOrders->getPackets() produces an array of RobotOrder objects
     * RobotOrder->getOrder() (note the singular) produces an array of a single order
	 * 
	 * @return boolean
	 */
	public function processRequest() {
		$this->installComponent('RobotOrderTools');
		$this->Item = $this->Catalog->Item;
        foreach ($this->RobotOrders->getPackets() as $order) {
            if (!$order->hasError()){
                if (!$this->RobotOrderTools->saveOrder($order)) {
                    $order->setErrorProperties('order_reference', $order->getOrderRef(), 5001);
                }
            }
        }
        return TRUE;
    }

	/**
	 * Prepare final order response for an robot call
	 */
	public function respond() {
	    $this->set('response', $this->RobotOrders->getResponse());
		CakeLog::write('robotIO', "$this->requestType order from {$this->RobotCredential->getName()} processed successfully.
		Here's the response. {$this->RobotOrders->getResponse()}");
		return true;
	}
	
}