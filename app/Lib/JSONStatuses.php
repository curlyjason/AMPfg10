<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('JSONStatus','Lib');
App::uses('RobotStatusPackets', 'Lib');
App::uses('Order', 'Model');

class JSONStatuses extends RobotStatusPackets
{

    /**
     * Read data from the provide json into an array that will be saved
     *
     * @param $input
     * @param $mode
     * @return boolean
     */
    public function migrateRequest($input, $mode) {
        $decode = json_decode($input[0]);
        if (is_null($decode)) {
            throw new RobotProcessException("Malformed JSON request", 1001);
        }
        $this->_data = $decode->Orders;
        $this->_credential = [
            'company' => $decode->Credentials->company,
            'token' => $decode->Credentials->token
            ];
        return TRUE;
    }

    /**
     * Marshall the order data provided by the migrateRequest
     * and convert into an Orders object containing Order objects
     *
     */
    public function marshallPackets()
    {
        $Order = ClassRegistry::init('Order');

        if(isset($this->_data[0]->order_numbers) && is_array($this->_data[0]->order_numbers)){
            foreach ($this->_data[0]->order_numbers as $order_number){
                $this->_orders[] = new JSONStatus([$Order, 'order_number', $order_number], $this->RobotCredential, $this->RobotErrors);
            }
        }
        if(isset($this->_data[0]->order_references) && is_array($this->_data[0]->order_references)) {
            foreach ($this->_data[0]->order_references as $order_reference) {
                $this->_orders[] = new JSONStatus([$Order, 'order_reference', $order_reference], $this->RobotCredential, $this->RobotErrors);
            }
        }
    }

    /**
     * Collect the response from related orders
     * Arrange as array per required structure
     * Return as XML
     *
     * @return string
     */
    public function getResponse()
    {
        $responseCollector = [];
        foreach ($this->_orders as $order){
            $responseCollector[$order->getOrderRef()]=$order->getResponse();
        }

        $response = json_encode($responseCollector);
        return $response;

    }
}