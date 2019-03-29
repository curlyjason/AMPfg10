<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */

App::uses('RobotErrors', 'Lib');


abstract class RobotPackets
{

    /**
     * Instance of the credential object for robot transactions
     * @var RobotCredential
     */
    public $RobotCredential;

    /**
     * Initial order data, of unknown / unregulated structure
     * Concrete instances of this Base class work with varying structures
     *
     * @var mixed
     */
    protected $_data;

    /**
     * Array of credentials as passed via robot transaction, along side data
     * `[
     * 'company' => 'the company name',
     * 'token' => 'the valid uuid token'
     * ]`
     *
     * @var array
     */
    protected $_credential;

    /**
     * Array of concrete order objects
     *
     * @var array
     */
    protected $_orders = [];

    public $RobotErrors;

    protected $_packetClass;


    public function __construct($RobotCredential)
    {
        $this->RobotCredential = $RobotCredential;
        $this->RobotErrors = new RobotErrors();
    }

    /**
     * Read data from the provide source into an array that will be saved
     *
     * @param $input source of data encoded in a concrete type
     * @param $mode action being taken on encoded data (eg: XMLOrder, XMLStatusQuery)
     * @return boolean
     */
    abstract function migrateRequest($input, $mode);

    /**
     * Marshall the order data provided by the migrateRequest
     * and convert into an Orders object containing Order objects
     *
     */
    abstract function marshallPackets();

    /**
     * Get the response array to send to requestor
     *
     * @return mixed
     */
    abstract function getResponse();


    /**
     * Get an array of all order objects
     *
     * @return array
     */
    public function getPackets()
    {
        return $this->_orders;
    }

    /**
     * Get a count of orders
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->_orders);
    }

    /**
     * Get the indexed value from the credentials array
     *
     * @param $index string 'company' or 'token'
     * @return mixed
     */
    public function getCredential($index)
    {
        if(array_key_exists($index, $this->_credential)){
            return $this->_credential[$index];
        } else {
            return '';
        }
    }

}