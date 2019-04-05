<?php

echo $this->Html->div('cartHeaderGrain', null);
if ($this->Session->read('Shop.Order.shop') != 1) {
    echo $this->Html->tag('h3', 'You have no open shopping cart.', array('class' => 'grainDisplay'));
} else {
//    echo $this->FgForm->grainDetail();

    echo $this->Html->tag('h3', 'Open Cart', array('class' => 'grainDisplay'));

    echo $this->Html->tag('Table', null);
    echo $this->FgHtml->tableHeaders(array('Number of Items', 'Total Cost'));
    $order = $this->Session->read('Shop.Order');
    echo $this->FgHtml->tableCells(array(
        $order['order_item_count'],
        $order['total']
    ));
    echo '</table>';
    echo '</div>';
}
?>