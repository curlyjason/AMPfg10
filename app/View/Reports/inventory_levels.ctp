<style>
select[multiple="multiple"] {
    height: 300px;
    width: 50%;
}
input[type="radio"] {
    margin: 4px 11px;
}
.tip {
    background: none repeat scroll 0 0 #dee;
    color: firebrick;
    font-size: 100%;
    margin: 15px 0 3px;
    padding: 10px;
    width: 49%;
}
h2 {
	color: firebrick;
	font-weight: bold;
}
</style>
<?php
echo $this->Html->tag('h2', 'Inventory Levels Report');
echo $this->FgForm->create('Reports', array('action' => 'inventoryStateReport'));
echo $this->FgHtml->para('tip', 'Select a sort order');
echo $this->FgForm->radio('sort', array(
		'name' => 'Name', 
		'item_code' => 'Amp #', 
		'customer_item_code' => 'Customer #'),
	array('value' => 'name', 'legend' => FALSE));
echo $this->FgHtml->para('tip', 'Select multiple customers by using Shift-click and Control-click');
echo $this->FgForm->select('customer', $customers, array('multiple' => TRUE));
echo $this->FgForm->end('Submit');
