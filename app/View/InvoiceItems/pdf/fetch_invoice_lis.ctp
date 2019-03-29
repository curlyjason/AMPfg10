
<link type="text/css" href="cake.generic.css" rel="stylesheet" />
<link type="text/css" href="ampfg.css" rel="stylesheet" />
<link type="text/css" href="report.print.css" rel="stylesheet" />
<link type="text/css" href="invoice.print.css" rel="stylesheet" />

<?php
	$this->start('billing');
		echo $this->Html->para(null, $invoiceCustomer['Customer']['name']);
		echo ($invoiceCustomer['Customer']['billing_contact'] == '') ? '' : $this->Html->para(NULL, $invoiceCustomer['Customer']['billing_contact']);
		echo ($invoiceCustomer['Address']['address'] == '') ? '' : $this->Html->para(NULL, $invoiceCustomer['Address']['address']);
		echo ($invoiceCustomer['Address']['address2'] == '') ? '' : $this->Html->para(NULL, $invoiceCustomer['Address']['address2']);
		echo $this->Html->para(null, $invoiceCustomer['Address']['city'] . ', ' . $invoiceCustomer['Address']['state'] . ' ' . $invoiceCustomer['Address']['zip']);
	$this->end();

        echo "<script type=\"text/javascript\">
//<![CDATA[
// \r// Baseline input values\rfunction establishBaseline() {\r\$.baseline = {\r";
		$c = 1;
//		$this->FgHtml->ddd($invoiceItems, 'master array');
        foreach ($invoiceItems as $index => $lineItems) {
			foreach ($lineItems as $lineItem) {
				foreach ($lineItem['InvoiceItem'] as $field => $value) {
					$f = ucfirst($field);
//					$this->FgHtml->ddd($f, 'field');
//					$this->FgHtml->ddd($value, 'value');
				echo "InvoiceItem{$c}$f : '$value',\r";
				}
				$c++;
			}		
		}
        echo"\r};\r};\r//]]>
</script>";
		
echo $this->FgForm->create();
	if ($this->layout !== 'ajax') {
?>
		<!-- BILLING ADDRESS -->
		<div class="section address left">
			<div class="header">
				<p>Customer address</p>
			</div>
			<div class="content_block">
				<?php echo $this->fetch('billing'); ?>
			</div>
		</div>

	<h2>Invoice for <?php echo $invoiceCustomer['Customer']['name'] ?> Total: <?php echo $this->FgHtml->tag('span',$this->Number->currency($invoiceTotal), array('id' => 'invoiceTotal')); ?></h2>
<?php
	}
?>
<table id="invoice">
	<?php
	/*
	 * array(
	 *   'general' => array(
	 *		0 => array(
	 *			'InvoiceItems => array (
	 *				fields
	 *		1 => array ....
	 *   OrderUUID => array)
	 *		0 => array(
	 *			'InvoiceItems => array (
	 *				fields
	 *		1 => array ....
	 *   OrderUUID => array)
	 *		0 => array ....
	 */
	foreach ($invoiceItems as $index => $lineItems) {
		if ($index === 'general') {
			echo $this->element('order_charge', array('index' => $index, 'data' => $lineItems));
		} else {
			echo $this->element('order_charge', array('index' => $index, 'data' => $lineItems, 'label' => $labelList[$index], ));
		}
	}
	?>
</table>
<?php
echo $this->FgForm->end();
?>