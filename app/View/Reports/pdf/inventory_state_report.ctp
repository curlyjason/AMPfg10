<!--Styles need to be manually linked in the pdf files-->
<link type="text/css" href="inventory_level.css" rel="stylesheet" />

<!--Styles can also be manually overridden-->
<style>
	h2, h4{
		margin: 0;
		padding: 0;
	}
	h2{
		margin-top: 0.5em;
	}
</style>
<?php
/**
 * Define the AssociateTable Helper to build ItemCollection tables
 * 
 * This pdf version also defines styles to be associated with these elements
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
	'td_attributes' => array(
		'class' => 'active_state_undefined',
		'style' => 
		'border-left: thin solid #ccc;'
		. 'padding-left: 0.25em;'
		. 'border-bottom: thin solid #ccc;'
		),
//	'tr_attributes' => '',
	'table_attributes' => array('style' => 
		'border-right: 0 none;
		clear: both;
		color: #333;
		margin-bottom: 10px;
		width: 95%;'
		),
//	'header_tr_attributes',
	'header_th_attributes' => array('style' => 
		'text-align:left;
			font-size: 70%;
			border-bottom: thin solid #ccc;
			padding-bottom: 0.25em;'
		),
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

echo $this->element('Reports/inventory_report_output_loop');