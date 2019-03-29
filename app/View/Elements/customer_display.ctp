<div class="customersDisplay" id="<?php echo $grain['Customer']['user_id'] ?>">
    <?php
if ($grain['Customer']['user_id'] != NULL) {
    echo $this->FgHtml->decoratedTag('order contact', 'p', $grain['Customer']['order_contact']);
    echo $this->FgHtml->decoratedTag('billing contact', 'p', $grain['Customer']['billing_contact']);
} else {
    echo $this->FgForm->input("Make $grainName a customer", array('type' => 'checkbox'));
}
?>
</div>