<?php
if ($budget['use_budget']) {
    echo $this->FgHtml->tag('span','$$');
}
if ($budget['user_item_budget']){
    echo $this->FgHtml->tag('span', '##');
}
?>