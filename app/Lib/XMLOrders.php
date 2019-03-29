<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:38
 */
App::uses('XMLOrder','Lib');
App::uses('RobotOrderPackets', 'Lib');

class XMLOrders extends RobotOrderPackets
{
    protected $_xsd = 'xmlOrder.xsd';

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
            throw new RobotProcessException($this->xmlError($errors[0]->message), 1002);
        }
        if(!$data->schemaValidate(WWW_ROOT . '/files/' . $this->_xsd)){
            $errors = libxml_get_errors();
            throw new RobotProcessException($this->xmlError($errors[0]->message), 1002);
        }
        $t = Xml::toArray($data);
        $this->_data = $t['Body']['Orders']['Order'];
        $this->_credential = $t['Body']['Credentials'];

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

        $XMLresponse = ['body' => []];
        foreach ($responseCollector as $index => $response) {
            $XMLresponse['body'][$index] = $response;
        }
        $response = XML::fromArray($XMLresponse);
        return $response->asXML();

    }
}