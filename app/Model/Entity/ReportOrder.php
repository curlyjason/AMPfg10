<?php

/**
 * Description of ReportOrder
 * 
 * Wrapper for the order array inside the ReportHelper
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
	
	public function orderCreated()
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
	
	

}
