<?php
//    debug($feeTable);
$row = array();
    if (!empty($feeTable)) {
	foreach ($feeTable as $index => $record) {
	    $row[] = array(
		$this->FgForm->input("$index.Price.id", array('value' => $record['Price']['id'])) .
			$this->FgForm->input("$index.Price.customer_id", array('value' => $record['Price']['customer_id'], 'type' => 'hidden')) .
			$this->FgForm->input("$index.Price.test_max_qty", array('value' => $record['Price']['test_max_qty'], 'type' => 'hidden', 'class' => 'MAX')) .
		"<p><span class=\"LO\">{$record['Price']['min_qty']}</span>
		     to <span class=\"HI\">{$record['Price']['max_qty']}</span>
		     for $<span class=\"PRICE\">{$record['Price']['price']}</span>",
		
		$this->FgForm->input("$index.Price.min_qty", array('value' => $record['Price']['min_qty'], 'label' => false, 'div' => false, 'class' => 'LO')),
		
		$this->FgForm->input("$index.Price.max_qty", array('value' => $record['Price']['max_qty'], 'label' => false, 'div' => false, 'class' => 'HI')),
		
		$this->FgForm->input("$index.Price.price", array('value' => $record['Price']['price'], 'label' => false, 'div' => false, 'class' => 'PRICE')),
			     
		$this->Html->tag('span', /* $this->Html->image('icon-remove') */ '', array('class' => 'remove', 'price_id' => $record['Price']['id']))
	    );
	}
    }
    
    echo $this->FgForm->create('Price');
    echo $this->Html->tag('table', null, array('customer_id' => $customer_id));
	echo $this->FgHtml->tableHeaders(array('', 'Minimum', 'Maximum', 'Price', 'X'));
	echo $this->FgHtml->tableCells($row);
    echo '</table>';
    echo $this->FgForm->button('New entry', array('type' => 'button', 'id' => 'newFee'));
    echo $this->FgForm->button('Submit', array('type' => 'button', 'id' => 'validate'));
    echo $this->FgForm->button('Cancel', array('type' => 'button', 'bind' => 'click.basicCancelButton'));
    echo $this->FgForm->end();
//    debug($row);
?>
