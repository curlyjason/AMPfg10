<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:44
 */

App::uses('Catalog', 'Model');
App::uses('RobotPacket', 'Lib');

abstract class RobotStatusPacket extends RobotPacket
{

    public function __construct($request, $RobotCredential, $RobotErrors)
    {
        parent::__construct($request, $RobotCredential, $RobotErrors);
        $this->_marshallStatus($request);

    }

    /**
     * Marshall the status
     *
     * This function takes the original data packet and parses it out to
     * marshall Items, Shipments, and the Order itself
     *
     * @param $order object The original data packet
     *
     */
    protected function _marshallStatus($request)
    {
        //unpack $request
        list($Order, $field_name, $field_value) = $request;
        $order = $this->_queryOrder($Order, $field_name, $field_value);
        if(empty($order)) {
            $this->setErrorProperties($field_name, $field_value, 3001);
        } else {
            $this->setShipment($order['Shipment'][0]);
            $this->_order = $order['Order'];
        }
    }

    //Marshalling functions
    /**
     * A standard Order query
     *
     * @param $Order the order model object
     * @param $field_name string the name of the field to search in
     * @param $field_value string the value to search for
     * @return mixed array
     */
    protected function _queryOrder($Order, $field_name, $field_value)
    {
        return $Order->find('first', [
            'conditions'=>
                [
                    $field_name => $field_value,
                    'user_customer_id' => $this->RobotCredential->getUserId()
                ],
            'contain' =>
                [
                    'Shipment'
                ]
        ]);
    }

}