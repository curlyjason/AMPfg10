<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('XMLStatus','Lib');
App::uses('RobotStatusPackets', 'Lib');

class XMLStatuses extends RobotStatusPackets
{
    protected $_xsd = 'xmlStatus.xsd';

    /**
     * Read data from the provide xml into an array that will be saved
     *
     * Using xmlTemplates, filter and read in the intial
     * values from the user's XML submission
     *
     * @param $input
     * @param $mode
     * @return boolean
     */
    public function migrateRequest($input, $mode) {
        libxml_use_internal_errors(true);
        if(!$data = Xml::build($input[0], array('return' => 'domdocument'))){
            $errors = libxml_get_errors();
            throw new RobotProcessException($this->xmlError($errors[0]->message));
        }
        if(!$data->schemaValidate(WWW_ROOT . '/files/' . $this->_xsd)){
            $errors = libxml_get_errors();
            throw new RobotProcessException($this->xmlError($errors[0]->message));
        }
        $t = Xml::toArray($data);
        $this->_data = $t['Body']['Orders'];
        $this->_credential = $t['Body']['Credentials'];

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

        if(isset($this->_data['OrderNumbers']['order_number']) && is_array($this->_data['OrderNumbers']['order_number'])){
            foreach ($this->_data['OrderNumbers']['order_number'] as $order_number){
                $this->_orders[] = new XMLStatus([$Order, 'order_number', $order_number], $this->RobotCredential, $this->RobotErrors);
            }
        }
        if(isset($this->_data['OrderReferences']['order_reference']) && is_array($this->_data['OrderReferences']['order_reference'])){
            foreach ($this->_data['OrderReferences']['order_reference'] as $order_reference){
                $this->_orders[] = new XMLStatus([$Order, 'order_reference', $order_reference], $this->RobotCredential, $this->RobotErrors);
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

        $XMLresponse = ['body' => []];
        foreach ($responseCollector as $index => $response) {
            $XMLresponse['body'][$index] = $response;
        }
        $response = XML::fromArray($XMLresponse);
        return $response->asXML();

    }

}