<?php
$this->start('script');
	echo $this->FgHtml->script('invoice');
$this->end();
$this->start('css');
	echo $this->FgHtml->css('invoice');
	echo $this->FgHtml->css('ampfg_forms');
$this->end();

        echo "<script type=\"text/javascript\">
//<![CDATA[
// \r// Baseline input values\rfunction establishBaseline() {\r\$.baseline = {\r";
		$c = 1;
        foreach ($invoiceItems as $index => $lineItems) {
			foreach ($lineItems as $lineItem) {
				foreach ($lineItem['InvoiceItem'] as $field => $value) {
					$f = ucfirst($field);
                    //Look at ampfg gotcha issue #598
                    //The values sent to this CDATA block are contained with double quotes
                    //And data values which contain double quotes will cause a jquery failure
				echo "InvoiceItem{$c}$f : \"$value\",\r";
				}
				$c++;
			}		
		}
        echo"\r};\r};\r//]]>
</script>";

echo $this->FgForm->create();
//echo $this->Html->link(__('PDF'), array('controller' => 'invoiceItems', 'action' => 'fetchInvoiceLis', 'ext' => 'pdf', $id, $alias), array('target' => '_blank'));
$tot = $this->Html->tag('span',$this->Number->currency($invoiceTotal), array('id' => 'invoiceTotal'));
echo $this->Html->tag('h2', $invoiceCustomer['Customer']['name'] . ' Total: ' . $tot);

echo $this->Html->tag('table', NULL, array('id' => 'invoice'));
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
			echo $this->element('order_charge', array('index' => $index, 'data' => $lineItems, 'label' => $labelList[$index], 'ordItems' => $ordItems));
		}
	}
echo '</table>';
echo $this->FgForm->end();
if (!$this->request->is('ajax')) {
	echo $this->FgHtml->div('reviewClosingButtons', NULL);
	if (isset($this->request->params['pass'][2])) {
		echo $this->Form->button('<- Edit', array(
			'class' => 'invoiceBackButton big red',
			'ecape' => false,
			'type' => 'button',
			'bind' => 'click.invoiceBackButton',
			'cust' => $invoiceCustomer['Customer']['user_id']));
	}
	echo $this->Form->button('Review Invoice', array('type' => 'button', 'class' => 'invoiceSubmitButton big green', 'ecape' => false, 'bind' => 'click.invoiceSubmit'));
}	
echo '</div>'; //close buttons div
?>