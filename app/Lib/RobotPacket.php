<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:44
 */

App::uses('Catalog', 'Model');

abstract class RobotPacket
{
    /**
     * An array of order HEADER information
     *
     *  array(
            'billing_company' => 'Sad New Vistas in Testing',
            'first_name' => 'Jason',
            'last_name' => 'Tempestini',
            'phone' => '925-895-4468',
            'billing_address' => '1107 Fountain Street',
            'billing_address2' => '',
            'billing_city' => 'Alameda',
            'billing_state' => 'CA',
            'billing_zip' => '94501',
            'billing_country' => 'US',
            'order_reference' => 'order1',
            'note' => 'This is a note for this order.',
            'user_customer_id' => '54',
            'user_id' => '27',
            'order_type' => 'robot',
            'status' => 'Submitted'
     * )
     * @var array
     */
    protected $_order = [];

    /**
     *An array of shipment information
     *  array(
     *     'billing' => 'Sender',
     *     'carrier' => 'UPS',
     *     'method' => '1DA',
     *     'billing_account' => '',
     *     'first_name' => 'Jason',
     *     'last_name' => 'Tempestini',
     *     'email' => 'jason@tempestinis.com',
     *     'phone' => '925-895-4468',
     *     'company' => 'Curly Media',
     *     'address' => '1107 Fountain Street',
     *     'address2' => '',
     *     'city' => 'Alameda',
     *     'state' => 'CA',
     *     'zip' => '94501',
     *     'country' => 'US',
     *     'tpb_company' => '',
     *     'tpb_address' => '',
     *     'tpb_city' => '',
     *     'tpb_state' => '',
     *     'tpb_zip' => '',
     *     'tpb_phone' => ''
     *   )
     *
     * @var array
     */
    protected $_orderShipment;

    public $RobotCredential;

    protected $_errorCode = 1;

    protected $_errorMessage = 'success';

    public $RobotErrors;

    /**
     * Create order object
     *
     *
     * @param $RobotCredential object
     * @param $request array
     * @param $RobotErrors object
     */
    public function __construct($request, $RobotCredential, $RobotErrors)
    {
        $this->RobotCredential = $RobotCredential;
        $this->RobotErrors = $RobotErrors;
    }

    /**
     * Set a standard, properly formatted return for status requests
     * that return no data
     *
     * @param $field_name string
     * @param $field_value string
     * @param $errorCode int
     */
    public function setErrorProperties($field_name, $field_value, $errorCode)
    {
        $this->setErrorCode($errorCode);
        $this->setErrorMessage($this->RobotErrors->message($errorCode));
        //Adding the error count to this possible na order reference because responses are indexed by order reference.
        $this->setOrderReference(($field_name == 'order_reference') ? $field_value : 'na' . $this->RobotErrors->getErrorCount());
        $this->setOrderNumber(($field_name == 'order_number') ? $field_value : 'na');
        $this->setStatus('Invalid ' . $field_name);
        $this->setCarrier('na');
        $this->setMethod('na');
        $this->setTrackingNumber('na');
        $this->setShippingCost('na');

        //Log the failure
        $class = get_class($this);
        CakeLog::write('robotIO', "$class received from {$this->RobotCredential->getName()} for $field_name $field_value had an error $errorCode {$this->getErrorMessage()}");

    }

    /**
     * Create standard robot order confirmation response
     *
     * @return array
     */
    public function getResponse()
    {
        $response = [
            'code' => $this->getErrorCode(),
            'message' => $this->getErrorMessage(),
            'order_reference' => $this->getOrderRef(),
            'order_number' => $this->getOrderNumber(),
            'status' => $this->getStatus(),
            'carrier' => $this->getCarrier(),
            'method' => $this->getMethod(),
            'tracking' => $this->getTrackingNumber(),
            'shipment_cost' => $this->getShippingCost()
        ];
        return $response;
    }


    /**
     * Standard getter for the order
     *
     * @return mixed
     */
    public function getOrder()
    {
        $order = $this->_order;
        $order['OrderItem'] = $this->_orderItem;
        $order['Shipment'] = [$this->_orderShipment];
        return $order;
    }

    public function getItems(){
        return $this->_orderItem;
    }

    public function getShipment()
    {
        return $this->_orderShipment;
    }

    public function getOrderRef()
    {
        if (empty($this->_order['order_reference'])){
            return "No Order Reference (" . uniqid() . ')';
        } else {
            return $this->_order['order_reference'];
        }
    }

    public function getOrderNumber()
    {
        if (isset($this->_order['order_number'])) {
            return $this->_order['order_number'];
        } else {
            return 'na';
        }
    }

    public function getStatus()
    {
        return $this->_order['status'];
    }

    public function getCarrier()
    {
        return $this->_orderShipment['carrier'];
    }

    public function getMethod()
    {
        return $this->_orderShipment['method'];
    }

    public function getTrackingNumber()
    {
        return $this->_orderShipment['tracking'];
    }

    public function getShippingCost()
    {
        if (isset($this->_orderShipment['shipment_cost'])) {
            return $this->_orderShipment['shipment_cost'];
        } else {
            return 0.00;
        }
    }

    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function setStatus($status)
    {
        $this->_order['status'] = $status;
    }


    public function setItem($index, $item)
    {
        $this->_orderItem[$index] = $item;
    }

    public function setShipment($shipment)
    {
        $this->_orderShipment = $shipment;

        //fix tracking for proper handling in XML
        if (!array_key_exists('tracking', $this->_orderShipment) || $this->_orderShipment['tracking'] === null){
            $this->_orderShipment['tracking'] = 'na';
        }

    }

    public function setOrderNumber($orderNumber)
    {
        $this->_order['order_number'] = $orderNumber;
    }

    public function setOrderReference($orderReference)
    {
        $this->_order['order_reference'] = $orderReference;
    }

    public function setOrderId($orderId)
    {
        $this->_order['order_id'] = $orderId;
    }

    public function setTrackingNumber($trackingNumber)
    {
        $this->_orderShipment['tracking'] = $trackingNumber;
    }

    public function setShippingCost($shipCost)
    {
        $this->_orderShipment['shipment_cost'] = $shipCost;
    }

    public function setCarrier($carrier)
    {
        $this->_orderShipment['carrier'] = $carrier;
    }

    public function setMethod($method)
    {
        $this->_orderShipment['method'] = $method;
    }

    public function setErrorCode($code)
    {
        $this->_errorCode = $code;
    }

    public function setErrorMessage($message)
    {
        $this->_errorMessage = $message;
    }

    public function hasError()
    {
        return $this->getErrorCode() != 1;
    }



}