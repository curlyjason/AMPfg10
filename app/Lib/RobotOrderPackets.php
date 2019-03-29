<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('XMLOrder','Lib');
App::uses('JSONOrder','Lib');
App::uses('RobotPackets', 'Lib');

class RobotOrderPackets extends RobotPackets
{
    function migrateRequest($input, $mode){}
    function getResponse(){}
    /**
     * Marshall the order data provided by the migrateRequest
     * and convert into an Orders object containing Order objects
     *
     */
    public function marshallPackets()
    {
        $Order = ClassRegistry::init('Order');
        $class = $this->RobotCredential->getMode();
        foreach ($this->_data as $order) {
            $data = [$Order, $order];
            $this->_orders[] = new $class($data, $this->RobotCredential, $this->RobotErrors);
        }
    }

}