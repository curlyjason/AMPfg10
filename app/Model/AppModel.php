<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');
App::uses('Logger', 'Lib/Trait');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	/**
	 * The statuses as we wish them to order on output
	 * 
	 * @var array
	 */
	public $statusOutputOrder = array(
		    'Backordered' =>  array(),
		    'Submitted' => array(),
		    'Approved' =>  array(),
		    'Released' =>  array(),
		    'Pulled' =>  array(),
		    'Shipped' =>  array(),
		    'Invoiced' =>  array(),
			'Open' => array(),
			'Placed' => array(),
			'Completed' => array()
	);
	/**
	 * IN list to limit catalog searches
	 *
	 * @var array
	 */
	public $catalogList = array();
	
	/**
	 * The user's query string for a simple query
	 *
	 * @var string
	 */
	public $query = '';
	
	/**
	 * The data array of users found in search
	 * 
	 * @var array the found user set 
	 */
	public $userQuery = false;
	
	/**
	 * in list of orders belonging to users discovered with 'search' tool
	 *
	 * @var array
	 */
	public $userQueryOrders = false;
	
	/**
	 * The data array of customers found in search
	 * 
	 * @var array the found catalog set 
	 */
	public $customerQuery = array();
	
	public $actsAs = array('Containable');

    
	/**
     * Write the hash value to prevent id tampering
     *
     * Also defined in AppHelper & AppController
     * Create a hash value from a record id and user/session specific
     * values. The id and hash can be sent to the client, then on
     * return to the server we can verify the id has not been altered
     *
     * @param string $id The record id to secure
     * @return string The secure has to use as a verification value
     */
    public function secureHash($id) {
	return sha1($id . AuthComponent::user('id') . AuthComponent::$sessionKey . Configure::read('Security.salt'));
    }
    
        /**
     * Verify that ID was not altered/spoofed
     *
     * @param type $id
     * @param type $hash
     * @return boolean
     * @throws BadMethodCallException
     */
    public function secureId($id = null, $hash = null) {
	if ($id === null || $hash === null) {
	    throw new BadMethodCallException('Missing security-check parameter(s).');
	}
	if ($this->secureHash($id) == $hash) {
	    return TRUE;
	}
	return FALSE;
    }

    /**
     * Validation rule for secured drop lists
     * 
     * Drop lists that are Model.id => Model.name are created
     * with id/hash instead of straight id. This validation insures
     * the id is valid and inserts just that id value back into
     * the model data so we save just the id
     * 
     * @param array $check The field data from the Model Verification system
     * @param type $field The name of the field we're validating
     * @return boolean valid or not
     */
    public function checkListHash($check, $model, $field) {
        $explodedCheck = explode('/', $check[$field]);
        if ($this->secureId($explodedCheck[0], $explodedCheck[1])) {
            $this->data[$model][$field] = $explodedCheck[0];
            return true;
        }
        return false;
    }
    
    public function ddd($dbg, $title = false, $stack = false){
        //set variables
        $ggr = Debugger::trace();
        $line = preg_split('/[\r*|\n*]/', $ggr);
        $togKey = sha1($line[1]);

        echo "<div class=\"cake-debug-output\">";
        if ($title) {
            echo "<h3 class=\"cake-debug\">$title</h3><p class=\"toggle\" id=\"$togKey\"><strong>$line[1]</strong></p>";
        }
        if ($stack) {
            echo "<pre class=\"$togKey hide\">$ggr</pre>";
        }
        echo"</div>";
        debug($dbg);
    }
    
    /**
     * Retrun an order/replenishment number
     * 
     * Given two values from the Order record
     * return a unique order number
     * YYMM-xxxx
     * YY = last two year digits
     * MM = two digit month
     * xxxx = a base 19 number
     * Base 19 has custom digit set, all caps
     * 
     * @param int $seed The seed number from the Order record
     * @param string $created The creation date from the Order record
     * @return string order number: YYMM-xxxx
     */
    public function getCodedNumber($seed, $created) {
	// digits for our Base 19 number system
	$digit = array(
	    '0' => 'A', // 0
	    '1' => 'B', // 1
	    '2' => 'C', // 2
	    '3' => 'D', // 3
	    '4' => 'E', // 4
	    '5' => 'F', // 5
	    '6' => 'G', // 6
	    '7' => 'H', // 7
	    '8' => 'K', // 8
	    '9' => 'M', // 9
	    'a' => 'N', // 10
	    'b' => 'P', // 11
	    'c' => 'R', // 12
	    'd' => 'S', // 13
	    'e' => 'T', // 14
	    'f' => 'W', // 15
	    'g' => 'X', // 16
	    'h' => 'Y', // 17
	    'i' => 'Z'  // 18
	);
	$num = base_convert($seed, 10, 19);

       $jnum = str_split('000' . base_convert($seed, 10, 19));
       $num = '';
       for ($j = count($jnum) - 4; $j < count($jnum); $j++) {
	   $num .= $digit[$jnum[$j]];
       }
       return str_replace('-', '', substr($created, 2, 5)).'-' . $num;
    }
    
    /**
     * Concatenate the error messages from a single model
     * 
     * @param array $modelErrors The error messages for a single model
     * @return string The contatenated error messages from this model
     */
    public function fetchModelValidationMessage($modelErrors){
        if(empty($modelErrors)){
            return $this->validationMessage;
        }
        foreach ($modelErrors as $key => $error) {
            $this->validationMessage .= $error[0]. ' || ';
        }
        return rtrim($this->validationMessage, "| ");
    }
	
	/**
	 * Assemble line item arrays for Order and Replenishment print
	 * 
	 * @param type $orderItems
	 */
	public function assemblePrintLineItems($orderItems) {
		$lines = array();
		foreach ($orderItems as $index => $orderItem) {
			$lines[] = array(
				'#' => $index + 1,
				'quantity' => $orderItem['quantity'],
				'code' => $orderItem['Item']['item_code'],
				'name' => $orderItem['name']//,
//				'location' => $this->stringLoc($orderItem['Item']['Location'])
			);
		}
		return $lines;
	}

	/**
	 * Extend set() to use dot notation to set Model properties and Model->data values
	 * 
	 * If there is a '.' in $one, the new property/data set processes will be used
	 * otherwise, the arguments will fall through for normal processing
	 * 
	 * @param string|dotNotation|array|SimpleXmlElement|DomNode $one Array or string of data or property
	 * @param string $two Value string for the alternative indata method
	 * @return array Data with all of $one's keys and values
	 */
	public function set($one, $two = NULL) {

		if (!$one) {
			return;
		}
		// a string with a dot is our expanded functionality
		if (is_string($one) && stristr($one, '.')) {
			
			preg_match('/^\w+/', $one, $m);
			if (!empty($m)) {
				
				// dot notation setting of properties
				if (property_exists($this, $m[0])) {
					return $this->setProperty($one, $two);
					
				// dot notation setting of $this->data
				} else {
					return $this->setData($one, $two);
				}
			} else
				return;
			
		// also added is property setting without dot notation
		} elseif (is_string($one) && property_exists($this, $one)) {
			$this->setProperty($one, $two);
			
		// or we can fall through to the normal set() features
		} else {
			return parent::set($one, $two);
		}
	}

	/**
	 * helper for set() to handle property setting including through full dot notation
	 * 
	 * @param string $one The property name or property.path to set
	 * @param mixed $two The value to set the property to
	 * @return mixed The value of the property 
	 */
	protected function setProperty($one, $two) {
		preg_match('/^\w+/', $one, $m);
		
		// a simple string that is a property name
		if (!empty($m) && property_exists($this, $one)) {
			$this->$one = $two;
			return $two;
			
		// dot notation on a property	
		} elseif ($m) {
			$this->$m[0] = Hash::insert($this->$m[0], str_replace("$m[0].", '', $one), $two);
			return $this->$m[0];
			
		// not sure how we got here! This should never happen if we run through set()
		} else {
			return;
		}
	}
	
	/**
	 * helper for set() to handle $this->data setting by full dot notation
	 * 
	 * @param string $one The data element to set
	 * @param mixed $two The value to set the data element to
	 * @return array The compelte this->data array 
	 */
	protected function setData($one, $two) {
		preg_match('/^\w+/', $one, $m);
		
		// dot notation on $this->data	
		if (!empty($m[0])) {
			// this follows the conventions of Model->set
			$parts = explode('.', $one);
			if (count($parts === 2)) {
				$modelName = $parts[0];
				$fieldName = $parts[1];
				if ($modelName === $this->alias) {
					if ($fieldName === $this->primaryKey) {
						$this->id = $two;
					}
					if (isset($this->validationErrors[$fieldName])) {
						unset($this->validationErrors[$fieldName]);
					}
				}
			}
			$this->data = Hash::insert($this->data, $one, $two);
			return $this->data;
					
		// not sure how we got here! This should never happen if we run through set()
		} else {
			return;
		}
	}

	/**
	 * Dot notation 'get' access to properties, property array values or $this->data values
	 * 
	 * @param string $path A dot notation path to a property or $this->data value
	 * @return The value requested
	 */
	public function get($path){
		if (!$path) {
			return;
		}
		
		// a string is a property or dot notation request
		if (is_string($path)) {
			
			// simple property request
			if (property_exists($this, $path)) {
				return $this->getProperty($path);
			}
			
			// dot notation: $this->data or property request
			preg_match('/^\w+/', $path, $m);
			if (!empty($m)) {
				$property = $m[0];
				
				// dot notation get of properties
				if (property_exists($this, $property)) {
					return $this->getProperty($path);
					
				// dot notation get of $this->data
				} else {
					return $this->getData($path);
				}
			}
		}
		// a non string or string that failed checks is not a valid request
		return;
	}
	
	/**
	 * 
	 * @param string $path The dot notation path to the property value
	 * 
	 * @return mixed The value of the specified property
	 */
	protected function getProperty($path) {
		preg_match('/^\w+/', $path, $m);
		
		// a simple string that is a property name
		if (!empty($m) && property_exists($this, $path)) {
			return $this->$path;
			
		// dot notation on a property	
		} elseif ($m) {
			$property = $m[0];
			return $this->_get($this->$property, str_replace("$property.", '', $path));
			
		// not sure how we got here! This should never happen if we run through get()
		} else {
			return;
		}
	}
	
	protected function getData($path){
		return $this->_get($this->data, $path);
	}
	
	private function _get($source, $path) {
		$result = Hash::extract($source, $path);
		if (empty($result)) {
			return NULL;
		} elseif (key($result) === 0 && !stristr($path, '{') && count($result) === 1) {
			return $result[0];
		} else {
			return $result;
		}		
	}
	
	/** 
	 * Debugging aid to show the last query
	 * 
	 * @return string
	 */
	public function getLastQuery() {
		$dbo = $this->getDatasource();
		$logs = $dbo->getLog();
		$lastLog = end($logs['log']);
		return $lastLog['query'];
	}
}