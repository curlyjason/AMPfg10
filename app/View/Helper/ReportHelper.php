<?php

App::uses('FgHtmlHelper', 'View/Helper');
App::uses('HtmlHelper', 'Cake/View/Helpler');
App::uses('FileExtension', 'Lib');
App::uses('Hash', 'Utility');
App::uses('ReportOrder', 'Model/Entity');
App::uses('ItemEntity', 'Model/Entity');
App::uses('Session', 'Helper');


/**
 * CakePHP Helper
 * @author dondrake
 */
class ReportHelper extends FgHtmlHelper {
	
	public $userGroup = '';
	
	public $rows = array();
	
	public $helpers = ['Html', 'Session'];
	
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
		echo $this->Html->tag('table', NULL, array('class' => $status));
		$rows[] = array(array('Status: ' . $status, array('colspan' => 4, 'class' => 'Status')));
		echo $this->Html->tableCells($rows);
		foreach ($orders as $orderId => $order) {
			echo $this->reportOrder($orderId, (new ReportOrder($order)));
		}
		echo '</table>';
	}
	
	public function reportOrder($orderId, $order) {
		$rows = [];
		$rows[] = 
		[ 
			$this->plusClass($order->orderNumber(), 'Order'),
			$order->userName(),
			$order->created(),
			($order->status() == 'Shipped') 
				? "Shipped: {$order->tracking()}" 
				: $order->status()
		];
		
		$rows[] = 
		[
			$this->plusClass('', 'spacer'),
			$this->plusColspan($this->reportOrderItem($order->items()), 3),
		];
		
		return $this->Html->tableHeaders(
				['Order #', 'Ordered By', 'Date', 'Status']) 
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
			$rows[] = [
				$this->plusClass($product['name'], 'orderItem'),
				$product['quantity'],
				$product['sell_unit'],
				$product['price'],
				$product['subtotal']
			];
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
	 * @param string $mode process 'start' or 'end' snapshot
	 */
	public function activitySnapshot($mode) {
        if(isset($this->request->params['ext']) && $this->request->params['ext'] == 'pdf'){
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
	 */
	public function activityLine() {
        if(isset($this->request->params['ext']) && $this->request->params['ext'] == 'pdf'){
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