<?php

App::uses('FgHtml', '/View/Helper');
App::uses('Form', '/Helper');

/**
 * CakePHP Helper
 * @author dondrake
 */
class InvoiceHelper extends FgHtmlHelper {
	
	public $rowIndex = 0;

	/**
	 * Assemble the title row for the group of invoice items
	 * 
	 * The group may be General, Order, or OrderItem
	 * 
	 * @param string $title Title for the Group Header row
	 * @param string $orderId The order_id or 'general' (this is an array index)
	 * @param array $invoiceTotals array of Group Header totals indexed by $orderId (see previous param)
	 */
	public function makeTitleRow($orderId, $title, $invoiceTotals) {
//		$this->ddd($invoiceTotals, 'invoicetotals');
		$titleRow = array(
			array($title,
				array('class' => 'invoiceTitle', 'colspan' => 5, 'id' => 'groupHeaderRow-' . $orderId)
			),
			array($this->tag(
						'span', $this->Number->currency($invoiceTotals[$orderId]), array('id' => 'groupHeaderTotal-' . $orderId)
				),
				array('class' => 'invoiceTitleTotal', 'id' => 'invoiceTitleTotal-' . $orderId, 'colspan' => 2)
			)
		);
		return $titleRow;
	}

	public function makeHeaderRow($index) {
		$headerRow = array(
			"#",
			'desc',
			'qty',
//			'unit',
			'price',
			'subtotal',
			'remove'
		);
		if($this->params['ext'] == 'pdf'){
			array_pop($headerRow);
		}
		return $headerRow;
	}

	public function makeToolRow($buttons = TRUE, $index = '', $context, $mode, $orderId, $title, $invoiceTotals) {
		if ($buttons) {
			$buttons = $this->FgForm->button('Done', array('type' => 'button', 'bind' => 'click.saveInvoiceCharges'));
		} else {
			$buttons = '';
		}
		if ($mode === 'edit') {
			$newTool = $this->link('+ new charge', array(), array('bind' => 'click.addNewCharge'));
		} else {
			$newTool = '';
		}
		
		$toolRow = array(
			array($this->firstToolCell($context), array('class' => 'cellOne', 'id' => String::uuid())),
			array($buttons, array('class' => 'invoiceToolButtons')),
			array($newTool, array('class' => 'invoiceTool', 'colspan' => 2, 'id' => 'toolRow-' . $index)),
			array("Subtotal $title",array('class' => 'invoiceTitle', 'id' => 'groupHeaderRow-' . $orderId)),
			array($this->tag('span', $this->Number->currency($invoiceTotals[$orderId]), array('id' => 'groupHeaderTotal-' . $orderId)),
			array('class' => 'invoiceTitleTotal', 'id' => 'invoiceTitleTotal-' . $orderId, 'colspan' => 2)
			)
		);
		return $toolRow;
	}
	
	public function makePdfToolRow($orderId, $title, $invoiceTotals) {
		$toolRow = array(
			array('', array('class' => 'spacer', 'colspan' => 3)),
			array("Subtotal $title", array('class' => 'invoiceTitle', 'colspan' => 2)),
			array($this->tag('span',  $this->Number->currency($invoiceTotals[$orderId]), array('class' => 'totals')), array())
		);
		return $toolRow;
	}
	
	/**
	 * Return the content of the firstToolCell
	 * 
	 * @param array $context the ids of the appropriate connections (customer_id, order_id, order_item_id)
	 * @param type $index
	 * @return string
	 */
	public function firstToolCell($context){
		$firstToolCell = '';
		foreach ($context as $field => $value) {
			$firstToolCell .= $this->FgForm->input($field, array('type' => 'hidden', 'value' => $value, 'id' => NULL));
		}
		return $firstToolCell;
	}

	public function makeChargeRow($charge, $index, $mode = 'edit') {
		$charge = $charge['InvoiceItem'];
		if (!$this->request->is('ajax')) {
			$index = ++$this->rowIndex;
		} else {
			++$index;
		}
//		$this->ddd($charge, 'charge');
		if ($mode != 'edit') {
			$chargeRow = array(
				array($this->tag('span', $index, array('class' => 'rowIndex rowNumberIndex')), array('class' => 'cellOne', 'id' => 'invoiceRow-' . $charge['id'])),
				array($charge['description'], array('class' => 'invoiceDesc')),
				array($charge['quantity'], array('class' => 'invoiceQty')),
//				array($charge['unit'], array('class' => 'invoiceUnit')),
				array($this->Number->currency($charge['price']), array('class' => 'invoicePrice')),
				array($this->Number->currency($charge['subtotal']), array('class' => 'invoiceSubTotal')),
				array('', array('class' => 'invoiceRemove'))
			);
		} else {
			$chargeRow = array(
				// row 1 (row number and hidden id/foreign-key data)
				array(
					$this->tag('span', $index, array('class' => 'rowIndex rowNumberIndex'))
					. $this->FgForm->input(
							"InvoiceItem.$index.id", array('type' => 'hidden', 'value' => $charge['id']))
					. $this->FgForm->input(
							"InvoiceItem.$index.order_id", array('type' => 'hidden', 'value' => $charge['order_id']))
					. $this->FgForm->input(
							"InvoiceItem.$index.order_item_id", array('type' => 'hidden', 'value' => $charge['order_item_id']))
					. $this->FgForm->input(
							"InvoiceItem.$index.unit", array('type' => 'hidden', 'value' => 'ea'))
					. $this->FgForm->input(
							"InvoiceItem.$index.customer_id", array('type' => 'hidden', 'value' => $charge['customer_id'])),
					array('class' => 'cellOne', 'id' => 'invoiceRow-' . $charge['id'], 'row' => $index)),
				// row 2 (description input)
				array($this->FgForm->input(
							"InvoiceItem.$index.description", array('label' => FALSE, 'value' => $charge['description'], 'bind' => 'change.saveChange'
					)),
					array('class' => 'invoiceDesc')),
				// row 3 (quantity input)
				array($this->FgForm->input(
							"InvoiceItem.$index.quantity", array('label' => FALSE, 'value' => $charge['quantity'], 'bind' => 'change.saveChange'
					)),
					array('class' => 'invoiceQty')),
				// row 4 (unit input)
//				array($this->FgForm->input(
//							"InvoiceItem.$index.unit", array('label' => FALSE, 'value' => $charge['unit'], 'bind' => 'change.saveChange'
//					)),
//					array('class' => 'invoiceUnit')),
				// row 5 (price input)
				array($this->FgForm->input(
							"InvoiceItem.$index.price", array('label' => FALSE, 'value' => $charge['price'], 'bind' => 'change.saveChange'
					)),
					array('class' => 'invoicePrice')),
				// row 6 (subtotal display)
				array($this->tag(
							'span', $this->Number->currency($charge['subtotal']), array('id' => "chargeSubtotal-{$charge['id']}")
					),
					array('class' => 'invoiceSubTotal')),
				// row 7 (delete row tool)
				array($this->tag('span', '', array('class' => 'remove', 'id' => 'delete_' . $charge['id'],
					)),
					array('class' => 'invoiceRemove'))
			);
		}
		if($this->params['ext'] == 'pdf'){
			array_pop($chargeRow);
		}
		return $chargeRow;
	}
    
    public function ordItems($data) {
        foreach ($data as $key => $item) {
			$rows[] = array(
				array($item['OrderItem']['quantity'] . ' ' . $item['OrderItem']['sell_unit'], array('class' => 'itemQuantity')),
				array($item['OrderItem']['name'], array('class' => 'itemName')),
                array($this->Number->currency($item['OrderItem']['price']), array('class' => 'itemPrice')),
                array($this->Number->currency($item['OrderItem']['subtotal']), array('class' => 'itemSubtotal'))
			);
        }
        return $rows;
    }
	
}

?>
