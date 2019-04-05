<?php

/**
 * Description of Order
 *
 * Wrapper for the order array inside the ReportHelper
 *
 * This initial draft is a simple drop&get with no manipulation
 * or reorganization.
 *
 * @todo Testing
 *
 * Currently used nodes:
 * <code>
 * [
 *		Order =>
 *		[
 *			order_number,
 *			created,
 *			status
 *		]
 *		OrderItem =>
 *		[
 *			{n} =>
 *			[
 *				name,
 *				quantity,
 *				sell_unit,
 *				price,
 *				subtotoal
 *			[
 *		]
 *		Shipment =>
 *		[
 *			0 =>
 *			[
 *				tracking
 *			[
 *		]
 *		User =>
 *		[
 *			name
 *		]
 * ]
 * </code>
 *
 * @author dondrake
 */

class OrderEntity
{
    protected $raw;
    protected $order;
    protected $Products;
    protected $Shipment;
    protected $User;

    public function __construct($order)
    {
        $this->raw = $order;
        $this->order = $order['Order'];
        $this->Products = new ProductCollection($order['OrderItem']);
        $this->Shipment = new ShipmentEntity($order['Shipment'][0]);
        $this->User = new UserEntity($order['User']);
    }

    public function raw()
    {
        return $this->raw;
    }

    public function orderNumber() {
        return $this->order['Order']['order_number'];
    }

    public function userName()
    {
        return $this->User->name();
    }

    /**
     *
     * @return string 2016-05-14 formated
     */
    public function created()
    {
        return date('Y-m-d',strtotime($this->order['Order']['created']));
    }

    public function status()
    {
        return $this->order['Order']['status'];
    }

    public function tracking()
    {
        return $this->order['Shipment'][0]['tracking'];
    }

    /**
     *
     * @return array Items for the order
     */
    public function items() {
        return $this->order['OrderItem'];
    }


}