<?php
$output = $this->Status->shippingCell($data);
$json_block = array(
    'shipping-' . $data['Order']['id'] => $output
);
echo json_encode($json_block);