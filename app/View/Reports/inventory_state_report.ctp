<?php
$this->start('css');
	echo $this->Html->css('inventory_level');
$this->end();


/**
 * Define the AssociateTable Helper to build ItemCollection tables
 */
$help_config = array(
	'className' => 'AssociateTable',
//	'collection' => $customers,
	'columns' => array(
		'Item' => 'name',
		'Item Code' => 'item_code',
		'Cust Code' => 'customer_item_code',
		'Quantity' => 'quantity',
		'Avail Qty' => 'available_qty',
		'Pending Qty' => 'pending_qty'
		),
//	'tools' => array(new AButton('Cancel'), new AButton('OR'), new AButton('Jump for Joy!', ['href' => 'nirvana'])),
	'td_attributes' => array('class' => 'active_state_undefined'),
//	'tr_attributes' => '',
//	'table_attributes' => '',
//	'header_tr_attributes',
//	'header_th_attributes' => '',
//	'tool_td_attributes' => '',
//	'tool_tr_attributes' => ['class' => 'tools']
);

/**
 * The output loop
 * 
 * Depends on the associate_table, an abstract factory
 * which accepts a helper of type AssociateTable
 * <pre>
 *	active
 *		Customer
 *		active
 *			Item
 *		inactive
 *			Item
 *  inactive
 *		Customer
 *		...
 * </pre>
 */

//setup the helper with data
$this->Helpers->load('associate', $help_config);

//Inset PDF link
echo $this->Html->link(__('Make PDF'), array('action' => 'inventoryStateReport', 'ext' => 'pdf', $customers->inlist(), $sort), array('target' => '_blank', 'style' => 
		'display: inline-block;'
	. 'padding: 5px 30px;'
	. 'background: #72ab00;'
	. 'border: thin solid black;'
	. 'border-radius: 5px;'
	. 'margin-bottom: 5px;'));

echo $this->element('Reports/inventory_report_output_loop');