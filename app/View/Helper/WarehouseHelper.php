<?php

/**
 * CakePHP Helper
 * @author dondrake
 */

App::uses('StatusBase', 'View/Helper');

class WarehouseHelper extends StatusBase {
	
	public $data = array();
	
    //============================================================
    // STATUS GRAIN DISPLAY
    //============================================================

    /**
     * 
     * @param array $data The data for this order
     * @param int $index The number of this order in the stream of orders in the current group
     * @param string $class Additional classes for the wrapper table
     * @return string The talbe HTML
     */
    public function pullWrapper($data, $params, $index = 0, $class = '', $alias = 'Order') {
        if (empty($data)) {
            return '';
        } else {
		$this->tools = array();
		$this->data = $data;
		}
        $params['status'] = $this->data[$alias]['status'];
        extract($params);
        $isFirst = ($index == 0) ? 'first' : 'notFirst'; //a css hook to see the first in a series of tables
        
		$headerArray = $this->assembleHeaderRow($alias);
		extract($headerArray); //this will produce $headerRow and $header, used below
		$pullTools  = $this->pullTools($alias);
		$itemRows = $this->pullItemRows($params, $alias);

		//row to contain order level tools and the order header        
        $rows[] = array_merge($pullTools, $headerRow);

        // row to contain the items table
        $rows[] = array(array($itemRows, array('colspan' => count($rows[0]), 'class' => $data[$alias]['id'] . ' hide warehouseItems ' . $alias)));
		
		$status = ($data[$alias]['status'] == 'Shipping') ? 'Shipped' : $data[$alias]['status'];
        $table = $this->Html->tag('Table', null, array(
            'class' => $status . $data[$alias]['user_id'] . ' ' . $class . ' hide order ' . $isFirst
        ));
        $header = $this->Html->tableHeaders($header);
        $cells = $this->Html->tableCells($rows);
        return $table . $header . $cells . '</table>';
    }
    
    /**
	 * A switcher function to provide either Order or Replenishment Headers
	 * 
	 * @param array $data
	 * @param string $alias Order or Replenishment
	 * @return array
	 */
	private function assembleHeaderRow($alias){
		if($alias == 'Order'){
			$headerArray = $this->assemblePullHeaderRow($alias);
		} else {
			$headerArray = $this->assembleReplenishHeaderRow($alias);
		}
		return $headerArray;
	}
	/**
     * Constructs the header row for pullWrapper as order
     * 
     * @param array $data The entire data package from orderWrapper
     * @return array The assembled header row and title arrays
     */
    private function assemblePullHeaderRow($alias) {
        $headerRow = array(
			array($this->data[$alias]['order_number'],
				array('class' => 'cartHead', 'id' => 'orderNumber-' . $this->data[$alias]['id'])
			),
			array($this->data[$alias]['order_reference'],
				array('class' => 'cartHead', 'id' => 'orderReference-' . $this->data[$alias]['id'])
			),
			array($this->data[$alias]['billing_company'],
				array('class' => 'cartHead', 'id' => 'order-' . $this->data[$alias]['id'])
			),
			array($this->FgHtml->discoverName($this->data['User']) . (($alias == 'Order') ? $this->FgHtml->budgetIndicator($this->data['Budget']) . $this->noteIndicator()  : ''),
				array('class' => 'cartHead', 'id' => 'orderedBy-' . $this->data[$alias]['id'])
			),
			array(date('M j, Y', strtotime($this->data[$alias]['created'])),
				array('class' => 'cartHead', 'id' => 'date-' . $this->data[$alias]['id'])
			),
			array($this->shippingCell($this->data),
				array('class' => 'cartHead', 'id' => 'shipping-' . $this->data[$alias]['id'])
			)
		);
        
        // title row
        $header = array('Tools', 'Order #', 'Ref #', 'Customer', 'Ordered By', 'Date', 'Shipping');

        return array('header' => $header, 'headerRow' => $headerRow);
    }

    /**
     * Constructs the header row for pullWrapper as replenishment
     * 
     * @param array $data The entire data package from orderWrapper
     * @return array The assembled header row and title arrays
     */
    private function assembleReplenishHeaderRow($alias) {
        $headerRow = array(
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
        
        // title row
        $header = array('Tools', 'Order #', 'Vendor', 'Ordered By', 'Date');

        return array('header' => $header, 'headerRow' => $headerRow);
    }

    /**
     * Build a table of order items
     * 
     * Just the items (and their heading row)
     * but no info about the order they belong to
	 * 
	 * $params = 
	 * 	'group' => 'approved',
	 *  'status' => 'Released'
	 * 
	 * table layout, rows and columns
	 * _________
	 * |___|_| |
	 * | | |_| |
	 * |_|_|_|_|
     * 
     * @param array $data A set of order item records
     * @return string A table of Order items
     */
    public function pullItemRows($params, $alias = 'Order') {
        if (empty($this->data[$alias.'Item'])) {
            return '';
        }
        extract($params);
        foreach ($this->data[$alias.'Item'] as $index => $item) {
			if ($alias == 'Order') {
				$itemType =  ' -t-' . $item['type'];
			} else {
				$itemType = '';
			}
			
            $rows[] = array(
				// r1-2, c1 row #, row span 3
                array($index + 1, array('class' => 'cartItem', 'id' => 'row-' . $item['id'] . $itemType, 'rowspan' => 2)),
				
				//r1,c2
                $this->nameCell($item),
				
				//r1, c3 checkbox tool
                $this->checkQtyCell($item, $status, $alias),
				
				
				//r1-4
				$this->kitCell($item, $alias)
			);
			$rows[] = array(
				//r2, c2
				$this->imageCell($item),
				
				//r2, c4
                $this->onHandContent($item, $status, $alias),
				
//				array($this->FgHtml->stringLocations($item, false), array('class' => 'locations ', 'orderItemId' => $item['id']))
				$this->locationCell($item['Item'], $this->data[$alias]['id'])
            );
        }
		
		if ($alias == 'Order') {
			$headerCell2Title = 'Pull';
			$class = $this->data[$alias.'Item'][0]['order_id'];
		} else {
			$headerCell2Title = 'Receive';
			$class = $this->data[$alias.'Item'][0]['replenishment_id'];
		}

        $header = array('#', 'Item', 'Action/Quantity', 'Locations');
        $table = $this->Html->tag('Table', null, array(
            'class' => $class . ' hide warehouseItems ' . $alias
        ));
        $header = $this->Html->tableHeaders($header);
        $cells = $this->Html->tableCells($rows);
        return $table . $header . $cells . '</table>';
    }
	
	/**
	 * Combine the checkbox tool and quantity labels into a single cell
	 * 
	 * @param type $item
	 * @param type $status
	 * @param type $alias
	 * @return type
	 */
	private function checkQtyCell($item, $status, $alias) {
		$qty = $this->rowQty($item, $status, $alias);
		$tool = $this->checkboxTool($item, $status, $alias, $qty['content']);
		return array($tool, $qty['attr']);		
	}
	/**
	 * Create image cell content
	 * 
	 * @param type $item
	 * @return array
	 */
	private function imageCell($item) {
		if (isset($item['Item']['Image'][0]['id'])) {
			$image = $this->Html->image('image' . DS . 'img_file' . DS . $item['Item']['Image'][0]['id'] . DS . 'x160y120_' . $item['Item']['Image'][0]['img_file']);
		} else {
			$image = 'NO IMAGE';
		}
		return $image;
	}
	
	/**
	 * Create name cell contents
	 * 
	 * @param type $item
	 * @return array
	 */
	private function nameCell($item) {
		if(!empty($item['Catalog']['id'])){
			$linkName = $this->Html->tag('span', $item['Item']['item_code'], array('class' => 'itemCode')) . '-' . $this->Html->tag('span', $item['Catalog']['name'], array('class' => 'itemName'));
			$link = $this->Html->link($linkName, array('controller' => 'catalogs', 'action' => 'item_peek', $item['Catalog']['id']), array('class' => 'detailLink', 'escape' => false));
			$after = '<div></div>';
			$options = array('class' => 'cartItem catalogName');
			return array($link.$after, $options);
		} else {
			return array($this->Html->tag('span', $item['Item']['name'], array('class' => 'itemName')), array('class' => 'cartItem'));
		}
	}
	
	private function locationCell($item, $id) {
		if(!empty($item)){
			$loc = $this->_View->element('Warehouse/locations', array('data' => $item, 'id' => $id));
			return array($loc, array('class' => 'location', 'orderItemId' => $item['id'], 'id' => "ord-$id--item-{$item['id']}"));
		} else {
			return array('', array('class' => 'location'));
		}
	}
	
	/**
	 * Create kit tool cell contents, conditionally
	 * 
	 * @param type $item
	 * @param type $alias
	 * @return string|array
	 */
	private function kitCell($item, $alias) {
		if($alias != 'Order' || ($item['Catalog']['type'] & PRODUCT)){
			return '';
		}
		if($item['Catalog']['type'] & COMPONENT){
			$kLabel = 'Break Kit';
		} else if($item['Catalog']['type'] & KIT){
			$kLabel = 'Kit Up';
		}
		$kLink = $this->Html->para('kitTool', $this->Html->link($kLabel, '', array('id' => 'kitCell-'.$item['id'], 'bind' => 'click.kitTool')));
		$a = $this->FgForm->input('kit_quantity', array('class' => 'kitQuantity', 'value' => 0, 'id' => 'kit_quantity-'.$item['id']));
		$b = $this->FgForm->input('kit_cat_type', array('value' => $item['Catalog']['type'], 'type' => 'hidden', 'id' => 'kit_cat_type-'.$item['id']));
		$c = $this->FgForm->input('kit_cat_id', array('value' => $item['Catalog']['id'], 'type' => 'hidden', 'id' => 'kit_cat_id-'.$item['id']));
		$kInput = $a. $b. $c;
		$kSubmit = $this->FgForm->button('Update Inventory', array('type' => 'button', 'bind' => 'click.kitInventoryUpdate'));
		
		$kReturn = array($kLink . $this->Html->div("kitCell-{$item['id']} hide", $kInput . $kSubmit), array('class' => 'kitTool'));
		
		return $kReturn;
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
    public function pullTools($alias = 'Order') {
		$this->revealItemsLink($alias);
		
		if (($this->data[$alias]['status'] == 'Pulled' && $this->data['Shipment'][0]['carrier'] != 'Other')
				|| $this->data[$alias]['status'] == 'Shipped') {
			
		} else if ($this->orderProcess[$this->data[$alias]['status']] == 'Ship') {
			$this->tools[] = $this->Html->link($this->orderProcess[$this->data[$alias]['status']], array(
				'controller' => Inflector::tableize($alias),
				'action' => 'ship',
				$this->data[$alias]['id'],
				$this->orderProcess[$this->data[$alias]['status']]
			));
		} else {
			$this->tools[] = $this->Html->link($this->orderProcess[$this->data[$alias]['status']], array(
				'controller' => Inflector::tableize($alias),
				'action' => 'statusChange',
				$this->data[$alias]['id'],
				$this->orderProcess[$this->data[$alias]['status']]
			));
		}        
		if($alias == 'Order'){
			$this->tools[] = $this->Html->link('Print', array('controller' => Inflector::tableize($alias), 'action' => 'printOrder', $this->data[$alias]['id'] . '.pdf'), array('target' => '_blank'));
			$this->tools[] = $this->Html->link('Label', array('controller' => Inflector::tableize($alias), 'action' => 'shippingLabels', $this->data[$alias]['id']));
		} else {
			$this->tools[] = $this->Html->link('Print', array('controller' => Inflector::tableize($alias), 'action' => 'printReplenishment', $this->data[$alias]['id'] . '.pdf'), array('target' => '_blank'));
		}
		$this->chargesTool($alias, NULL, 'Order');
		$this->documentTool($alias);
		$content = array(implode('|', $this->tools), array('class' => 'tools'));
        return array($content);
    }

	/**
	 * Returns the proper cell content for the On Hand cell
	 * 
	 * Filters the appearance of an input and creates/appends unit of measure information
	 * 
	 * @param string $item is item ID
	 * @param string $status the current status of the order/replenishment
	 * @param string $alias order/replenishment
	 * @return type
	 */
	private function onHandContent($item, $status, $alias){
		if($alias == 'Order'){
			$kitHeader = ($item['type'] & KIT_HEADER) ? TRUE : FALSE;
			$end = ' Pull';
		} else {
			$kitHeader = FALSE;
			$end = ' Receipt';
		}
		
		$ohcHook = "-I{$item['Item']['id']}-C-";
		if (in_array($status, array('Released', 'Placed')) && !$kitHeader) {
			$ohcInput = $this->FgForm->input('onHand-' . $item['id'], array(
					'div' => array('class' => "ohc{$item['id']} hide"),
					'class' => "numeric form-control input-small",// input$ohcHook",
					'bind' => 'change.adjustOnHand', // needs ajax call then update of all of same itemID
					'label' => false,
					'value' => $item['Item']['quantity'],
					'before' => 'On Hand Quantity',
					'after' => ' ea ' . '<p>Adjust shelved amount ' . $this->Html->tag('span',($item['pulled'])?'after':'before', array('class' => 'onHandLabel')) . $end .'</p>',
					'origValue' => $item['Item']['quantity'],
					'itemId' => $item['item_id']				
			));
			$ohcClass = 'avail' . $ohcHook;
			$ohcView = $this->Html->tag('span', 'Current Inventory: ' 
					. $this->Html->tag('span',$item['Item']['quantity'], 
							array('qtyItemId' => $item['Item']['id'], 'class' => $ohcClass)) 
					. ' (click to edit)', 
					array ('itemId' => $item['Item']['id'], 'id' => "ohc{$item['id']}", 'class' => 'toggle'));
			$ohc = $ohcView . $ohcInput;
		} else {
			$iq = $this->Html->tag('span', $item['Item']['quantity'], array ('itemId' => $item['Item']['id']));
			$ohc = ' ' . $iq . ' ' . $this->FgHtml->unitName($item, $alias) . ' | Shelved amount';
		}
		return array($ohc,array('class' => 'cartItem')) ;
	}
	
	/**
	 * Conditionally provide a checkbox Pull/Receive tool for orderItems
	 * 
	 * Pull/Receive tools only appear for orderItems in Placed or Released status
	 * that are NOT kitHeaders
	 * 
	 * @param array $item
	 * @param string $status
	 * @param string $alias
	 * @return array or string
	 */
	private function checkboxTool($item, $status, $alias, $qtyLabel){
		//set variables based upon alias
		if ($alias == 'Order') {
			$kitHeader = ($item['type'] & KIT_HEADER) ? TRUE : FALSE;
			$label = "Pull $qtyLabel";
			
			//refine labels for special kit circumstances
			if($item['type'] & BROKEN_KITS){
				$label = "Break Kit $qtyLabel";
			} else if ($item['type'] & KIT_COMPONENT) {
				$label = "Kit Up $qtyLabel";
			}
		
		} else {
			$kitHeader = FALSE;
			$label = "Receive $qtyLabel";
		}
		
		//if the order is in Released or Placed status, provide the checkbox tool
		if (in_array($status, array('Released', 'Placed')) && !$kitHeader) {
			$checkboxTool = $this->FgForm->input('pull-' . $item['id'], array(
				'div' => false,
				'class' => $alias . ' tool',//form-control input-small pull',
				'label' => $label,
				'type' => 'checkbox',
				'pullQuantity' => $item['quantity'],
				'item-id' => $item['item_id'],
				'checked' => $item['pulled'],
				'bind' => 'click.pullCheckboxClick',
				'hiddenField' => false
				)
			);
		//else, simply display the current status
		} else {
			$checkboxTool = "$status $qtyLabel";
		}
		return $checkboxTool;
	}
	
	/**
	 * Conditionally provide quantity element for warehouse status
	 * 
	 * Construction splits based upon $alias, with elements set differently
	 * for Orders and Replenishments
	 * 
	 * @param array $item
	 * @param string $status
	 * @param string $alias
	 * @return array
	 */
	private function rowQty($item, $status, $alias) {
		if ($alias == 'Replenishment') {
			$of = $item['po_unit'] === 'ea' 
				? "{$item['po_quantity']} {$item['po_unit']}"
				: "{$item['po_unit']} of {$item['po_quantity']}";
		} else {
			$of = $item['sell_unit'] === 'ea' 
				? "{$item['sell_quantity']} {$item['sell_unit']}"
				: "{$item['sell_unit']} of {$item['sell_quantity']}";
		}
		
		if ($alias == 'Order') {
			$after = $this->Html->para('orderAfter', $this->FgHtml->calculatedQuantity($item, 'Order'));
		} else {
			$after = $this->FgHtml->calculatedQuantity($item, 'Replenishment');
//			$after = ' ' . $this->FgHtml->unitName($item, $alias) . ' of ' . $item['po_quantity'];
		}

		return array(
			'content' => "{$item['quantity']} / $of<span>$after</span>",
			'attr' => array('class' => 'cartItem cartQuantity', 'id' => 'quantity-' . $item['id'], 'itemId' => $item['item_id'])
		);
	}
}

?>