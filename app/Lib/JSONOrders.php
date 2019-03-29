<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('JSONOrder','Lib');
App::uses('RobotOrderPackets', 'Lib');

class JSONOrders extends RobotOrderPackets
{
    /**
     * Read data from the provide json into an array that will be saved
     *
     * @param $input
     * @param $mode
     * @return boolean
     */
    public function migrateRequest($input, $mode) {
        $decode = json_decode($input[0], true);
//        debug($decode);
        if (is_null($decode)) {
            throw new RobotProcessException("Malformed JSON request", 1001);
        }
        $this->_data = $decode['Orders'];
        $this->_credential = [
            'company' => $decode['Credentials']['company'],
            'token' => $decode['Credentials']['token']
            ];
        return TRUE;
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