<?php
$f = $this->FgForm->create('Replenishment');
$index = $item['Item']['index'];
$subtotal = $item['Item']['cost'] * $item['Item']['reorder_qty'];
$rows[] = array(
    //inside of this array, each element is a column
    array($index + 1 . $this->Status->hiddenInputs($item), 
        array('class' => 'replenishmentItem hide', 'id' => 'itemRow' . $item['Item']['index'])),
    
    array($item['Item']['item_code'],
        array('class' => 'replenishmentItem', 'id' => 'itemCode-' . $item['Item']['id'])
    ),
    
    array($item['Item']['name'],
        array('class' => 'replenishmentItem', 'id' => 'itemCode-' . $item['Item']['id'])
    ),
    
    array($this->FgForm->input("ReplenishmentItem.$index.quantity", array(
        'label' => false,
	'div' => false,
//	'class' => 'numeric form-control input-small',
	'data-id' => $item['Item']['id'],
//	'before' => $this->Status->calculatedQuantity($item, 'Order', $title = 'Current recorded inventory level') . ' Change to: ',
        'value' => $item['Item']['reorder_qty']
        )),
        array('class' => 'replenishmentItem', 'id' => 'quantity-' . $item['Item']['id'])
    ),
    
    array($this->FgForm->input("ReplenishmentItem.$index.note", array(
        'label' => false,
            'div' => false,
//            'class' => 'numeric form-control input-small',
            'data-id' => $item['Item']['id'],
        'type' => 'textarea'
        )),
        array('class' => 'replenishmentItem', 'id' => 'quantity-' . $item['Item']['id'])
    ),
    
    array($this->FgForm->input("ReplenishmentItem.$index.price", array(
        'label' => false,
            'div' => false,
//            'class' => 'numeric form-control input-small',
            'data-id' => $item['Item']['id'],
            'value' => $item['Item']['cost'],
	    'before' => '<span>$</span>'
        )),
        array('class' => 'replenishmentItem', 'id' => 'price-' . $item['Item']['id'])
    ),
    array($this->FgForm->input("ReplenishmentItem.$index.po_quantity", array(
        'label' => false,
            'div' => false,
//            'class' => 'numeric form-control input-small',
            'data-id' => $item['Item']['id'],
            'value' => $item['Item']['po_quantity']
        )),
        array('class' => 'replenishmentItem', 'id' => 'po_quantity-' . $item['Item']['id'])
    ),
    
    array($this->FgForm->input("ReplenishmentItem.$index.po_unit", array(
        'label' => false,
            'div' => false,
//            'class' => 'numeric form-control input-small',
            'data-id' => $item['Item']['id'],
            'value' => $item['Item']['po_unit']
        )),
        array('class' => 'replenishmentItem', 'id' => 'po_unit-' . $item['Item']['id'])
    ),
    
    array($this->Number->currency($subtotal),
        array('class' => 'replenishmentItem', 'id' => 'subTotal-' . $item['Item']['id'])
    ),
);
$cells = $this->Html->tableCells($rows);
$c = $this->FgForm->end();
echo json_encode(array('row' => $this->Html->tableCells($rows)));

?>