<?php

App::uses('StatusBase', 'View/Helper');
App::uses('FixtureDataNet', 'Lib');

/**
 * CakePHP Helper
 * @author dondrake
 */
class StatusHelper extends StatusBase {
    
    public $helpers = array('Number');
	//============================================================
	// STATUS GRAIN DISPLAY
	//============================================================

	/**
	 * Flag to determine if tools should be rendered
	 * 
	 * The plan: make evaluation method to call, reset at different points as rendering proceeds
	 * Plan 2: make this an array and do detailed analysis at the beginning
	 *
	 * @var boolean 
	 */
	public $allowTool = true;

	/**
	 * Flag to determine if inputs should be rendered
	 * 
	 * The plan: make evaluation method to call, reset at different points as rendering proceeds
	 * Plan 2: make this an array and do detailed analysis at the beginning
	 *
	 * @var boolean 
	 */
	public $allowInput = true;

	/**
	 * The status of the Order or Replenishment being processed
	 *
	 * @var string $status The status
	 */
	public $status = '';

	/**
	 * The id of the creator of the order or replenishment
	 *
	 * @var string $orderOwner The id
	 */
	public $orderOwner = '';

	/**
	 * Render an order or preplenishment table and its items
	 * 
	 * @param array $data The data for this order
	 * @param int $index The number of this order in the stream of orders in the current group
	 * @param string $class Additional classes for the wrapper table
	 * @param array $params 'alias' => model->alias
	 * @return string The talbe HTML
	 */
	public function orderWrapper($data, $params, $index = 0, $class = '') {
		if (empty($data)) {
			return '';
		} else {
			$this->data = $data;
		}

		// Abstract the model name to allow use with Replenishment
		if (!isset($params['alias'])) {
			$params['alias'] = 'Order';
		}

		// Modify provided params
		$this->status = $params['status'] = $this->data[$params['alias']]['status'];
		$this->orderOwner = $params['orderOwner'] = $this->data[$params['alias']]['user_id'];
		$params['itemLimitBudget'] = $this->data['User']['use_item_limit_budget'];

		extract($params);
		$isFirst = ($index == 0) ? 'first' : 'notFirst'; //a css hook to see the first in a series of tables
		//row to contain order level tools and the order header
		$rows[] = array_merge($this->orderTools($params), $this->assembleHeaderRow($alias));

		// row to contain the items table
		$rows[] = array(array($this->orderItemRows($params), array('colspan' => count($rows[0]), 'class' => $this->data[$alias]['id'] . ' hide orderItems')));

		// header row, cased by Order or Replenishment
		$header = $this->assembleHeader($alias);
		$status = ($this->data[$alias]['status'] == 'Shipping') ? 'Shipped' : $this->data[$alias]['status'];
		$table = $this->Html->tag('Table', null, array(
			'class' => $status . $this->data[$alias]['user_id'] . ' ' . $class . ' hide order ' . $isFirst
		));
		$header = $this->Html->tableHeaders($header);
		$cells = $this->Html->tableCells($rows);
		return $table . $header . $cells . '</table>';
	}
	
	/**
	 * Based upon the $alias type (Order or Replenishment) return the appropriate header
	 * 
	 * @param string $alias
	 * @return array
	 */
	private function assembleHeader($alias) {
		if ($alias == 'Order') {
			return array('Tools', 'Order #', 'Ref #', 'Customer', 'Ordered By', 'Date', 'Shipping');
		} else {
			return array('Tools', 'Order #', 'Vendor', 'Ordered By', 'Date');
		}
	}

	/**
	 * Switch between the Order and Replenishment header row contructors based upon alias
	 * 
	 * @param string $alias either Order or Replenishment
	 * @return array
	 */
	private function assembleHeaderRow($alias) {
		if($alias == 'Order'){
			return $this->assembleOrderHeaderRow($alias);
		} elseif($alias == 'Replenishment'){
			return $this->assembleReplenishmentHeaderRow($alias);
		} else {
			return array(
				array("Unknown alias: $alias",
					array('class' => 'cartHead', 'id' => 'orderNumber-' . $this->data[$alias]['id'])
				));
		}
	}
	
	/**
	 * Constructs the header row for orderWrapper for Orders
	 * 
	 * @param string $alias Order or Replenishment
	 * @return array The assembled header row array
	 */
	private function assembleOrderHeaderRow($alias) {
		return array(
			array($this->data[$alias]['order_number'],
				array('class' => 'cartHead', 'id' => 'orderNumber-' . $this->data[$alias]['id'])
			),
			array($this->data[$alias]['order_reference'],
				array('class' => 'cartHead', 'id' => 'orderReference-' . $this->data[$alias]['id'])
			),
			array($this->data[$alias]['billing_company'], // **********************
				array('class' => 'cartHead', 'id' => 'order-' . $this->data[$alias]['id'])
			),
			array($this->FgHtml->discoverName($this->data['User']) . (($alias == 'Order') ? $this->FgHtml->budgetIndicator($this->data['Budget']) . $this->noteIndicator()  : ''), // **********************
				array('class' => 'cartHead', 'id' => 'orderedBy-' . $this->data[$alias]['id'])
			),
			array(date('M j, Y', strtotime($this->data[$alias]['created'])),
				array('class' => 'cartHead', 'id' => 'date-' . $this->data[$alias]['id'])
			),
			array($this->shippingCell($this->data),
				array('class' => 'cartHead', 'id' => 'shipping-' . $this->data[$alias]['id'])
			)
		);
	}

	/**
	 * Constructs the header row for orderWrapper for Replenishments
	 * 
	 * @param string $alias The entire data package from orderWrapper
	 * @return array The assembled header row array
	 */
	private function assembleReplenishmentHeaderRow($alias) {
		return array(
			array($this->data[$alias]['order_number'],
				array('class' => 'cartHead', 'id' => 'orderNumber-' . $this->data[$alias]['id'])
			),
			array($this->data[$alias]['vendor_company'], // **********************
				array('class' => 'cartHead', 'id' => 'order-' . $this->data[$alias]['id'])
			),
			array($this->FgHtml->discoverName($this->data['User']) . (($alias == 'Order') ? $this->FgHtml->budgetIndicator($this->data['Budget']) . $this->noteIndicator()  : ''), // **********************
				array('class' => 'cartHead', 'id' => 'orderedBy-' . $this->data[$alias]['id'])
			),
			array(date('M j, Y', strtotime($this->data[$alias]['created'])),
				array('class' => 'cartHead', 'id' => 'date-' . $this->data[$alias]['id'])
			),
		);
	}

	/**
	 * Build a table of order items
	 * 
	 * Just the items (and their heading row)
	 * but no info about the order they belong to
	 * 
	 * @param array $params The basic parameters set
	 * @return string A table of Order items
	 */
	public function orderItemRows($params) {
		extract($params);
		if (empty($this->data[$alias . 'Item'])) {
			return '';
		}
		$removeClass = ($this->permitEditing("{$alias}Item")) ? 'remove' : '';
		foreach ($this->data[$alias . 'Item'] as $index => $item) {
			if ($alias == 'Order') {
				$itemType =  ' -t-' . $item['type'];
			} else {
				$itemType = '';
			}
			
			if(!empty($item['Catalog']['id'])){
				$itemName = array($this->Html->link($item['Catalog']['name'], array('controller' => 'catalogs', 'action' => 'item_peek', $item['Catalog']['id'])) . '<div></div>',
					array('class' => 'cartItem')
				);
			} else {
				$itemName = array($item['name'], array('class' => 'cartItem'));
			}
			
			// prepare the table row for this item
			$rows[] = array(
				array($index + 1, array('class' => 'cartItem', 'id' => 'row-' . $item['id'] . $itemType)),
				$itemName,
				array($this->qtyContent($item, $params, $index) . ' ' . $this->chargesTool($alias, " : {$item['name']}", 'OrderItem', $item['id']),
					array('class' => 'cartItem', 'id' => 'quantity-' . $item['id']) // cell attributes
				),
				// this is 'price' display (Order)
				// or cost/per/unit inputs (Replenishment)
				array($this->priceCellContent($item, $alias),
					array('class' => 'cartItem', 'id' => 'price-' . $item['id'])
				),
				// --------------------------------------
				array($this->Number->currency($item['subtotal']),
					array('class' => 'cartItem', 'id' => 'subtotal-' . $item['id'])
				),
				array($this->Html->tag('span', '', array('class' => $removeClass, 'id' => '' . $item['id'])),
					array('class' => 'cartItem')
				)
			);
		}

//        $header = array('#', 'Item Name', 'Weight', 'Qty', 'Price', 'SubTotal');
		if ($alias == 'Order') {
			$priceHeader = 'Price';
		} else {
			$priceHeader = 'Bits per Unit at $ per Unit';
		}
		$header = array('#', 'Item Name', 'Qty', $priceHeader, 'SubTotal');
		$table = $this->Html->tag('Table', null, array(
			'class' => (($alias == 'Order') ? $this->data[$alias . 'Item'][0]['order_id'] : $this->data[$alias . 'Item'][0]['replenishment_id']) . " hide orderItems $alias" // **********************
		));
		$header = $this->Html->tableHeaders($header);
		$cells = $this->Html->tableCells($rows);
		return $table . $header . $cells . '</table>';
	}

	/**
	 * Return a Quantity input or value for Replenishments or Orders
	 * 
	 * @param type $item
	 * @param type $params
	 */
	public function qtyContent($item, $params, $index) {
//		FixtureDataNet::record(__METHOD__, func_get_args());
		extract($params);
		$this->permitEditing("{$alias}Tool");
//		debug($item);
//		die;
		if ($alias == 'Order' || $alias == 'Catalog') {
			$itemAlert = $this->FgHtml->itemLimitAlert($item, $itemLimitBudget);
		} elseif ($alias == 'Replenishment') {
			$itemAlert = '';
		}
		//set a null title for calculatedQuantity, if one not in params
		if (!isset($title)) {
			$title = null;
		}
		$after = " {$this->FgHtml->unitName($item, $alias)} | {$this->FgHtml->calculatedQuantity($item, $alias, $title)} {$this->lineItemBackorderTools($item, $status, $alias)} $itemAlert";
//		debug($item);
		if ($alias == 'Catalog'){
			return $item['Item']['quantity'] . $after;
		} elseif (!$this->permitEditing("{$alias}Item")) {
			return $item['quantity'] . $after;
		} else {
			return $this->FgForm->input('quantity-' . $item['id'], array(
						'div' => false,
						'class' => 'numeric form-control input-small',
						'label' => false,
						'size' => 2,
						'tabindex' => $index + 1,
						'data-id' => $item['id'],
						'value' => $item['quantity'],
						'after' => $after
			));
		}
	}

	/**
	 * Prepare tool set for an order grain on the status page
	 * 
	 * The return array has one element per desired cell
	 * This is a tableCells() compatible array
	 * array ('Html code here', '2nd cell code here');
	 * or
	 * array(
	 *   array('Html here', array('attributes'=>'here'),
	 *   ...
	 * )
	 * 
	 * @param array $data An order record
	 * @return array An array containing the tool cell(s)
	 */
	public function orderTools($params) {
		extract($params);
		$this->tools = array();
		$this->universalStatusTools($params);

		//if editing is allowed, add the filtered status tools
		if ($this->permitEditing("{$alias}Tools")) {
			$this->filteredStatusTools($params);
		}

		$content = array(implode('|', $this->tools), array('class' => 'tools'));
		return array($content);
	}

	/**
	 * Ord/Replen Status page tools that are available to everyone
	 * 
	 * @param type $data
	 * @param type $params
	 * @return array One tool per element
	 */
	private function universalStatusTools($params) {
		extract($params);

		// Expand Items tool
		$this->revealItemsLink($alias);

		// Detail Item tool
		if($alias == 'Order'){
			$this->tools[] = $this->Html->link('Print', array('controller' => 'orders', 'action' => 'printOrder', $this->data[$alias]['id'] . '.pdf'), array('target' => '_blank'));
			$this->tools[] = $this->Html->link('Labels', array('controller' => 'orders', 'action' => 'shippingLabels', $this->data[$alias]['id']));
		} else {
			$this->tools[] = $this->Html->link('Print', array('controller' => 'replenishments', 'action' => 'printReplenishment', $this->data[$alias]['id'] . '.pdf'), array('target' => '_blank'));
		}
		$this->documentTool($alias);
	}

	/**
	 * Ord/Replen Status page tools that are not available to everyone
	 * 
	 * tools for editors
	 * most status change tools are for all editors
	 * but approval is only for approvers
	 * and backorder tools only apply to certain orders
	 * 
	 * @param type $data
	 * @param type $params
	 * @return array One tool per element
	 */
	private function filteredStatusTools($params) {
		extract($params);

//		<editor-fold defaultstate="collapsed" desc="params array">
//		array(
//			'group' => 'watch',
//			'approvable' => array( 4 => 4, 12 => 12), // not on approved orders
//			'alias' => 'Order',
//			'status' => 'Pulled',
//			'orderOwner' => '4',
//			'itemLimitBudget' => false
//		);
//		</editor-fold>
		
		// the link to advance to the next status
		// in this case you must be an approver
		if (isset($approvable) && !empty($approvable) && $this->data['Order']['status'] === 'Submitted') {
			$observed = array(
				$this->data['Order']['user_id'] => $this->data['Order']['user_id'],
				$this->data['Order']['user_customer_id'] => $this->data['Order']['user_customer_id']);

			// yes, you're an apprver, you get the tool
			if (array_intersect_key($observed, $approvable)) {
				$this->tools[] = $this->Html->link($this->orderProcess[$this->data[$alias]['status']], array(
					'controller' => 'orders',
					'action' => 'statusChange',
					$this->data[$alias]['id'],
					$this->orderProcess[$this->data[$alias]['status']]
				));
			}

		}else if($this->group != 'Warehouses' && in_array($this->data[$alias]['status'], array('Released','Placed', 'Shipped'))){
//			$this->tools[] = '';
			// it didn't matter if you were an approver, so do the tool
		} else if($this->data[$alias]['status'] == 'Pulled'){
            $this->tools[] = $this->Html->link($this->orderProcess[$this->data[$alias]['status']], array(
                'controller' => 'orders',
                'action' => 'ship',
                $this->data[$alias]['id']
            ));
		} else {
			$this->tools[] = $this->Html->link($this->orderProcess[$this->data[$alias]['status']], array(
				'controller' => 'orders',
				'action' => 'statusChange',
				$this->data[$alias]['id'],
				$this->orderProcess[$this->data[$alias]['status']]
			));
		}
		
		$this->chargesTool($alias, NULL, 'Order');

		// backorder tool displays only when allowed by permit editing
		if ($this->backorderTool) {
			$this->wholeOrderBackorderTools($params);
		}
	}
	
	/**
	 * Create a set of hidden inputs to be inserted into the createReplenishment form
	 * 
	 * @param array $item The entire data block being handled by the replenishment creator
	 * @return string
	 */
	public function hiddenInputs($item) {
		$index = $item['Item']['index'];
		$inputs =
				$this->FgForm->input("ReplenishmentItem.$index.name", array(
					'value' => $item['Item']['name'],
					'type' => 'hidden'
				)) .
				$this->FgForm->input("ReplenishmentItem.$index.item_id", array(
					'value' => $item['Item']['id'],
					'type' => 'hidden'
				)) .
				$this->FgForm->input("ReplenishmentItem.$index.po_item_code", array(
					'value' => $item['Item']['po_item_code'],
					'type' => 'hidden'
				))
		;
		return $inputs;
	}

	/**
	 * Make a hidden div the contains Order Backorder tools and a link to reveal it
	 * 
	 * Backorder tools on the order are a bit complex
	 * so this make a nice toggling div and puts
	 * titles on the tool links so hovers give explanations
	 * 
	 * @param type $data
	 * @param type $params
	 * @return html
	 */
	public function wholeOrderBackorderTools($params) {
		$bo = array();
		extract($params);
		
//		<editor-fold defaultstate="collapsed" desc="params array">
//		array(
//			'group' => 'watch',
//			'approvable' => array( 4 => 4, 12 => 12), // not on approved orders
//			'alias' => 'Order',
//			'status' => 'Pulled',
//			'orderOwner' => '4',
//			'itemLimitBudget' => false
//		);
//		</editor-fold>
		
			// backorder this whole damn order with no analysis of need
			$bo[] = $this->Html->link('Backorder', array(
				'controller' => 'orders',
				'action' => 'backorderSweep',
				$this->data[$alias]['id'],
				'fullOrder'),
				array('title' => 'Backorder this entire Order')
			);

			// if we are short SOME, backorder ALL. otherwise let it stand on the order
			$bo[] = $this->Html->link('Full Items', array(
				'controller' => 'orders',
				'action' => 'backorderSweep',
				$this->data[$alias]['id'],
				'fullQty'),
				array('title' => 'Backorder the full quantity of items that are short of inventory')
			);

			// if we are short SOME, backorder that amount. otherwise let stand
			$bo[] = $this->Html->link('Over Items', array(
				'controller' => 'orders',
				'action' => 'backorderSweep',
				$this->data[$alias]['id'],
				'overQty'),
				array('title' => 'Backorder the overage of items that are short of inventory. Ship what\'s in stock')
			);
			
			$ul = implode("\n\t", $bo);
			
			$dv = $this->Html->div(
					"hide tools toolPallet backOrder-{$this->data[$alias]['id']} backOrderX-{$this->data[$alias]['id']}",
							$ul . $this->Html->para('close toggle', 'X', array(
								'id' => "backOrderX-{$this->data[$alias]['id']}"))
							);
			
			$this->tools[] = $this->Html->link('Backorder', array(''), array(
				'bind' => 'click.backorderLink',
				'id' => "backOrder-{$this->data[$alias]['id']}",
				'escape' => FALSE)) . $dv;
			
	}
	/**
	 * Make Backorder links for an OrderItem if necessary
	 * 
	 * This is the tool on the Line Item itself
	 * 
	 * @param array $item The Item data to analize
	 * @return string The link html or an empty string
	 */
	public function lineItemBackorderTools($item, $status, $alias) {
		// This creates item backorder links if the item can have/needs them
		$this->permitEditing('OrderTools');
		$backorder = ($this->backorderTool && $item['Item']['available_qty'] < 0 && $alias == 'Order') ? $this->Html->link('Full', array('controller' => 'orders', 'action' => 'setupBackorderItem', $item['id'], 'fullQty'), // link href attribute
						array('title' => "Backrder the full amount even though some may be in stock.") // link attributes
				)
				. '|' . $this->Html->link('Over', array('controller' => 'orders', 'action' => 'setupBackorderItem', $item['id'], 'overQty'), // link href attribute
						array('title' => "Backorder only the amount that is over stock and ship what is available.")
				) : '';

		return $backorder;
	}

	/**
	 * Return price cell content for Order or Replenishment
	 * 
	 * @param type $item
	 * @param type $alias
	 * @return type
	 */
	public function priceCellContent($item, $alias) {
		if ($alias == 'Order') {
			return $this->Number->currency($item['price']);
		} else {
			if ($this->allowInput) {
				if ($this->permitEditing("{$alias}Item")) {
					$inputs =
							$this->FgForm->input('po_quantity', array(
								'id' => "po_quantity-{$item['id']}",
								'class' => 'po_quantity',
								'value' => $item['po_quantity'],
								'label' => false,
								'div' => false,
								'after' => ' per '
							))
							. $this->FgForm->input('po_unit', array(
								'id' => "unit-{$item['id']}",
								'class' => 'unit',
								'value' => $item['po_unit'],
								'label' => false,
								'div' => false
							))
							. $this->FgForm->input('price', array(
								'id' => "price-{$item['id']}",
								'class' => 'price',
								'value' => $item['price'],
								'label' => false,
								'div' => false,
								'before' => ' at $',
								'after' => " {$this->FgHtml->unitName($item, $alias)}"
					));
				} else {
					$inputs = "Purchase units: {$item['po_quantity']} {$item['po_unit']} at {$item['price']}";
				}
				return $this->Html->div('po_price', $inputs);
			}
		}
	}

}
?>