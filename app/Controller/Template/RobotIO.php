<?php
/**
 * Robot Transaction Base Class
 *
 * Base Interface of Template Pattern describing automated input/output requests
 *
 * PHP 5
 *
 * @package       Robot
 * @author dondrake
 */

App::uses('AppController', 'Controller');
App::uses('RobotCredential', 'Lib');
/**
 * Robot Transaction Base Class
 *
 * Base Interface of Template Pattern describing automated input/output requests
 *
 * PHP 5
 *
 * @package       Robot
 * @author dondrake
 */
abstract class RobotIO extends AppController {
	
	public $mode;
		
	public $RobotCredential;

	public $requestType;


    /**
     * General input Template for automated delivery of data for storage
     *
     * Creation of an Order or Replenishment are two examples
     */
    public function input(){
        //Always begin with a RobotCredential object
        $this->RobotCredential = new RobotCredential();

        $this->layout = 'ajax';
        try {
            $this->validateRequest();		// guard method on the request and record the event
            $this->migrateRequest();		// convert from request format to array or object
            $this->validateRequestingUser();// insure request is from a valid user
            $this->marshallRequest();		// move the data into save array(s)
            $this->processRequest();        // save the request
            $this->respond();				// assemble return response and record the event
        } catch (Exception $exc) {
            Configure::write('debug', 0);
            $response = $this->robotErrorFormatter($exc);
            CakeLog::write('robotIO', "{$this->mode} received from {$this->RobotCredential->getName()} FAILED TO PROCESS due to error code {$exc->getCode()}");
            $this->set('response', $response);
        }
        $this->render('/Common/input');
    }

    /**
	 * General output Template for automated data requests
	 * 
	 * Inventory levels or shop activity are two examples
	 */
	public function output(){
        //Always begin with a RobotCredential object
        $this->RobotCredential = new RobotCredential();

        $this->layout = 'ajax';
		try {
			$this->validateRequest();		// guard method on the request and record the event
			$this->migrateRequest();		// convert from request format to array or object
 			$this->validateRequestingUser();// insure request is from a valid user
			$this->marshallRequest();		// move the data into save array(s)
			$this->processRequest();		// retrieve the request
			$this->respond();				// assemble return response and record the event
		} catch (Exception $exc) {
			echo $exc->getMessage();
		}
		$this->render('/Common/output');
	}

    public function robotErrorFormatter($exc)
    {
        $error_array = [
            'code' => $exc->getCode(),
            'message' => $exc->getMessage()
        ];

        if(isset($this->request->params['pass'][0]) && preg_match("/[XxMmLl]/",$this->request->params['pass'][0])){

            $XMLresponse = ['body' => [$error_array]];
            $response = Xml::fromArray($XMLresponse)->asXML();
        } else {
            $response = json_encode($error_array);
        }
        return $response;
	}

	/**
	 * Ensure request is properly submitted
	 * 
	 * Request must be a POST and must contain a string of data at TRD[0]
	 * If you need a different data type or need to receive it in 
	 * a different way, over-ride this method in your sub-class
	 */
	protected function validateRequest(){
	    //Make sure this request is a POST
        if (!$this->request->is('post')) {
            throw new RobotProcessException("The request must be a POST.", 1001);
        }
	    //Make sure this POST has data at index 0
		if (!isset($this->request->data[0])) {
			throw new RobotProcessException("The post must contain your data as a string at index 0.", 1001);
		}
		//Validate and set the request type
		$params = $this->request->params;
        if(isset($params['pass'][0])){
            $type = strtoupper($params['pass'][0]);
            if(in_array($type, ['XML','JSON'])){
                $this->requestType = $type;
            }else{
                throw new RobotProcessException('The last argument in the URL must be either XML or JSON, corresponding to the data type sent.', 1001);
            }
        }else{
            throw new RobotProcessException('There must be a last argument in the URL that must be either XML or JSON, corresponding to the data type sent.', 1001);
        }
	}

	/**
	 * Convert from request format to array or object
	 * 
	 * Implemented method is expected to validate genral structure of request
	 * although transformRequest can do further validation
	 * 
	 * This method MUST extract the Customer name and token from the request
	 * and set $this->validUser and $this->validToken for the next template step
	 *
     * @param $input
	 */
	abstract function migrateRequest();
	
	/**
	 * Guard against unauthorized access
	 * 
	 * @return boolean
	 * @throws RobotProcessException
	 */
	protected function validateRequestingUser() {
		if (!$this->User->Customer->find('first', array(
			'conditions' => array(
				'Customer.token' => $this->RobotCredential->getToken(),
				'User.username' => $this->RobotCredential->getUser()
			)
		))) {
		    //Error 1002 Invalid user credentials
			throw new RobotProcessException($this->xmlError("User token for {$this->mode} would not validate"),1003);
		}
		CakeLog::write('robotIO', "{$this->mode} from {$this->RobotCredential->getName()} received for processing");
		return TRUE;
	}

	/**
	 * Transform the array into a usable mode for saving
	 * 
	 * Further validation of the request might also be done at this point
	 */
	abstract function marshallRequest();
	
	/**
	 * Carry out the requested action
	 * 
	 * This might be to save the prepared data
	 * Or perform a requested query
     *
     * @param $package provided data
	 */
	abstract function processRequest();
	
	/**
	 * Setup the robot response to the customer and log in system
	 * 
	 * This might be notification of success
	 * or the delivery of some requested data
	 */
	abstract function respond();
	
}
?>