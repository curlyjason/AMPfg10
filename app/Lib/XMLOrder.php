<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:44
 */

App::uses('Catalog', 'Model');
App::uses('RobotOrderPacket', 'Lib');

class XMLOrder extends RobotOrderPacket
{

    /**
     * Create order object
     *
     * Order array structure:
     * 			array(
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
                    'OrderItems' => array(
                        'OrderItem' => array(
                            (int) 0 => array(
                                'index' => '0',
         *                      'customer_item_code' => '1456adhd',
                                'catalog_id' => '52',
                                'name' => 'Eucalyptus',
                                'quantity' => '1'
                            ),
                            (int) 1 => array(
                                'index' => '1',
         *                      'customer_item_code' => '1456adhd',
                                'catalog_id' => '56',
                                'name' => 'Kit - Inventory Both - Can Order',
                                'quantity' => '1'
                            )
                        )
                    ),
                    'Shipments' => array(
                        'billing' => 'Sender',
                        'carrier' => 'UPS',
                        'method' => '1DA',
                        'billing_account' => '',
                        'first_name' => 'Jason',
                        'last_name' => 'Tempestini',
                        'email' => 'jason@tempestinis.com',
                        'phone' => '925-895-4468',
                        'company' => 'Curly Media',
                        'address' => '1107 Fountain Street',
                        'address2' => '',
                        'city' => 'Alameda',
                        'state' => 'CA',
                        'zip' => '94501',
                        'country' => 'US',
                        'tpb_company' => '',
                        'tpb_address' => '',
                        'tpb_city' => '',
                        'tpb_state' => '',
                        'tpb_zip' => '',
                        'tpb_phone' => ''
     *              )
                ),
     *
     * XMLOrder constructor.
     * @param $order
     * @param $RobotCredential object
     */


    //Marshalling functions

    /**
     * Marshall the order
     *
     * This function takes the original data packet and parses it out to
     * marshall Items, Shipments, and the Order itself
     *
     * @param $order array The original data packet
     * @return boolean
     *
     */
    protected function _marshallOrder($order)
    {

        if($error = $this->_marshallItems($order['OrderItems']['OrderItem'], $order['order_reference']) !=1){
            $this->setErrorProperties('order_reference', $order['order_reference'], $error);
            return false;
        };
        $this->_marshallShipments($order['Shipments'], $order['order_reference']);

        //Remove items and shipments from data packet
        unset($order['OrderItems']);
        unset($order['Shipments']);

        //Setup the order node
        $this->_order = $order;
        $this->_order['user_customer_id'] = $this->RobotCredential->getUserId();
        $this->_order['user_id'] = $this->RobotCredential->getUserId();
        $this->_order['order_type'] = 'robot';
        $this->_order['status'] = 'Submitted';
        return true;
    }

}