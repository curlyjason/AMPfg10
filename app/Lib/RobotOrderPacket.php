<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:44
 */

App::uses('Catalog', 'Model');
App::uses('RobotPacket', 'Lib');

abstract class RobotOrderPacket extends RobotPacket
{
    /**
     * An array of order items
     *   array(
     *       (int) 0 => array(
     *           'index' => '0',
     *           'customer_item_code' => '1456adhd',
     *           'catalog_id' => '52',
     *           'name' => 'Eucalyptus',
     *           'quantity' => '1'
     *       ),
     *       (int) 1 => array(
     *           'index' => '1',
     *           'customer_item_code' => '1456adhd',
     *           'catalog_id' => '56',
     *           'name' => 'Kit - Inventory Both - Can Order',
     *           'quantity' => '1'
     *       )
     *   )
     *
     * @var array
     */
    protected $_orderItem;

    public function __construct($data, $RobotCredential, $RobotErrors)
    {
        parent::__construct($data, $RobotCredential, $RobotErrors);
        list($Order, $order) = $data;
        if(isset($order['order_reference'])){
            $existingOrders = $Order->find('first',
                [
                    'conditions' =>
                    [
                        'order_reference' => $order['order_reference'],
                        'user_customer_id' => $this->RobotCredential->getUserId()
                    ],
                    'contain' => FALSE,
                    'fields' =>
                    [
                        'order_reference'
                    ]
                ]
            );
            if(!empty($existingOrders)){
                $this->setErrorProperties('order_reference', $order['order_reference'], 2001);
//                throw new RobotProcessException("{$order['order_reference']} isn't unique.", 2001);
            }
        } else {
            $this->setErrorProperties('order_reference', 'unknown order reference', 2004);
//            throw new RobotProcessException("Order reference is required", 2004);
        }
        if(!$this->hasError()){
            $this->_marshallOrder($order);
        }

    }

    //Abstract functions
    abstract protected function _marshallOrder($order);

    //Marshalling functions
    /**
     * Marshall the items property from know array structure.
     *
     * Concrete _marshallOrders method creates the following
     * array structure and passes it
     *
     *`array(
     *   (int) 0 => array(
     *       'catalog_id' => '52',
     *       'customer_item_code' => '',
     *       'name' => 'Eucalyptus',
     *       'quantity' => '1'
     *   ),
     *   (int) 1 => array(
     *       'catalog_id' => '',
     *       'customer_item_code' => 'bc1',
     *       'name' => 'Ball Cap',
     *       'quantity' => '1'
     *   )
     * )`
     *
     * @param $items array
     * @return boolean
     */
    protected function _marshallItems($items)
    {
        $Catalog = ClassRegistry::init('Catalog');

        //setup a list of all customer's valid items
        $catalogList = $Catalog->fetchCatalogList($this->RobotCredential->getCatalogId());


        foreach ($items as $index => $item) {

            //set the catalog_id if the robot only provided customer_item_id
            if (($item['catalog_id'])=='') {
                $item['catalog_id'] = $Catalog->fetchCatalogId($this->RobotCredential->getCatalogId(), $item['customer_item_code']);
            }

            //validate catalog_ids
            if (!in_array($item['catalog_id'], $catalogList)) {
                return FALSE;
            }

            //add data points to items
            $c = $Catalog->fetchOnIdNoContainment($item['catalog_id']);
            $m = array_merge($c['Catalog'], $item);

            //conform items to standard item array
            $template = [
                'item_id' => '',
                'catalog_id' => '',
                'name' => '',
                'price' => '',
                'quantity' => '',
                'sell_quantity' => '',
                'sell_unit' => '',
                'type' => '',
                'catalog_type' => ''];

            $item = array_intersect_key($m, $template);
            $item['catalogType'] = $item['type'];

            //Add array keys and data
            $item['each_quantity'] = $item['quantity'] * $item['sell_quantity'];
            $item['subtotal'] = $item['quantity'] * $item['price'];

            //Save the item back into the primary property
            $this->setItem($index, $item);
        }
        return 1;
    }

    /**
     * Setup the proper shipment node
     *
     * Concrete _marshallOrders method creates the following
     * array structure and passes it
     *
     *  `array(
     *       'billing' => 'Sender',
     *       'carrier' => 'UPS',
     *       'method' => '1DA',
     *       'billing_account' => '',
     *       'first_name' => 'Jason',
     *       'last_name' => 'Tempestini',
     *       'email' => 'jason@tempestinis.com',
     *       'phone' => '925-895-4468',
     *       'company' => 'Curly Media',
     *       'address' => '1107 Fountain Street',
     *       'address2' => '',
     *       'city' => 'Alameda',
     *       'state' => 'CA',
     *       'zip' => '94501',
     *       'country' => 'US',
     *       'tpb_company' => '',
     *       'tpb_address' => '',
     *       'tpb_city' => '',
     *       'tpb_state' => '',
     *       'tpb_zip' => '',
     *       'tpb_phone' => ''
     *   )`
     *
     * @param $shipment array the Shipment node of the original data packet
     * @param $order_reference string the customer provided order reference
     */
    protected function _marshallShipments($shipment, $order_reference)
    {
        //break out emails and flags for UPS (only? Also FedEx?)
        $e = explode(',', $shipment['email']);
        $i = 0;
        while($i<3){
            $j=$i+1;
            $shipment["ups_email$j"] = (isset($e[$i]) && stristr($e[$i], '@')) ? trim($e[$i]) : '';
            $shipment["ups_flag$j"] = (isset($e[$i]) && stristr($e[$i], '@')) ? 'y' : 'n';
            $i++;
        }

        //Order reference to ship_ref1
        $shipment['ship_ref1'] = $order_reference;

        //set data to proper node
        $this->setShipment($shipment);
    }

}