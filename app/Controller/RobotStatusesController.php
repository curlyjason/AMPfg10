<?php
/**
 * Robot Transaction Concrete Implementation for Robot Status requests
 *
 * Concrete Class implementing the automated
 * submission of status requests
 *
 * PHP 5
 *
 * @package       Robot.Xml
 * @author dondrake
 */
App::uses('RobotIO', 'Controller/Template');
App::uses('CakeEvent', 'Event');
App::uses('InventoryEvent', 'Lib');
App::uses('XMLStatuses', 'Lib');
App::uses('JSONStatuses', 'Lib');

class RobotStatusesController extends RobotIO {

	public $RobotStatuses;

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
        $this->mode = $this->requestType.'Status';

	    //create a classname based upon the type of request (ie XMLOrders or JSONOrders)
	    $className = $this->requestType.'Statuses';

	    //instantiate the RobotStatus object with this derived concrete object name
	    $this->RobotStatuses = new $className($this->RobotCredential);

	    //use the RobotStatus object to migrate the request
	    $this->RobotStatuses->migrateRequest($this->request->data, $this->mode);

        //Setup RobotCredential object with proper company data
        $this->RobotCredential->setCredential($this->RobotStatuses->getCredential('company'), $this->RobotStatuses->getCredential('token'), $this->mode);
    }

    /**
     * Retrieve the orders based upon the request list
     * Assemble them into a Statuses object containing Status object(s)
     */
    public function marshallRequest()
    {
        $this->RobotStatuses->marshallPackets();
	}

	/**
	 * Stand in blank function. Status requests do not need to be processed.
     * Implemented to satisfy the abstract requirements
	 * 
	 * @return boolean
	 */
	public function processRequest() {
        return TRUE;
    }

	/**
	 * Prepare final order response for an robot call
	 */
	public function respond() {
	    $this->set('response', $this->RobotStatuses->getResponse());
		CakeLog::write('robotIO', "$this->requestType status from {$this->RobotCredential->getName()} processed successfully.
		Here's the response. {$this->RobotStatuses->getResponse()}");
		return true;
	}
	
}