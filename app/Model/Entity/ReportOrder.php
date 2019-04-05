<?php

/**
 * Description of ReportOrder
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
class ReportOrder
{
	
	protected $order;
	
	public function __construct($order)
	{
		$this->order = $order;
	}
	
	public function raw()
	{
		return $this->order;
	}
	
	public function orderNumber() {
		return $this->order['Order']['order_number'];
	}
	
	public function userName()
	{
		return $this->order['User']['name'];
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
