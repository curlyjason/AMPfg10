<?php
if(is_array($stateList)){
    echo $this->Form->input($model . '.state', array(
        'type' => 'select',
        'options' => $stateList,
        'empty' => 'Choose a state or province',
        'label' => 'State/Province'
    ));
} else {
    echo $this->FgForm->input($model . '.state');
}
?>