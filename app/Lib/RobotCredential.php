<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:21
 */

App::uses("Customer",'Model');
App::uses("CakeLog", 'Log');

class RobotCredential{

    protected $_user;
    protected $_token;
    protected $_mode;
    protected $_customer;


    /**
     * RobotCredential constructor. Setup as "no known data"
     *
     * @param $user customer user id
     * @param $token secure token
     * @param $mode mode of the robot transaction "XML order" or "JSON replenishment"
     */
    public function __construct()
    {
        $this->_user = "unknown company";
        $this->_token = "no provided token";
        $this->_mode = "mode not known";

    }

    /**
     * Setup the credential set and get customer data from provided user & token
     *
     * @param $user
     * @param $token
     * @param $mode
     */
    public function setCredential($user, $token, $mode)
    {
        $this->_user = $user;
        $this->_token = $token;
        $this->_mode = $mode;
        $this->_customer = $this->_setCustomer();

    }

    /**
     * Setup the customer as a part of setCredential
     *
     * @return mixed
     */
    protected function _setCustomer()
    {
        $Customer = ClassRegistry::init('Customer');
        try {
            $cust = $Customer->find('first', array(
                'conditions' => array(
                    'Customer.token' => $this->_token,
                    'User.username' => $this->_user
                ),
                'contain' => array('User', 'Address', 'Catalog')));
        } catch (Exception $e) {
            throw new RobotProcessException($this->xmlError("User token for {$this->_mode} would not validate"));
        }
		$result = json_encode($cust);
        CakeLog::write('robotIO', "{$this->_mode} from {$this->_user} received for processing. "
		. "Customer record discovered:\n(json_encoded) $result"
        . "\nCustomer IP Address: {$_SERVER['REMOTE_ADDR']}");
        return $cust;
		
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * @return mode
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Return the customer's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_customer['User']['username'];
    }

    /**
     * Return the customer's user ID
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->_customer['User']['id'];
    }

    /**
     * Return the customer's catalog id
     *
     * @return string
     */
    public function getCatalogId()
    {
        return $this->_customer['Catalog']['id'];
    }

}