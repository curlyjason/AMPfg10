<?php

App::uses('FgHtmlHelper', 'View/Helper');
App::uses('HtmlHelper', 'Cake/View/Helpler');
App::uses('FileExtension', 'Lib');
App::uses('Hash', 'Utility');
App::uses('ReportOrder', 'Model/Entity');

/**
 * CakePHP Helper
 * @author dondrake
 */
class ReportHelper extends FgHtmlHelper {
	
	public $userGroup = '';
	
	public $rows = array();
	
	public $helpers = ['Html'];
	
	/**
	 * Produce a table block based upon a provided string and it's iterator of orders
	 * 
	 * @param string $status
	 * @param iterator $orders
	 * @return type
	 */
	public function orderReportBlock($status, $orders) {
		if(!$orders->valid()){
			return;
		}
		$rows = array();
		echo $this->tag('table', NULL, array('class' => $status));
		$rows[] = array(array('Status: ' . $status, array('colspan' => 4, 'class' => 'Status')));
		echo $this->tableCells($rows);
		foreach ($orders as $orderId => $order) {
			$this->reportOrder($orderId, (new ReportOrder($order)));
		}
		echo '</table>';
	}
	
	public function reportOrder($orderId, $orderObj) {
		$order = $orderObj->raw();
		$rows = array();
		$status = ($orderObj->status() == 'Shipped') 
				? 'Shipped: ' . $orderObj->tracking() 
				: $orderObj->status();
		
		$rows[] = 
		[ 
			$this->plusClass($orderObj->orderNumber(), 'Order'),
			$orderObj->userName(),
			$orderObj->orderCreated(),
			$status
		];
		
		$rows[] = 
		[
			$this->plusClass('', 'spacer'),
			$this->plusColspan($this->reportOrderItem($order['OrderItem']), 3),
		];
		$headers = array('Order #', 'Ordered By', 'Date', 'Status');
		return $this->Html->tableHeaders($headers)
				. $this->Html->tableCells($rows);
	}
	
	private function plusClass($data, $class)
	{
		return [$data, ['class'=>$class]];
	}
	
	private function plusColspan($data, $span)
	{
		return [$data, ['colspan'=>$span]];
	}
	
	public function reportOrderItem($orderItem) {
		$rows = array();
		$headers = array('Item', 'Qty', 'Unit', 'Price', 'Subtotal');
		foreach ($orderItem as $key => $product) {
			$rows[] = array(
				array($product['name'], array('class' => 'orderItem')),
				$product['quantity'],
				$product['sell_unit'],
				$product['price'],
				$product['subtotal']
			);
		}
		$return  = '<table>' .
				$this->Html->tableHeaders($headers) .
				$this->Html->tableCells($rows) .
				'</table>';
		return $return;
	}
	
	/**
	 * Inventory Activity Report: processes the full set of activities
	 */
	public function reportItemActivity() {
		$this->activityItemHead();
//		$this->activitySnapshot('start');
		if(!empty($this->item['Activity'])){
			foreach ($this->item['Activity'] as $activity) {
				$this->activity = $activity;
				$this->activityLine();
			}
		}
//		$this->activitySnapshot('end');
	}
	
	/**
	 * Inventory Activity Report: item first line
	 */
	public function activityItemHead() {
		
		$pre = ($this->userGroup == 'Clients') ? 'Clients' : 'Staff';
//		preg_match("/{$pre}_code: ([\S ]+)\|name/", $this->item['Snapshot'][0], $code);
		$s = (count($this->item['Type']) > 1) ? 's' : '' ;

		$this->rows[$this->active][] = array(
			array($this->item['Snapshot'][0]["{$pre}_code"], array('class' => 'inventoryHead')),
			array($this->item['name'], array('colspan' => 2)),
			array("Item Type$s: " . implode(' | ', $this->item['Type']), array('colspan' => 2))
		);
	}
	
	/**
	 * Inventory Activity Report: snapshot (start state or end state)
	 * 
	 * @todo discover what the new pdf-condition detection should be 
	 *		because the old params['ext'] is gone
	 * 
	 * @param string $mode process 'start' or 'end' snapshot
	 */
	public function activitySnapshot($mode) {
		if(FileExtension::isPdf('missingHaystack')){
			$style = 'background-color: grey; font-size: 60%; border: none; color: white; padding: 1pt;';
		} else {
			$style = '';
		}

		if ($mode != 'start') {
			$pre = 'End';
			$i = 1;
		} else {
			$pre = 'Start';
			$i = 0;
		}
		
		$printDate = "{$pre}ing Inventory Snapshot: " . $this->item['Snapshot'][$i]['date'];
		$printInventory = "{$pre}ing Inventory: ". $this->item['Snapshot'][$i]['inventory'];
		$class = "inventory{$pre}Snapshot";

		$this->rows[$this->active][] = array(
			array('', array('class' => $class, 'style' => $style)),
			array($printDate, array('colspan' => 2, 'style' => $style)),
			array($printInventory, array('colspan' => 2, 'style' => $style))
		);
	}
	
	/**
	 * Inventory Activity Report: a single activity line
	 * 
	 * @todo discover what the new pdf-condition detection should be 
	 *		because the old params['ext'] is gone
	 */
	public function activityLine() {
		if(FileExtension::isPdf('missingHaystack')){
			$style = 'font-size: 60%;';
		} else {
			$style = '';
		}


		$this->rows[$this->active][] = array(
			array('', array('class' => 'inventoryActivity')),
			array($this->activity['date'],array('style' => $style)),
			array('order '. $this->activity['number'], array('style' => $style)),//$order[1],
			array("{$this->activity['from']} to {$this->activity['to']} ({$this->activity['change']})", array('style' => $style)),
			array($this->activity['by'], array('style' => $style))
		);
	}
	
	/**
	 * Set the users group property
	 */
	public function setUserGroupProperty() {
		$this->userGroup = $this->Session->read('Auth.User.group');
	}
}

?>